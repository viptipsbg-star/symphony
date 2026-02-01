<?php

namespace Elearning\CoursesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Elearning\CoursesBundle\Entity\CourseListening;
use Elearning\CoursesBundle\Entity\CourseQuestion;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Elearning\CoursesBundle\Form\CourseQuestionType;
use Elearning\CoursesBundle\Form\CourseFaqType;
use Elearning\CoursesBundle\Entity\CourseFaq;

class QuestionsController extends Controller
{

    public function askQuestionAction(Request $request, $listening_id)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $question = $request->request->get('question');
        $listening = $em->find("ElearningCoursesBundle:CourseListening", $listening_id);
        if ($listening->getUserId() != $user->getId()) {
            throw new AccessDeniedException("Not allowed");
        }

        $courseQuestion = new CourseQuestion();
        $courseQuestion->setQuestion($question);
        $courseQuestion->setCourseListening($listening);
        $courseQuestion->setCreatedtime(new \DateTime());
        $courseQuestion->setViewed(false);

        $em->persist($courseQuestion);
        $em->flush();

        $this->notifyLecturerNewQuestion($courseQuestion);

        return $this->redirectToRoute('elearning_course_general_info', array('id' => $listening->getCourse()->getId(), 'listening_id' => $listening_id));
    }

    private function notifyLecturerNewQuestion($question)
    {
        $template = 'ElearningCoursesBundle:Emails:new_question_email.txt.twig';
        $course = $question->getCourseListening()->getCourse();
        $lectureremail = $course->getCreator()->getEmail();
        $viewlink = $this->generateUrl('elearning_courses_question_lecturer', array('question_id' => $question->getId()), true);
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
            ->setTo($lectureremail)
            ->setBody($body, 'text/html');
        $this->get('mailer')->send($message);
    }


    public function questionsListAction(Request $request, $course_id)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $params = array();
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT q
            FROM ElearningCoursesBundle:CourseQuestion q
            JOIN q.courseListening l
            WHERE l.course_id = :course_id
            ORDER BY q.createdtime DESC'
        )->setParameter('course_id', $course_id);

        $questions = $query->getResult();

        $paginator = $this->get('knp_paginator');
        $questions = $paginator->paginate(
            $questions,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        $params['questions'] = $questions;

        return $this->render(sprintf("ElearningCoursesBundle:CourseLecturer:questions_list.html.twig"), $params);
    }

    public function questionAction(Request $request, $question_id)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $params = array();
        $em = $this->getDoctrine()->getManager();

        $question = $em->find("ElearningCoursesBundle:CourseQuestion", $question_id);
        $params['question'] = $question;

        $form = $this->createForm(new CourseQuestionType(), $question, array(
            'action' => $this->generateUrl('elearning_courses_question_lecturer', array('question_id' => $question_id)),
            'method' => 'POST'
        ));


        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $answer = $question->getAnswer();
                $answeredtime = $question->getAnsweredtime();
                if (!empty($answer) && empty($answeredtime)) {
                    $question->setAnsweredtime(new \DateTime());
                    $this->notifyStudentAnswer($question);
                }
                $em->persist($question);
                $em->flush();
                return $this->redirectToRoute('elearning_courses_questions_list_lecturer', array('course_id' => $question->getCourseListening()->getCourse()->getId()));
            }
        }
        $params['form'] = $form->createView();


        return $this->render(sprintf("ElearningCoursesBundle:CourseLecturer:question.html.twig"), $params);
    }

    public function questionDeleteAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        $question = $em->find("ElearningCoursesBundle:CourseQuestion", $id);
        $courseId = $question->getCourseListening()->getCourseId();
        $em->remove($question);
        $em->flush();
        return $this->redirect(
            $this->generateUrl('elearning_courses_questions_list_lecturer', array('course_id' => $courseId))
        );
    }

    private function notifyStudentAnswer($question) {
        $template = 'ElearningCoursesBundle:Emails:new_answer_email.txt.twig';
        $course = $question->getCourseListening()->getCourse();
        $studentemail = $question->getCourseListening()->getUser()->getEmail();
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
            ->setTo($studentemail)
            ->setBody($body, 'text/html');
        $this->get('mailer')->send($message);
    }


    public function faqQuestionsListAction(Request $request, $course_id)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $params = array();
        $em = $this->getDoctrine()->getManager();

        $questions = $em->getRepository('ElearningCoursesBundle:CourseFaq')
            ->findBy(array('course_id' => $course_id));


        $paginator = $this->get('knp_paginator');
        $questions = $paginator->paginate(
            $questions,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );


        $course = $em->find('ElearningCoursesBundle:Course', $course_id);
        $params['course'] = $course;


        $params['questions'] = $questions;

        return $this->render(sprintf("ElearningCoursesBundle:CourseLecturer:faq_questions_list.html.twig"), $params);
    }

    public function faqQuestionAction(Request $request, $question_id = null)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $params = array();
        $em = $this->getDoctrine()->getManager();

        if (!empty($question_id)) {
            $question = $em->find("ElearningCoursesBundle:CourseFaq", $question_id);
        } else {
            $question = new CourseFaq();
            $course_question_id = $request->query->get('course_question');
            if (!empty($course_question_id)) {
                $coursequestion = $em->find("ElearningCoursesBundle:CourseQuestion", $course_question_id);
                $question->setQuestion($coursequestion->getQuestion());
                $question->setAnswer($coursequestion->getAnswer());
                $course = $coursequestion->getCourseListening()->getCourse();
                $question->setCourse($course);
                $question->setCourseId($course->getId());
                $course->addQuestion($question);
                $em->persist($course);
            }
            $course_id = $request->query->get('course_id');
            if (!empty($course_id)) {
                $course = $em->find("ElearningCoursesBundle:Course", $course_id);
                $question->setCourse($course);
                $question->setCourseId($course->getId());
            }
        }

        $params['question'] = $question;

        $form = $this->createForm(new CourseFaqType(), $question, array(
            'action' => $this->generateUrl('elearning_courses_faq_question_lecturer', array('question_id' => $question_id)),
            'method' => 'POST'
        ));


        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $question->setCourse($em->getReference("ElearningCoursesBundle:Course", $question->getCourseId()));
                $createdtime = $question->getCreatedtime();
                if (empty($createdtime)) {
                    $question->setCreatedtime(new \DateTime());
                }
                $em->persist($question);
                $em->flush();
                return $this->redirectToRoute('elearning_courses_faq_questions_list_lecturer', array('course_id' => $question->getCourse()->getId()));
            }
        }
        $params['form'] = $form->createView();


        return $this->render(sprintf("ElearningCoursesBundle:CourseLecturer:faq_question.html.twig"), $params);
    }

    public function faqQuestionDeleteAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        $question = $em->find("ElearningCoursesBundle:CourseFaq", $id);
        $courseId = $question->getCourseId(); 
        $em->remove($question);
        $em->flush();
        return $this->redirect(
            $this->generateUrl('elearning_courses_faq_questions_list_lecturer', array('course_id' => $courseId))
        );
    }
}