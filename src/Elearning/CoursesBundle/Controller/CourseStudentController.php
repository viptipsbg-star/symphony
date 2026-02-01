<?php

namespace Elearning\CoursesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Elearning\CoursesBundle\Entity\Certificate;
use Elearning\CoursesBundle\Entity\Chapter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Elearning\CoursesBundle\Entity\CourseListening;
use Elearning\CoursesBundle\Entity\CourseCompletion;
use Elearning\CoursesBundle\Entity\ExamAttempt;
use Elearning\CoursesBundle\Entity\ExamAttemptAnswer;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class CourseStudentController extends Controller
{
    public function myCoursesAction(Request $request, $category_id = null)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $params = array();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $query = $em->createQuery(
            'SELECT l
            FROM ElearningCoursesBundle:CourseListening l
            JOIN l.groupCourse gc
            JOIN l.course cr
            WHERE l.user_id = :user_id
            AND cr.status = :state
            AND l.completed = 0
            AND gc.active = 1'
        )->setParameter('user_id', $user->getId())
        ->setParameter('state', 'published');
        $listenings = $query->getResult();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();
        $qb->from('ElearningCoursesBundle:Course', 'c')
            ->from('ElearningCoursesBundle:Category', 'cat')
            ->join('c.groupCourses', 'gc')
            ->join('gc.group', 'g')
            ->join('g.employees', 'e')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('e.user_id', ':user_id'),
                $qb->expr()->eq('c.status', ':state'),
                $qb->expr()->eq('gc.active', '1'),
                $qb->expr()->eq('cat.id', 'c.category_id')
            ))
            ->orderBy('c.ordering', 'ASC')
            ->setParameter('user_id', $user->getId())
            ->setParameter('state', 'published');

        $coursesQb = clone $qb;
        $coursesQb->select('c');
        if (is_numeric($category_id)) {
            $coursesQb->andWhere($qb->expr()->eq('c.category_id', ':category_id'))
                ->setParameter('category_id', $category_id);
        }

        $appСonfig = $this->container->getParameter('app_config');
        $groupCourses = !empty($appСonfig['group_courses']);

        if ($groupCourses && !$category_id) {
            $coursesQb->andWhere($qb->expr()->orX(
                $qb->expr()->neq('cat.main_page', true),
                $qb->expr()->eq('c.main_page', true)
            ));
        }

        $coursesQb
            ->andWhere('c.active_date_to > :now OR c.active_date_to IS NULL')
            ->setParameter('now', new \DateTime());

        $courses = $coursesQb->getQuery()->getResult();

        $paginator = $this->get('knp_paginator');
        $courses = $paginator->paginate(
            $courses,
            $request->query->getInt('page', 1)/*page number*/,
            8/*limit per page*/
        );

        $params['courses'] = $courses;

        /** @var QueryBuilder $examsQb */
        $examsQb = $em->createQueryBuilder()
            ->select(array('ch.course_id', 'COUNT(DISTINCT ch.id) as exams_cnt', 'COUNT(DISTINCT cc.id) as passed_cnt'))
            ->from('ElearningCoursesBundle:Chapter', 'ch', 'ch.course_id')
            ->leftJoin(
                'ElearningCoursesBundle:CourseCompletion',
                'cc',
                'WITH',
                'ch.id = cc.chapter_id AND cc.completed = 1 AND cc.listen_id IN (
                    SELECT cl.id FROM ElearningCoursesBundle:CourseListening cl WHERE cl.user_id = :user_id
                )'
            )
            ->where('ch.state = :state')
            ->andWhere('ch.type = :type')
            ->groupBy('ch.course_id')
            ->setParameters(array(
                'state' => Chapter::STATE_PUBLISHED,
                'type' => Chapter::TYPE_EXAM,
                'user_id' => $user->getId()
            ));

        $params['coursesExams'] = $examsQb->getQuery()->getResult();

        if ($groupCourses) {
            $categoriesQb = clone $qb;
            $categoriesQb->select('cat')
                ->andWhere(
                    $qb->expr()->eq('cat.main_page', true)
                )
                ->orderBy('cat.ordering', 'ASC');
            $categoriesMainPage = $categoriesQb->getQuery()->getResult();
            $params['categoriesMainPage'] = $categoriesMainPage;
        }

        $questionsnotifications = array();
        $categories_hierarchy = array();
        foreach ($listenings as $listening) {
            $course = $listening->getCourse();
            if (!isset($categories_hierarchy[$course->getId()])) {
                $categories = array();
                $category = $course->getCategory()->getParent();
                while (!empty($category)) {
                    array_unshift($categories, $category->getName());
                    $category = $category->getParent();
                }
                $categories_hierarchy[$course->getId()] = $categories;
            }

            foreach ($listening->getQuestions() as $question) {
                if (!$question->getViewed() && $question->getAnswer()) {
                    $questionsnotifications[$course->getId()] = true;
                }
            }
        }


        $params['categoriesHierarchy'] = $categories_hierarchy;
        $params['questionsnotifications'] = $questionsnotifications;
        $params['groupCourses'] = $groupCourses;

        return $this->render(sprintf("ElearningCoursesBundle:CourseStudent:my_courses_list.html.twig"), $params);
    }

    /* TODO not used now */
    public function allCoursesAction()
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $params = array();
        $em = $this->getDoctrine()->getManager();
        $courses = $em->getRepository('ElearningCoursesBundle:Course')
            ->findBy(array('status' => 'published'));

        $params['courses'] = $courses;

        return $this->render(sprintf("ElearningCoursesBundle:CourseStudent:all_courses_list.html.twig"), $params);
    }

    /**
     * @param $id Course id
     * @return BaseResponse
     */
    public function generalInfoAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $params = array();
        $course = $em->find('ElearningCoursesBundle:Course', $id);

        if (!$course) {
            throw new NotFoundHttpException();
        }

        if ($course->getActiveDateTo() && $course->getActiveDateTo() < new \DateTime()) {
            throw new AccessDeniedException("Course has expired");
        }

        $params['course'] = $course;

        $user = $this->getUser();

        try {
            $query = $em->createQuery(
                "SELECT l
                 FROM ElearningCoursesBundle:CourseListening l
                 JOIN l.groupCourse gc
                 WHERE l.course_id = :course_id
                 AND l.user_id = :user_id
                 AND gc.active = 1")
                ->setParameter('course_id', $id)
                ->setParameter('user_id', $user->getId());
            $listening = $query->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $listening = null;
        }


        if (!empty($listening)) {
            if ($listening->getCourse()->getId() != $id) {
                throw new AccessDeniedException("Not allowed");
            }

            $query = $em->createQuery(
                "SELECT COUNT(cc.id)
                 FROM ElearningCoursesBundle:CourseCompletion cc
                 JOIN cc.chapter c
                 WHERE cc.listen_id = :listen_id
                 AND cc.completed = 1
                 AND c.state = 'published'")
                ->setParameter('listen_id', $listening->getId());
            $completedChaptersCount = $query->getSingleScalarResult();


            $params['completedChaptersCount'] = $completedChaptersCount;


            $query = $em->createQuery(
                "SELECT cq
                 FROM ElearningCoursesBundle:CourseQuestion cq
                 JOIN cq.courseListening l
                 WHERE l.course_id = :course_id
                 AND l.user_id = :user_id")
                ->setParameter('course_id', $listening->getCourseId())
                ->setParameter('user_id', $user->getId());
            $myquestions = $query->getResult();
            foreach ($myquestions as $question) {
                if ($question->getAnswer()) {
                    $question->setViewed(true);
                }
            }
            $em->flush();
            $params['myquestions'] = $myquestions;

            $params['listening'] = $listening;
        } else {
            $listening = new CourseListening();
            $listening->setCourse($course);
            $listening->setUser($user);
            $listening->setCreated(new \DateTime());

            $foundGroupCourse = false;
            foreach ($user->getEmployee()->getGroups() as $group) {
                foreach ($group->getGroupCourses() as $groupcourse) {
                    if ($groupcourse->getCourseId() == $course->getId() && $groupcourse->getActive()) {
                        $listening->setGroupCourse($groupcourse);
                        $foundGroupCourse = true;
                        break;
                    }
                }
            }
            if ($foundGroupCourse) {
                $listening->setCompleted(false);
                $em->persist($listening);
                $params['listening'] = $listening;
            }
            $em->flush();

            $params['completedChaptersCount'] = 0;
            $params['myquestions'] = array();
        }

        $allChapters = $em->getRepository("ElearningCoursesBundle:Chapter")
            ->findBy(array('course_id' => $course->getId(), 'state' => 'published'));
        $params['totalChaptersCount'] = count($allChapters);

        $faq = $course->getQuestions();
        $params['faq'] = $faq;

        return $this->render(sprintf("ElearningCoursesBundle:CourseStudent:course_info.html.twig"), $params);
    }

    /**
     * Checks if all given chapters are completed and returns completion info
     * @param $listening CourseListening
     * @param $chaptersCount int
     * @return array(
     *  $completed boolean shows if course is completed
     *  $startedChapter \ElearningCoursesBundle\Entity\Chapter first not completed chapter
     *  $completedChapterIds int[] list of completed chapters ids
     * )
     */
    private function getCompletionInfo($listening, $chaptersCount)
    {
        /* Checking maybe all chapters are already completed */
        $startedChapter = array();
        $completedChapterIds = array();
        $allchapterscompleted = true;
        $lastCompletedChapterId = null;
        $completionChaptersCount = 0;
        $maxOrdering = -1;
        foreach ($listening->getCompletion() as $completion) {
            if ($completion->getChapter()->getState() != "published") {
                continue;
            }
            $completionChaptersCount++;
            if ($completion->getCompleted()) {
                $currentCompletedChapter = $completion->getChapter();
                $completedChapterIds[] = $currentCompletedChapter->getId();
                if ($currentCompletedChapter->getOrdering() >= $maxOrdering) {
                    $maxOrdering = $currentCompletedChapter->getOrdering();
                    $lastCompletedChapterId = $currentCompletedChapter->getId();
                }
            } else {
                $allchapterscompleted = false;
                $startedChapter[] = $completion->getChapter();
            }
        }


        /* Completed chapters can be more, than now, that is why we need to check counts */
        $completed = $allchapterscompleted && $completionChaptersCount == $chaptersCount && !$listening->getCompleted();

        return array($completed, $startedChapter, $completedChapterIds, $lastCompletedChapterId);
    }


    /**
     * Finds current chapter to show for user
     * @param $listening CourseListening
     * @param $next mixed user selected chapter id or string "next" to open next chapter
     * @param $chapters Chapter[]
     * @return array(
     *  $currentchapter Chapter current chapter to show
     *  $next String updated $next value
     * )
     */
    private function getCurrentChapterInfo($listening, $next, $chapters)
    {
        $currentcompletion = null;
        foreach ($listening->getCompletion() as $completion) {
            if ($completion->getChapter()->getState() == "published") {
                $currentcompletion = $completion;
            }
        }
        $currentchapter = null;
        $em = $this->getDoctrine()->getManager();

        /* Check if user opened previously completed chapter */
        $nextchapterid = false;
        if (!empty($next) && $next != "next") {
            /* If there is started exam, don't allow changing current chapter to other */
            $examisbeingtaken = false;
            foreach ($listening->getExamAttempts() as $attempt) {
                $starttime = $attempt->getStarttime();
                $endtime = $attempt->getEndtime();
                if (empty($endtime) && !empty($starttime)) {
                    $examisbeingtaken = true;
                }
            }

            /* If exam is not being taken now, get chapter chosen by user with $next var */
            $courseFlexible = $listening->getCourse()->getFlexibleOrder();
            if (!$examisbeingtaken || $courseFlexible) {
                $nextchapterid = $next;
                $selectedcurrentcompletion = $em->getRepository("ElearningCoursesBundle:CourseCompletion")
                    ->findOneBy(array('chapter_id' => $nextchapterid, 'listen_id' => $listening->getId()));
                //If course order is flexible, create completion for any selected chapter
                if (empty($selectedcurrentcompletion) && $courseFlexible) {
                    $currentchapter = $em->getRepository("ElearningCoursesBundle:Chapter")->find($next);
                    if (!empty($currentchapter)) {
                        $selectedcurrentcompletion = new CourseCompletion();
                        $selectedcurrentcompletion->setChapter($currentchapter);
                        $selectedcurrentcompletion->setCourseListen($listening);
                        $selectedcurrentcompletion->setCompleted(false);
                        $selectedcurrentcompletion->setCompletion(0);
                        $selectedcurrentcompletion->setUpdatetime(new \DateTime());
                        $em->persist($selectedcurrentcompletion);
                        $em->flush();
                    }
                }

                if (!empty($selectedcurrentcompletion) && $selectedcurrentcompletion->getChapter()->getType() != "exam") {
                    $currentcompletion = $selectedcurrentcompletion;
                    $currentchapter = $currentcompletion->getChapter();
                }

            } else {
                $next = "next";
            }
        }

        if ($listening->getCompleted() && empty($nextchapterid)) {
            /* Course is completed, selecting first chapter */
            $currentchapter = $chapters[0];
        } else if (empty($currentcompletion) && !$listening->getCompleted()) {
            /* Course is not started, just selecting first chapter */
            $currentchapter = $chapters[0];

            $completion = $em->getRepository("ElearningCoursesBundle:CourseCompletion")
                ->findOneBy(array('listen_id' => $listening->getId(), 'chapter_id' => $currentchapter->getId()));

            if (empty($completion)) {
                $completion = new CourseCompletion();
                $completion->setChapter($currentchapter);
                $completion->setCourseListen($listening);
                $completion->setCompleted(false);
                $completion->setCompletion(0);
                $completion->setUpdatetime(new \DateTime());
                $em->persist($completion);
            }
        } else if (!$nextchapterid) { /* Finding last listened chapter */
            $allcompletions = $listening->getCompletion();

            foreach ($allcompletions as $completion) {
                if ($completion->getUpdateTime() > $currentcompletion->getUpdateTime() &&
                    !$completion->getCompleted()
                ) {
                    $currentcompletion = $completion;
                }
            }

            if ($currentcompletion->getChapter()->getState() == "published") {
                $currentcompletion->setUpdatetime(new \DateTime());
                $currentchapter = $currentcompletion->getChapter();
            }
        }

        $em->flush();

        return array($currentchapter, $next);
    }

    public function listenAction($listening_id, $next = 0)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $params = array();
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $listening = $em->find('ElearningCoursesBundle:CourseListening', $listening_id);

        if (empty($listening) || $listening->getUserId() != $user->getId() || !$listening->getGroupCourse()->getActive()) {
            throw new AccessDeniedException("Not allowed");
        }

        $course = $listening->getCourse();
        $id = $course->getId();

        if ($course->getActiveDateTo() && $course->getActiveDateTo() < new \DateTime() ) {
            throw new AccessDeniedException("Course has expired");
        }

        $startedDate = $listening->getStarted();
        if (empty($startedDate)) {
            $listening->setStarted(new \DateTime());
        }
        $listening->setLastListen(new \DateTime());
        $em->flush();

        $params['listening'] = $listening;
        $params['course'] = $course;


        $chapters = $em->getRepository('ElearningCoursesBundle:Chapter')
            ->findBy(array('course_id' => $id, 'state' => 'published'), array('ordering' => 'ASC'));
        $params['chapters'] = $chapters;

        if (empty($chapters)) {
            return $this->redirectToRoute('elearning_course_general_info', array('listening_id' => $id));
        }


        list($completed, $startedchapter, $completedChapterIds, $lastCompletedChapterId) = $this->getCompletionInfo($listening, count($chapters));
        $params['completedChapterIds'] = $completedChapterIds;
        $params['lastCompletedChapterId'] = $lastCompletedChapterId;
        $params['startedchapter'] = $startedchapter;

        if ($completed) {
            /* course is completed, redirect to completion */
            return $this->redirectToRoute('elearning_course_completed', array('listening_id' => $listening_id));
        }

        list($currentchapter, $next) = $this->getCurrentChapterInfo($listening, $next, $chapters);

        /* Creating CourseCompletion instance for selected current chapter, if it is not yet created */
        if ($next == "next" || empty($currentchapter)) { /* Finding first not completed chapter */
            foreach ($chapters as $chapter) {
                $completion = $em->getRepository("ElearningCoursesBundle:CourseCompletion")
                    ->findOneBy(array('listen_id' => $listening->getId(), 'chapter_id' => $chapter->getId()));
                if (empty($completion) || !$completion->getCompleted()) {

                    if (empty($completion)) {
                        $completion = new CourseCompletion();
                        $completion->setChapter($chapter);
                        $completion->setCourseListen($listening);
                        $completion->setCompleted(false);
                        $completion->setCompletion(0);
                        $completion->setUpdatetime(new \DateTime());
                        $em->persist($completion);
                    }
                    $currentchapter = $chapter;
                    break;
                }
            }
        }

        $em->flush();
        $params['currentchapter'] = $currentchapter;

        if (empty($currentchapter)) {
            return $this->redirectToRoute('elearning_course_completed', array('listening_id' => $listening->getId()));
        }


        $template = "";
        if ($currentchapter->getType() == "video" || $currentchapter->getType() == "video_files") {
            $template = "ElearningCoursesBundle:CourseStudent:chapter_video_chapter.html.twig";
            $app_config = $this->container->getParameter('app_config');
            $rewindForwardOption = !empty($app_config['flexible_courses_enabled']);
            $params['canRewindForward'] = $rewindForwardOption &&
                (in_array($currentchapter->getId(), $completedChapterIds) || $course->getFlexibleOrder());
        } elseif ($currentchapter->getType() == "quiz") {
            $quiz = $em->getRepository("ElearningCoursesBundle:Quiz")
                ->findOneBy(array('chapter_id' => $currentchapter->getId(), 'version' => 'public'));
            $params['quiz'] = $quiz;
            $data = $quiz->getData();
            if ($quiz->getType() == "quiz") {
                $data = $this->prepareQuizData($data);
                $quiz->setData($data);
                $template = "ElearningCoursesBundle:CourseStudent:chapter_quiz_test.html.twig";
            } elseif ($quiz->getType() == "connect") {
                $data = $this->prepareConnectData($data);
                $quiz->setData($data);
                $template = "ElearningCoursesBundle:CourseStudent:chapter_quiz_connect.html.twig";
            }
        } elseif ($currentchapter->getType() == "lesson") {
            $lesson = $em->getRepository("ElearningCoursesBundle:Lesson")
                ->findOneBy(array('chapter_id' => $currentchapter->getId(), 'version' => 'public'));
            $params['lesson'] = $lesson;
            $template = "ElearningCoursesBundle:CourseStudent:chapter_text_lesson.html.twig";
        } elseif ($currentchapter->getType() == "exam") {
            $exam = $em->getRepository("ElearningCoursesBundle:Exam")
                ->findOneBy(array('chapter_id' => $currentchapter->getId(), 'version' => 'public'));

            $params['exam'] = $exam;

            $examdata = $this->prepareExamChapter($listening, $exam, $currentchapter);
            if ($examdata instanceof BaseResponse) {
                return $examdata;
            }
            list($attempt, $questions, $answers) = $examdata;
            $params['attempt'] = $attempt;
            $params['questions'] = $questions;
            $params['answers'] = $answers;
			$params['incorrectAnswers'] = false;

            $template = "ElearningCoursesBundle:CourseStudent:chapter_exam.html.twig";
        }
        elseif ($currentchapter->getType() == "feedback") {
            $feedback = $em->getRepository("ElearningCoursesBundle:Feedback")
                ->findOneBy(array('chapter_id'=>$currentchapter->getId(), 'version'=>'public'));

            $params['feedback'] = $feedback;
            $template = "ElearningCoursesBundle:CourseStudent:chapter_feedback.html.twig";
        }
        elseif ($currentchapter->getType() == "material") {
            $materials = $em->getRepository("ElearningCoursesBundle:Material")
                ->findBy(array('chapter_id'=>$currentchapter->getId()));

            $params['materials'] = $materials;
            $template = "ElearningCoursesBundle:CourseStudent:chapter_material.html.twig";
        }
        elseif ($currentchapter->getType() == "slides") {
            $slides = $em->getRepository("ElearningCoursesBundle:Slide")
                ->findBy(array('chapter_id'=>$currentchapter->getId()), array('ordering' => 'ASC'));

            $params['slides'] = $slides;
            $template = "ElearningCoursesBundle:CourseStudent:chapter_slides.html.twig";
        }

        return $this->render(sprintf($template), $params);
    }


    /**
     * @param $listening \Elearning\CoursesBundle\Entity\CourseListening
     * @param $exam \Elearning\CoursesBundle\Entity\Exam
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function prepareExamChapter($listening, $exam, $currentChapter)
    {
        $em = $this->getDoctrine()->getManager();

        $attempt = new ExamAttempt();
        $questions = array();
        $answers = array();


        $attempts = $em->createQueryBuilder('a')
            ->select('a, CASE WHEN (a.starttime IS null) THEN 0 ELSE 1 END as HIDDEN isstarted')
            ->from('ElearningCoursesBundle:ExamAttempt', 'a')
            ->where('a.listening_id = :listening_id')
            ->andWhere('a.exam_id = :exam_id')
            ->orderBy('isstarted', 'ASC')
            ->addOrderBy('a.starttime', 'DESC')
            ->setParameter('listening_id', $listening->getId())
            ->setParameter('exam_id', $exam->getId())
            ->getQuery()
            ->getResult();

        $examisstarted = false;

        /* This will be not the first attempt */
        if (count($attempts) > 0) {
            /** @var ExamAttempt $lastAttempt */
            $lastAttempt = $attempts[0];
            $spentTime = $lastAttempt->getSpentTime();
            $lastStartDate = null;
            if ($lastAttempt->getStarttime()) {
                $lastStartDate = clone $lastAttempt->getStarttime();
                if ($spentTime) {
                    $lastStartDate->sub(new \DateInterval("PT" . $spentTime . "S"));
                }
            }
            $lastEndtime = $lastAttempt->getEndtime();
            if (!empty($lastEndtime)) {
                $lastEndtime = clone $lastAttempt->getEndtime();
            }
            $maxtime = $exam->getOptions()->maxtime;
            $waittime = $exam->getOptions()->waittime;

            /* Check if exam is already started */
            $started = !empty($lastStartDate);
            $timeended = ($started && $lastStartDate->add(new \DateInterval("PT" . $maxtime . "M")) < new \DateTime()) ||
                !empty($lastEndtime) || ($maxtime <= $spentTime / 60);
            $isLastAttempt = count($attempts) == $exam->getOptions()->numberofretries;
            $isWaitTime = !empty($lastEndtime) &&
                !$lastAttempt->getPassed() &&
                $lastEndtime->add(new \DateInterval("PT" . $waittime . "H")) > new \DateTime();

            if (empty($started) || (empty($lastEndtime) && !$timeended && !$isWaitTime)) {
                /* Exam is started, but not ended, loading answers */
                $examisstarted = true;
                $attempt = $lastAttempt;
                $questions = array();
                $answers = array();
                foreach ($lastAttempt->getAnswers() as $answer) {
                    if ($answer->getAnswerId()) {
                        if (!isset($answers[$answer->getQuestionId()])) {
                            $answers[$answer->getQuestionId()] = array();
                        }
                        $answers[$answer->getQuestionId()][] = $answer->getAnswerId();
                    }
                    $questions[] = $answer->getQuestion();
                }

            } else if ($timeended && $lastAttempt->getResult() === null) {
                $request = $this->get('request');
                $request->request->set('attempt_id', $lastAttempt->getId());
                /*
                $this->container->set('request', $request, 'request');
                $this->container->get('request_stack')->push($request);
                */
                return $this->forward("ElearningCoursesBundle:Exam:examCompleted");
                //return $this->examCompletedAction($request);
            } else if ($timeended && $isLastAttempt && $lastAttempt->getPassed() === false) {
                /* All exam's failed and all attempts used - this course is failed */
                return $this->redirectToRoute('elearning_course_completed', array('listening_id' => $listening->getId()));
            } else if ($timeended && $isWaitTime) {
                /* Wait time after last attempt not ended. Showing exam result page */
                /* TODO complete fixing this */
                $chapter = $em->getRepository('ElearningCoursesBundle:Course')
                    ->getFirstChapter($listening->getCourseId());

                if ($currentChapter->getId() != $chapter->getId()) {
                    return $this->redirectToRoute('elearning_course_exam_result', array('attempt_id' => $lastAttempt->getId()));
                }
            }
        }

        if (!$examisstarted) {
            $attempt = new ExamAttempt();
            $attempt->setCourseListen($listening);
            $attempt->setExam($exam);
            $em->persist($attempt);

            $questions = $em->getRepository('ElearningCoursesBundle:ExamQuestion')
                ->findBy(array('exam_id' => $exam->getId()), array('ordering' => 'ASC'));
            shuffle($questions);

            $numberofquestions = $exam->getOptions()->numberofquestions;
            $selectedquestions = array_slice($questions, 0, $numberofquestions);
            foreach ($selectedquestions as $question) {
                $attemptanswer = new ExamAttemptAnswer();
                $attemptanswer->setAttempt($attempt);
                $attemptanswer->setQuestion($question);
                $em->persist($attemptanswer);
            }
            $questions = $selectedquestions;
            $em->flush();
        }

        return array($attempt, $questions, $answers);
    }

    private function prepareQuizData($data)
    {
        $shuffleddata = $data;
        shuffle($shuffleddata);
        foreach ($shuffleddata as &$question) {
            shuffle($question['choices']);
        }
        return $shuffleddata;
    }

    private function prepareConnectData($data)
    {
        $choices = $data['choices'];
        foreach ($choices as $key => &$choice) {
            $choice['first']['correct_id'] = $key;
            if ($choice['first']['type'] == "image") {
                $path = __DIR__ . "/../../../../" . $choice['first']['value'];
                if (is_file($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $imagedata = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($imagedata);
                    $choice['first']['value'] = $base64;
                }
            }
            $choice['second']['correct_id'] = $key;
            if ($choice['second']['type'] == "image") {
                $path = __DIR__ . "/../../../../" . $choice['second']['value'];
                if (is_file($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $imagedata = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($imagedata);
                    $choice['second']['value'] = $base64;
                }
            }

        }
        $shuffledchoices = array();
        $shuffledkeys1 = array_keys($choices);
        $shuffledkeys2 = array_keys($choices);
        shuffle($shuffledkeys1);
        shuffle($shuffledkeys2);
        for ($i = 0; $i < count($shuffledkeys1); $i++) {
            $shuffledchoices[] = array('first' => $choices[$shuffledkeys1[$i]]['first'],
                'second' => $choices[$shuffledkeys2[$i]]['second']);
        }
        return $shuffledchoices;
    }

    /* @param $listening_id int listening_id */
    public function completedAction($listening_id)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $user = $this->getUser();

        $params = array();
        $em = $this->getDoctrine()->getManager();

        $listening = $em->find('ElearningCoursesBundle:CourseListening', $listening_id);
        if ($listening->getUserId() != $user->getId()) {
            throw new AccessDeniedException("Not allowed");
        }
        $params['listening'] = $listening;
        $params['course'] = $listening->getCourse();

        $examCourseEnded = false;
        /** @var ExamAttempt[] $attempts */
        $attempts = $em->createQueryBuilder('a')
            ->select('a, CASE WHEN (a.starttime IS null) THEN 0 ELSE 1 END as HIDDEN isstarted')
            ->from('ElearningCoursesBundle:ExamAttempt', 'a')
            ->where('a.listening_id = :listening_id')
            ->orderBy('isstarted', 'ASC')
            ->addOrderBy('a.starttime', 'DESC')
            ->setParameter('listening_id', $listening->getId())
            ->getQuery()
            ->getResult();

        /* This will be not the first attempt */
        if (count($attempts) > 0) {
            $lastAttempt = $attempts[0];
            $spentTime = $lastAttempt->getSpentTime();
            $lastStartDate = null;
            if ($lastAttempt->getStarttime()) {
                $lastStartDate = clone $lastAttempt->getStarttime();
                if ($spentTime) {
                    $lastStartDate->sub(new \DateInterval("PT" . $spentTime . "S"));
                }
            }
            $lastEndtime = $lastAttempt->getEndtime();
            if (!empty($lastEndtime)) {
                $lastEndtime = clone $lastAttempt->getEndtime();
            }
            $exam = $lastAttempt->getExam();
            $maxtime = $exam->getOptions()->maxtime;

            /* Check if exam is already started */
            $started = !empty($lastStartDate);
            $timeended = ($started && $lastStartDate->add(new \DateInterval("PT" . $maxtime . "M")) < new \DateTime()) ||
                !empty($lastEndtime);

            $countAttempts = 0;
            foreach ($attempts as $attempt) {
                if ($attempt->getExamId() === $exam->getId()) {
                    $countAttempts++;
                }
            }

            $isLastAttempt = $countAttempts == $exam->getOptions()->numberofretries;

            $examCourseEnded = ($timeended && $isLastAttempt && $lastAttempt->getPassed() === false);
        }



        foreach ($listening->getCompletion() as $completion) {
            if (!$completion->getCompleted() && $completion->getChapter()->getState() == "published" && !$examCourseEnded) { /* Course is not completed here */
                return $this->redirectToRoute('elearning_course_listen', array('listening_id' => $listening->getId(), 'next' => 'next'));
            }
        }
        $completions = $listening->getCompletion();
        $chapters = $em->getRepository("ElearningCoursesBundle:Chapter")
            ->findBy(array('state' => 'published', 'course_id' => $listening->getCourse()->getId()));
        if (count($completions) < count($chapters) && !$examCourseEnded) { /* Not all chapters are completed */
            return $this->redirectToRoute('elearning_course_listen', array('listening_id' => $listening->getId(), 'next' => 'next'));
        }

        if (!$listening->getCompleted()) {
            $certificate = new Certificate();
            $certificate->setCourseListen($listening);
            $date = new \DateTime();
            $certificate->setIssueDate($date);
            $number = 1;
            $code = $date->format("Ymd") . "-" . $number;
            $found = false;
            $rep = $em->getRepository('ElearningCoursesBundle:Certificate');
            while (!$found) {
                $codeCertificates = $rep->findBy(array('code' => $code));
                if (empty($codeCertificates)) {
                    $found = true;
                } else {
                    $number++;
                    $code = $date->format("Ymd") . "-" . $number;
                }
            }
            $certificate->setCode($code);
            $em->persist($certificate);
            $em->flush();
        }

        $listening->setCompleted(true);
        $em->persist($listening);
        $em->flush();
        
        if (!$listening->getCourse()->getCertificateNeeded()) {
            return $this->redirectToRoute('elearning_course_listen', array('listening_id' => $listening->getId(), 'next' => 'next'));
        }

        return $this->render(sprintf("ElearningCoursesBundle:CourseStudent:course_completed.html.twig"), $params);
    }

    public function chapterCompletedAction($course_id, $listening_id, $chapter_id)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $listening = $em->find("ElearningCoursesBundle:CourseListening", $listening_id);
        if ($listening->getUserId() != $user->getId()) {
            throw new AccessDeniedException("Not allowed");
        }
        $completion = $em->getRepository("ElearningCoursesBundle:CourseCompletion")
            ->findOneBy(array('listen_id' => $listening->getId(), 'chapter_id' => $chapter_id));
        if (empty($completion)) {
            $completion = new CourseCompletion();
            $completion->setChapter($em->getReference("ElearningCoursesBundle:Chapter", $chapter_id));
            $completion->setCourseListen($listening);
        }
        $completion->setCompleted(1);
        $completion->setCompletion(100);
        $completion->setUpdatetime(new \DateTime());
        $em->persist($completion);
        $em->flush();

        return $this->redirectToRoute('elearning_course_listen', array('listening_id' => $listening_id, 'next' => 'next'));
    }

    /**
     * @Security("has_role('ROLE_STUDENT') or has_role('ROLE_MANAGER') or has_role('ROLE_SUPERVISOR')")
     */
    public function certificateAction($listening_id)
    {
        $em = $this->getDoctrine()->getManager();
        $listening = $em->find("ElearningCoursesBundle:CourseListening", $listening_id);

        if (!$listening->getCourse()->getCertificateNeeded()) {
            throw new AccessDeniedException("Not allowed");
        }

        $exampassed = false;
        foreach ($listening->getExamAttempts() as $attempt) {
            if ($attempt->getPassed()) {
                $exampassed = true;
            }
        }
        if (!$exampassed && count($listening->getExamAttempts()) > 0) {
            throw new AccessDeniedException("Not allowed");
        }

        foreach ($listening->getCompletion() as $completion) {
            if (!$completion->getCompleted() && $completion->getChapter()->getState() == "published") { /* Course is not completed here */
                return $this->redirectToRoute('elearning_course_listen', array('listening_id' => $listening->getId(), 'next' => 'next'));
            }
        }

        if (!$listening->getCompleted()) { /* Course is not set as completed here */
            return $this->redirectToRoute('elearning_course_listen', array('listening_id' => $listening->getId(), 'next' => 'next'));
        }


        $certificate = $em->getRepository("ElearningCoursesBundle:Certificate")
            ->findOneBy(array('listen_id' => $listening->getId()));
        if (empty($certificate)) {
            throw new NotFoundHttpException("Certificate is not found");
        }

        $completiondate = $certificate->getIssueDate();
        $code = $certificate->getCode();
        $coursetitle = $listening->getCourse()->getName();
        $user = $listening->getUser();
        $employee = $user->getEmployee();
        $namesurname = $employee->getFieldValue('name') . " " . $employee->getFieldValue('surname');

        $html = $this->renderView('ElearningCoursesBundle:CourseStudent:certificate.html.twig', array(
            'code' => $code,
            'completiondate' => $completiondate,
            'coursetitle' => $coursetitle,
            'namesurname' => $namesurname
        ));


        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                'dpi' => 72,
                'image-quality' => 100,
                'disable-smart-shrinking' => true,
                'image-dpi' => 72,
                'margin-top' => 0,
                'margin-left' => 0,
                'margin-right' => 0,
                'margin-bottom' => 0,
            )),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'filename="certificate.pdf"'
            )
        );
    }


    /**
     * Function for cronjob
     */
    public function sendCourseEndRemindLetters()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT cl
            FROM ElearningCoursesBundle:CourseListening cl
            JOIN cl.course c
            WHERE cl.completed = 0
            AND cl.started IS NOT NULL
            AND DATE_DIFF(DATE_ADD(cl.started, c.listenperiod, "day"), CURRENT_DATE()) = 3'
        );
        $listenings = $query->getResult();

        foreach ($listenings as $listening) {
            $email = $listening->getUser()->getEmail();
            $course = $listening->getCourse();

            $template = 'ElearningCoursesBundle:Emails:course_ending_email.txt.twig';

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

        return new JsonResponse(array('success' => true));
    }

}
