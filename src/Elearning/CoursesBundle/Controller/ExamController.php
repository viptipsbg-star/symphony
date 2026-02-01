<?php

namespace Elearning\CoursesBundle\Controller;

use Elearning\CoursesBundle\Entity\Chapter;
use Elearning\CoursesBundle\Entity\ExamAttempt;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elearning\CoursesBundle\Entity\Exam;
use Elearning\CoursesBundle\Entity\ExamQuestion;
use Elearning\CoursesBundle\Entity\ExamAnswer;
use Elearning\CoursesBundle\Entity\ExamAttemptAnswer;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class ExamController extends Controller
{
    public function getAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter_id = $request->query->get('chapter_id');
        $em = $this->getDoctrine()->getManager();
        $exam = $em->getRepository('ElearningCoursesBundle:Exam')
            ->findOneBy(array('chapter_id' => $chapter_id));


        $data = array();
        $options = array();

        if (!empty($exam)) {
            foreach ($exam->getQuestions() as $question) {
                $el = new \stdClass();
                $el->question = $question->getQuestion();
                $el->choices = array();
                foreach ($question->getAnswers() as $answer) {
                    $choice = new \stdClass();
                    $choice->text = $answer->getAnswer();
                    $choice->correct = $answer->getCorrect();
                    $el->choices[] = $choice;
                }
                $data[] = $el;
            }

            $options = $exam->getOptions();
        }

        if ($exam) {
            return new JsonResponse(array('success' => true, 'exam' => array(
                'id' => $exam->getId(),
                'data' => $data,
                'options' => $options
            )));
        }
        return new JsonResponse(array('success' => false));
    }

    public function saveAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');
        $chapter_id = $request->request->get('chapter_id');
        $data = $request->request->get('data');
        $options = $request->request->get('options');

        if ($id) {
            $oldexam = $em->find('ElearningCoursesBundle:Exam', $id);
            foreach ($oldexam->getQuestions() as $question) {
                foreach ($question->getAnswers() as $answer) {
                    $em->remove($answer);
                }
                $em->remove($question);
            }
        }

        $exam = ($id)
            ? $em->getRepository('ElearningCoursesBundle:Exam')->find($id)
            : new Exam();

        $exam->setChapter($em->getReference('ElearningCoursesBundle:Chapter', $chapter_id));

        $options = json_encode($options);
        $exam->setOptions($options);
        $exam->setVersion('edit');
        $em->persist($exam);

        foreach ($data as $key => $qst) {
            $question = new ExamQuestion();
            $question->setExam($exam);
            $question->setQuestion($qst['question']);
            $question->setOrdering($key);
            $em->persist($question);
            foreach ($qst['choices'] as $ch_index => $choice) {
                $answer = new ExamAnswer();
                $answer->setQuestion($question);
                $answer->setAnswer($choice['text']);
                $answer->setCorrect((bool)((int)$choice['correct']));
                $answer->setOrdering($ch_index);
                $em->persist($answer);
            }
        }

        $em->flush();
        return new JsonResponse(array('success' => true, 'id' => $exam->getId()));
    }


    public function startExamAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $listening_id = $request->request->get('listening_id');
        $exam_id = $request->request->get('exam_id');
        $user = $this->getUser();

        $listening = $em->find("ElearningCoursesBundle:CourseListening", $listening_id);
        if ($listening->getUserId() != $user->getId()) {
            throw new AccessDeniedException("Not allowed");
        }

        $exam = $em->find('ElearningCoursesBundle:Exam', $exam_id);

        $attempts = $em->createQueryBuilder('a')
            ->select('a, CASE WHEN (a.starttime IS null) THEN 0 ELSE 1 END as HIDDEN isstarted')
            ->from('ElearningCoursesBundle:ExamAttempt', 'a')
            ->where('a.listening_id = :listening_id')
            ->andWhere('a.exam_id = :exam_id')
            ->orderBy('isstarted', 'ASC')
            ->addOrderBy('a.starttime', 'DESC')
            ->setParameter('listening_id', $listening->getId())
            ->setParameter('exam_id', $exam_id)
            ->getQuery()
            ->getResult();


        if (count($attempts) == 0) {
            throw new AccessDeniedException("Not allowed");
        }

        /** @var ExamAttempt $lastAttempt */
        $lastAttempt = $attempts[0];
        $lastEndtime = $lastAttempt->getEndtime();

        $spentTime = $lastAttempt->getSpentTime();
        $maxtime = $exam->getOptions()->maxtime;

        // Предполагаемое время окончания экзамена
        $lastStartDate = null;
        if ($lastAttempt->getStarttime()) {
            $lastStartDate = clone $lastAttempt->getStarttime();
            $lastStartDate->add(new \DateInterval("PT" . $maxtime . "M"));
            if ($spentTime) {
                $lastStartDate->sub(new \DateInterval("PT" . $spentTime . "S"));
            }
        }
        $timeended = (!empty($lastStartDate) && $lastStartDate < new \DateTime()) || ($maxtime <= $spentTime / 60);
        $isLastAttempt = count($attempts) >= $exam->getOptions()->numberofretries;

        /* Check if exam is already started */
        if (!empty($lastStartDate) && empty($lastEndtime) && !$timeended) {
            $lefttime = abs($lastStartDate->getTimestamp() - (new \DateTime())->getTimestamp());

            $response = array(
                'success' => true,
                'attempt_id' => $lastAttempt->getId(),
                'left_time' => $lefttime,
            );
            return new JsonResponse($response);
        } else if ($timeended && $isLastAttempt && !$lastAttempt->getPassed()) {
            /* All exam's failed and all attempts used - this course is failed */
            $response = array('success' => false, 'reason' => 'maxattempts');
            return new JsonResponse($response);
        } else if ($timeended && empty($lastEndtime)) {
            $response = array('success' => false, 'reason' => 'maxtime', 'attempt_id' => $lastAttempt->getId());
            return new JsonResponse($response);
        }

        $lastAttempt->setStarttime(new \DateTime());
        $em->flush();

        $lefttime = $exam->getOptions()->maxtime * 60 - $spentTime;

        $response = array(
            'success' => true,
            'attempt_id' => $lastAttempt->getId(),
            'left_time' => $lefttime,
        );
        return new JsonResponse($response);
    }

    public function pauseExamAction($attempt_id)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $attempt = $em->find('ElearningCoursesBundle:ExamAttempt', $attempt_id);
        $user = $this->getUser();

        if (empty($attempt) || $attempt->getCourseListen()->getUserId() != $user->getId()) {
            throw new AccessDeniedException("Not allowed");
        }

        $course = $attempt->getCourseListen()->getCourse();
        if (!$course->getCanPauseExams()) {
            throw new AccessDeniedException("Not allowed");
        }

        $spentTime = abs((new \DateTime())->getTimestamp() - $attempt->getStarttime()->getTimestamp());

        $attempt->setStarttime(null);
        $attempt->setSpentTime($spentTime + $attempt->getSpentTime());
        $em->persist($attempt);
        $em->flush();

        if ($course->getFlexibleOrder()) {
            /** @var Chapter $firstChapter */
            $firstChapter = $em->getRepository('ElearningCoursesBundle:Course')
                ->getFirstChapter($course->getId());

            return $this->redirectToRoute('elearning_course_listen', array(
                    'listening_id' => $attempt->getCourseListen()->getId(),
                    'next' => $firstChapter->getId()
                )
            );
        }

        return $this->redirectToRoute('elearning_my_courses_list_student');
    }

    public function saveExamAnswerAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $attempt_id = $request->request->get('attempt_id');
        $question_id = $request->request->get('question_id');
        $answers = $request->request->get('answers');
        if (empty($attempt_id) || empty($question_id)) {
            throw new AccessDeniedException("Not allowed");
        }

        $attempt = $em->find('ElearningCoursesBundle:ExamAttempt', $attempt_id);
        $user = $this->getUser();

        if (empty($attempt) || $attempt->getCourseListen()->getUserId() != $user->getId()) {
            throw new AccessDeniedException("Not allowed");
        }

        $attemptAnswer = $em->getRepository("ElearningCoursesBundle:ExamAttemptAnswer")
            ->findBy(array('attempt_id' => $attempt_id, 'question_id' => $question_id));
        if (!empty($attemptAnswer)) {
            foreach ($attemptAnswer as $answer) {
                $em->remove($answer);
            }
        }

        if (!empty($answers)) {
            foreach ($answers as $answerid) {
                $attemptAnswer = new ExamAttemptAnswer();
                $attemptAnswer->setAttempt($attempt);
                $examanswer = $em->getReference("ElearningCoursesBundle:ExamAnswer", $answerid);
                $attemptAnswer->setAnswer($examanswer);
                $question = $em->getReference("ElearningCoursesBundle:ExamQuestion", $question_id);
                $attemptAnswer->setQuestion($question);
                $em->persist($attemptAnswer);
            }
        }

        $em->flush();

        $response = array('success' => true);
        return new JsonResponse($response);

    }


    public function examCompletedAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $attempt_id = $request->request->get('attempt_id');

        if (empty($attempt_id)) {
            throw new AccessDeniedException("Not allowed");
        }

        $attempt = $em->find('ElearningCoursesBundle:ExamAttempt', $attempt_id);
        $user = $this->getUser();

        if (empty($attempt) || $attempt->getCourseListen()->getUserId() != $user->getId()) {
            throw new AccessDeniedException("Not allowed");
        }

        $exam = $attempt->getExam();

        $attempts = $em->getRepository('ElearningCoursesBundle:ExamAttempt')
            ->findBy(array('listening_id' => $attempt->getListeningId(), 'exam_id' => $exam->getId()), array('starttime' => 'DESC'));
        $isLastAttempt = count($attempts) == $exam->getOptions()->numberofretries;

        $questionsCorrectCount = array();
        foreach ($exam->getQuestions() as $question) {
            $questionsCorrectCount[$question->getId()] = 0;
            foreach ($question->getAnswers() as $answer) {
                if ($answer->getCorrect()) {
                    $questionsCorrectCount[$question->getId()]++;
                }
            }
        }

        $questionsResult = array(); /* Stores how many correct answers for every question */
        $questionsWithIncorrectAnswers = array();
        foreach ($attempt->getAnswers() as $answer) {
            if ($answer->getAnswer()) {
                if ($answer->getAnswer()->getCorrect()) {
                    if (!isset($questionsResult[$answer->getAnswer()->getQuestionId()])) {
                        $questionsResult[$answer->getAnswer()->getQuestionId()] = 0;
                    }
                    $questionsResult[$answer->getAnswer()->getQuestionId()]++;
                } else {
                    $questionsWithIncorrectAnswers[$answer->getAnswer()->getQuestionId()] = true;
                }
            }
        }

        foreach ($questionsResult as $question_id => $answered_count) {
            if ($answered_count !== $questionsCorrectCount[$question_id] || isset($questionsWithIncorrectAnswers[$question_id])) {
                unset($questionsResult[$question_id]);
            }
        }

        $result = round((count($questionsResult) / count($attempt->getDistinctQuestions())) * 100);
        $passed = $result >= $exam->getOptions()->passcriteria;

        $attempt->setResult($result);
        $attempt->setPassed($passed);
        $attempt->setEndTime(new \DateTime());

        /* If exam was paused */
        if (!$attempt->getStarttime()) {
            $maxtime = $exam->getOptions()->maxtime;
            $endTime = clone $attempt->getEndtime();
            $startTime = $endTime->sub(new \DateInterval("PT" . $maxtime . "M"));
            $attempt->setStarttime($startTime);
        }

        if ($passed || $isLastAttempt) {
            $completion = $em->getRepository("ElearningCoursesBundle:CourseCompletion")
                ->findOneBy(array('listen_id' => $attempt->getListeningId(), 'chapter_id' => $exam->getChapterId()));
            $completion->setCompleted(true);
            $completion->setCompletion(100);
            $completion->setUpdatetime(new \DateTime());
        }

        if (!$passed && $isLastAttempt) {
            $listening = $em->find("ElearningCoursesBundle:CourseListening", $attempt->getListeningId());
            $listening->setCompleted(true);
        }
        $em->flush();

        return $this->redirectToRoute('elearning_course_exam_result', array('attempt_id' => $attempt->getId()));
    }


    public function examResultAction($attempt_id)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $attempt = $em->find('ElearningCoursesBundle:ExamAttempt', $attempt_id);
        $user = $this->getUser();

        if (empty($attempt) || $attempt->getCourseListen()->getUserId() != $user->getId()) {
            throw new AccessDeniedException("Not allowed");
        }

        $exam = $attempt->getExam();

        $params = array();
        $params['exam'] = $exam;
        $params['attempt'] = $attempt;

        $firstChapter = $em->getRepository('ElearningCoursesBundle:Course')
            ->getFirstChapter($attempt->getCourseListen()->getCourseId());

        $params['firstChapter'] = $firstChapter;

        $attempts = $em->getRepository('ElearningCoursesBundle:ExamAttempt')
            ->findBy(array('listening_id' => $attempt->getListeningId(), 'exam_id' => $exam->getId()), array('starttime' => 'DESC'));
        $numberOfRetries = $exam->getOptions()->numberofretries;
        if (count($attempts) >= $numberOfRetries) {
            $params['lastAttempt'] = true;
        } else {
            $params['lastAttempt'] = false;
            $waittime = $exam->getOptions()->waittime;
            $lastEndtime = clone $attempt->getEndtime();
            $lefttimeseconds = ($lastEndtime->add(new \DateInterval("PT" . $waittime . "H"))->getTimestamp() - (new \DateTime())->getTimestamp());
            if ($lefttimeseconds > 0) {
                $hours = floor($lefttimeseconds / 3600);
                $minutes = floor(($lefttimeseconds - $hours * 3600) / 60);
                $seconds = $lefttimeseconds - $minutes * 60 - $hours * 3600;
                $params['lefttime'] = sprintf("%02d", $hours) . ":" . sprintf("%02d", $minutes) . ":" . sprintf("%02d", $seconds);
                $params['lefttimeseconds'] = $lefttimeseconds;
            } else {
                $params['lefttime'] = "00:00:00";
                $params['lefttimeseconds'] = 0;
            }
        }

        return $this->render(sprintf("ElearningCoursesBundle:CourseStudent:exam_result.html.twig"), $params);
    }


    /**
     * Function for cronjob
     * @param Request $request
     * @return JsonResponse
     */
    public function completeNotFinishedExamsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $attempts = $em->getRepository('ElearningCoursesBundle:ExamAttempt')
            ->findBy(array('endtime' => null, 'result' => null, 'passed' => null));

        foreach ($attempts as $attempt) {
            $maxtime = $attempt->getExam()->getOptions()->maxtime;
            if ($attempt->getStarttime()->add(new \DateInterval("PT" . $maxtime . "M")) < new \DateTime()) {

                $personattempts = $em->getRepository('ElearningCoursesBundle:ExamAttempt')
                    ->findBy(array('listening_id' => $attempt->getListeningId(), 'exam_id' => $attempt->getExam()->getId()), array('starttime' => 'DESC'));
                $isLastAttempt = count($personattempts) == $attempt->getExam()->getOptions()->numberofretries;

                $questionsCorrectCount = array();
                foreach ($attempt->getExam()->getQuestions() as $question) {
                    $questionsCorrectCount[$question->getId()] = 0;
                    foreach ($question->getAnswers() as $answer) {
                        if ($answer->getCorrect()) {
                            $questionsCorrectCount[$question->getId()]++;
                        }
                    }
                }
                
                $questionsResult = array(); /* Stores how many correct answers for every question */
                $questionsWithIncorrectAnswers = array();
                foreach ($attempt->getAnswers() as $answer) {
                    if ($answer->getAnswer()) {
                        if ($answer->getAnswer()->getCorrect()) {
                            if (!isset($questionsResult[$answer->getAnswer()->getQuestionId()])) {
                                $questionsResult[$answer->getAnswer()->getQuestionId()] = 0;
                            }
                            $questionsResult[$answer->getAnswer()->getQuestionId()]++;
                        } else {
                            $questionsWithIncorrectAnswers[$answer->getAnswer()->getQuestionId()] = true;
                        }
                    }
                }

                foreach ($questionsResult as $question_id => $answered_count) {
                    if ($answered_count !== $questionsCorrectCount[$question_id] || isset($questionsWithIncorrectAnswers[$question_id])) {
                        unset($questionsResult[$question_id]);
                    }
                }

                $result = round((count($questionsResult) / count($attempt->getDistinctQuestions())) * 100);
                $passed = $result >= $attempt->getExam()->getOptions()->passcriteria;

                $attempt->setResult($result);
                $attempt->setPassed($passed);
                $attempt->setEndTime(new \DateTime());

                if ($passed || $isLastAttempt) {
                    $completion = $em->getRepository("ElearningCoursesBundle:CourseCompletion")
                        ->findOneBy(array('listen_id' => $attempt->getListeningId(), 'chapter_id' => $attempt->getExam()->getChapterId()));
                    $completion->setCompleted(true);
                    $completion->setCompletion(100);
                    $completion->setUpdatetime(new \DateTime());
                }

                if (!$passed && $isLastAttempt) {
                    $listening = $em->find("ElearningCoursesBundle:CourseListening", $attempt->getListeningId());
                    $listening->setCompleted(true);
                }

            }
        }
        $em->flush();

        return new JsonResponse(array('success' => true));
    }


    /**
     * Function for cronjob
     */
    public function sendExamRetakeRemindLetters()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT ea
            FROM ElearningCoursesBundle:ExamAttempt ea
            WHERE ea.passed = 0
            AND ea.endtime IS NOT NULL'
        );
        $attempts = $query->getResult();

        foreach ($attempts as $attempt) {
            $waittime = $attempt->getExam()->getOptions->waittime;
            $waittimeended = $attempt->getEndtime()->add(new \DateInterval("PT" . $waittime . "H")) > new \DateTime();
            $examnotcompleted = !$attempt->getCourseListen()->getCompleted();
            if ($waittimeended && $examnotcompleted) {
                $email = $attempt->getCourseListen->getUser()->getEmail();
                $course = $attempt->getCourseListen->getCourse();

                $template = 'ElearningCoursesBundle:Emails:exam_retake_email.txt.twig';

                $viewlink = $this->generateUrl('elearning_course_general_info', array('id' => $course->getId()), true);
                $rendered = $this->get('templating')->render($template, array(
                    'coursename' => $course->getName(),
                    'viewlink' => $viewlink
                ));

                // Render the email, use the first line as the subject, and the rest as the body
                $renderedLines = explode("\n", trim($rendered));
                $subject = $renderedLines[0];
                $body = implode("\n", array_slice($renderedLines, 1));

                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom(array($this->getParameter('mailer_from_email')['address'] => $this->getParameter('mailer_from_email')['sender_name']))
                    ->setTo($email)
                    ->setBody($body, 'text/html');
                $this->get('mailer')->send($message);
            }
        }
        return new JsonResponse(array('success'=>true));
    }

    public function showIncorrectAnswersAction($attempt_id)
    {
        $em = $this->getDoctrine()->getManager();
        $attempt = $em->getRepository('ElearningCoursesBundle:ExamAttempt')->find($attempt_id);
        if (!$attempt) {
            throw new \Exception('Attempt not found!');
        }

        if (
            !$this->isGranted('ROLE_LECTURER') &&
            $this->getUser()->getId() !== $attempt->getCourseListen()->getUser()->getId()
        ) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $params['attempt'] = $attempt;
        $params['exam'] = $attempt->getExam();

        $questions = array();
        $answers = array();

        foreach ($attempt->getAnswers() as $answer) {
            if ($answer->getAnswerId()) {
                if (!isset($answers[$answer->getQuestionId()])) {
                    $answers[$answer->getQuestionId()] = array();
                }
                $answers[$answer->getQuestionId()][] = $answer->getAnswerId();
            }
            $questions[] = $answer->getQuestion();
        }


        $params['questions'] = $questions;
        $params['answers'] = $answers;
        $params['currentchapter'] = $attempt->getExam()->getChapter();
        $params['course'] = $attempt->getExam()->getChapter()->getCourse();
        $params['chapters'] = [];
        $params['incorrectAnswers'] = true;

        return $this->render("ElearningCoursesBundle:CourseStudent:chapter_exam.html.twig", $params);
    }
}
