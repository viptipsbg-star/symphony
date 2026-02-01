<?php

namespace Elearning\CoursesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elearning\CoursesBundle\Entity\Feedback;
use Elearning\CoursesBundle\Entity\FeedbackData;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FeedbackController extends Controller
{

    public function getAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter_id = $request->query->get('chapter_id');
        $em = $this->getDoctrine()->getManager();
        $feedback = $em->getRepository('ElearningCoursesBundle:Feedback')
            ->findOneBy(array('chapter_id' => $chapter_id));
        if ($feedback) {
            return new JsonResponse(array('success' => true, 'feedback' => array(
                'id' => $feedback->getId(),
                'data' => $feedback->getData()
            )));
        }
        return new JsonResponse(array('success' => false));
    }

    public function saveAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');
        $questions = $request->request->get('questions');
        $chapter_id = $request->request->get('chapter_id');

        $data = array();
        foreach ($questions as $key => $question) {
            $data[] = array('fieldname' => $question['text'], 'type' => $question['type'], 'choices' => $question['choice']);
        }

        $feedback = ($id)
            ? $em->getRepository('ElearningCoursesBundle:Feedback')->find($id)
            : new Feedback();

        $feedback->setData($data);
        $feedback->setChapter($em->getReference('ElearningCoursesBundle:Chapter', $chapter_id));
        $em->persist($feedback);
        $em->flush();
        return new JsonResponse(array('success' => true, 'id' => $feedback->getId()));
    }


    public function saveAnswersAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $answers = $request->request->get('answers');
        $chapter_id = $request->request->get('chapter_id');
        $listen_id = $request->request->get('listen_id');
        $user = $this->getUser();

        $listening = $em->find("ElearningCoursesBundle:CourseListening", $listen_id);
        if ($listening->getUserId() != $user->getId()) {
            throw new AccessDeniedException("Not allowed");
        }

        $feedback = $em->getRepository('ElearningCoursesBundle:Feedback')
            ->findOneBy(array('chapter_id' => $chapter_id, 'version' => 'public'));

        $feedbackData = new FeedbackData();
        $feedbackData->setFeedback($feedback);
        $feedbackData->setSubmitdate(new \DateTime());
        $feedbackData->setCourseListen($listening);
        $feedbackData->setData($answers);
        $em->persist($feedbackData);
        $em->flush();

        return $this->redirectToRoute('elearning_course_chapter_completed',
            array('course_id' => $listening->getCourse()->getId(),
                'listening_id' => $listening->getId(),
                'chapter_id' => $chapter_id));
    }


    public function listAction(Request $request, $course_id)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');
        $params = array();
        $view = $request->query->get('view') ? $request->query->get('view') : '';

        $em = $this->getDoctrine()->getManager();
        $course = $em->find('ElearningCoursesBundle:Course', $course_id);

        $query = $em->createQuery(
            "SELECT fd
             FROM ElearningCoursesBundle:FeedbackData fd
             JOIN fd.feedback f
             JOIN f.chapter ch              
             WHERE ch.course_id = :course_id")
            ->setParameter('course_id', $course->getId());
        $feedbacks = $query->getResult();

        if ($view == 'xlsx') {
            return $this->exportFeedbacksXLSX($feedbacks);
        }

        $paginator = $this->get('knp_paginator');
        $feedbacks = $paginator->paginate(
            $feedbacks,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        $params['feedbacks'] = $feedbacks;
        $params['course'] = $course;

        return $this->render(sprintf("ElearningCoursesBundle:CourseLecturer:feedbacks_list.html.twig"), $params);
    }

    public function exportFeedbacksXLSX ($feedbacks)
    {
        $data = array();
        $questions = array();
        $listenings = array();
        foreach ($feedbacks as $feedback) {
            if (!isset($questions[$feedback->getFeedback()->getId()])) {
                $questions[$feedback->getFeedback()->getId()] = $feedback->getFeedback()->getData();
            }
            $data[$feedback->getFeedback()->getId()][] = $feedback;
            $listenings[$feedback->getListenId()] = true;
        }

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            "SELECT l, c, u, e, f
             FROM ElearningCoursesBundle:CourseListening l
             LEFT JOIN l.certificate c
             JOIN l.user u
             JOIN u.employee e
             JOIN e.fields f
             WHERE l.id IN (:listenings)
             AND f.fieldname IN ('name', 'surname')")
            ->setParameter('listenings', array_keys($listenings));
        $listeningsData = $query->getResult();
        $employees = array();

        foreach ($listeningsData as $listening) {
            $employee = $listening->getUser()->getEmployee();
            $employees[$listening->getId()]['name'] = $employee->getFieldValue('name');
            $employees[$listening->getId()]['surname'] = $employee->getFieldValue('surname');
        }

        $excelService = $this->get('phpexcel');
        $phpExcelObject = $excelService->createPHPExcelObject();
        $phpExcelObject->setActiveSheetIndex(0);
        $sheet = $phpExcelObject->getActiveSheet();
        $sheet->setTitle('Sheet1');

        $row = 1;
        $column = 0;

        foreach ($questions as $feedbackId => $question) {
            $questionKey = 0;
            foreach ($question as $questionData) {
                $sheet->setCellValueByColumnAndRow($column, $row, $questionData->fieldname . " ($feedbackId)");
                $sheet->mergeCellsByColumnAndRow($column, $row, $column + 1, $row);
                $sheet->getStyle("A" . $row)->getFont()->setBold(true);
                $row++;
                foreach($data[$feedbackId] as $answerData) {
                    if (isset($answerData->getData()[$questionKey]) && $answerData->getData()[$questionKey] !== '') {
                        $listeningId = $answerData->getListenId();
                        $name = isset($employees[$listeningId]['name']) ? $employees[$listeningId]['name'] : '';
                        $surname = isset($employees[$listeningId]['surname']) ? $employees[$listeningId]['surname'] : '';
                        $sheet->setCellValueByColumnAndRow($column, $row, $name . ' ' . $surname);
                        $column++;
                        $sheet->setCellValueByColumnAndRow($column, $row, $questionData->type == 'select' ? implode(', ', $answerData->getData()[$questionKey]) : $answerData->getData()[$questionKey]);
                        $column = 0;
                        $row++;
                    }
                }
                $questionKey++;
            }

        }

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        if ($row > 0) {
            $dimension = 'A1:' . \PHPExcel_Cell::stringFromColumnIndex(1) . ($row - 1);
            $sheet->getStyle($dimension)->applyFromArray($styleArray);
            $sheet->getDefaultColumnDimension()->setWidth(40);
            $sheet->getStyle($dimension)->getAlignment()->setWrapText(true);
        }

        $objWriter = $excelService->createWriter($phpExcelObject, 'Excel2007');
        $response = $excelService->createStreamedResponse($objWriter);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'feedbacks.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

}
