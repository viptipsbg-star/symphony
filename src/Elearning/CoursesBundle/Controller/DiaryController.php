<?php

namespace Elearning\CoursesBundle\Controller;

use Elearning\CoursesBundle\Entity\DiaryCriterionGroup;
use Elearning\CoursesBundle\Entity\DiaryRate;
use Elearning\CoursesBundle\Entity\Topic;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elearning\CoursesBundle\Form\DiaryTopicType;
use Elearning\CoursesBundle\Entity\DiaryTopic;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DiaryController extends Controller
{

    public function editAction($employee_id, $format)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $params = array();

        $em = $this->getDoctrine()->getManager();

        $current_employee = $em->getRepository("ElearningCompaniesBundle:Employee")->find($employee_id);

        if (!$current_employee) {
            throw $this->createNotFoundException('Not found employee');
        }

        $current_user = $this->getUser();
        $current_manager = $em->getRepository("ElearningCompaniesBundle:Employee")->findOneBy(array('user_id' => $current_user->getId()));
        $granted = $this->isGrantedGroup($current_manager, $current_employee);

        $diary_topics = $current_employee->getActiveDiaryTopics();
        $rates = array();
        foreach ($diary_topics as $topic) {
            foreach ($topic->getRates() as $rate) {
                $rates[$topic->getId() . '_' . $rate->getCriterionId()] = $rate->getRate();
            }
        }

        $criteria_groups = $this->prepareCriteriaGroups();

        $diary_topic = new DiaryTopic();
        $form = $this->createForm(new DiaryTopicType(), $diary_topic, array(
            'action' => $this->generateUrl('elearning_course_diary_add_topic'),
            'method' => 'POST'
        ));

        $params['diary_topic_form'] = $form->createView();
        $params['current_employee'] = $current_employee;
        $params['criteria_groups'] = $criteria_groups;
        $params['diary_topics'] = $diary_topics;
        $params['rates'] = $rates;
        $params['granted'] = $granted;
        $params['student'] = false;

        if ($format == 'excel') {
            return $this->exportDiaryXLSX($params);
        }

        return $this->render('ElearningCoursesBundle:CourseLecturer:diary.html.twig', $params);
    }

    public function addRateAction($diary_topic_id, $criterion_id, $rate = null)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $em = $this->getDoctrine()->getManager();

        $current_user = $this->getUser();
        $current_manager = $em->getRepository("ElearningCompaniesBundle:Employee")->findOneBy(array('user_id' => $current_user->getId()));

        $diary_topic = $em->getReference('ElearningCoursesBundle:DiaryTopic', $diary_topic_id);
        $employee = $diary_topic->getEmployee();

        if (!$this->isGrantedGroup($current_manager, $employee)) {
            return new JsonResponse(array('success' => false));
        }

        $diary_rate = $em->getRepository('ElearningCoursesBundle:DiaryRate')->findOneBy(array('diary_topic_id' => $diary_topic_id, 'criterion_id' => $criterion_id));

        if (!$diary_rate) {
            $diary_rate = new DiaryRate();
            $diary_rate->setDiaryTopic($diary_topic);
            $diary_rate->setCriterion($em->getReference('ElearningCoursesBundle:DiaryCriterion', $criterion_id));
        }

        if ($rate != 'null') {
            $diary_rate->setRate($rate);
            $em->persist($diary_rate);
        } else {
            $em->remove($diary_rate);
        }

        $em->flush();
        return new JsonResponse(array('success' => true));
    }

    public function addDiaryTopicAction(Request $request)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $em = $this->getDoctrine()->getManager();

        $current_user = $this->getUser();
        $current_manager = $em->getRepository("ElearningCompaniesBundle:Employee")->findOneBy(array('user_id' => $current_user->getId()));

        $form_data = $request->get('diary_topic');
        $diary_topic = new DiaryTopic();
        $employee_id = $form_data['employee_id'];
        $employee = $em->getReference('ElearningCompaniesBundle:Employee', $employee_id);

        if (!$this->isGrantedGroup($current_manager, $employee)) {
            return new JsonResponse(array('success' => false));
        }

        $topic = $em->getRepository("ElearningCoursesBundle:Topic")->find($form_data['topic']);
        if (!$topic) {
            $topic = $em->getRepository("ElearningCoursesBundle:Topic")->findOneBy(array('text' => $form_data['topic']));
            if ($topic)
                $form_data['topic'] = $topic->getId();
        }

        if (!$topic) {
            $topic = new Topic();
            $topic->setText($form_data['topic']);
            $em->persist($topic);
            $em->flush();
            $form_data['topic'] = $topic->getId();
        }

        $form = $this->createForm(new DiaryTopicType(), $diary_topic);

        $form->submit($form_data);

        if ($form->isValid()) {
            $diary_topic->setEmployee($employee);
            $diary_topic->setCreatorEmployee($current_manager);
            $em->persist($diary_topic);
            $em->flush();
            return new JsonResponse(array('success' => true));
        }

        return new JsonResponse(array('success' => false));
    }

    public function removeDiaryTopicAction($diary_topic_id)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $em = $this->getDoctrine()->getManager();

        $diary_topic = $em->getRepository('ElearningCoursesBundle:DiaryTopic')->find($diary_topic_id);
        if ($diary_topic) {
            $employee = $diary_topic->getEmployee();
            $current_user = $this->getUser();
            $current_manager = $em->getRepository("ElearningCompaniesBundle:Employee")->findOneBy(array('user_id' => $current_user->getId()));
            if (!$this->isGrantedGroup($current_manager, $employee)) {
                return new JsonResponse(array('success' => false));
            }

            $diary_topic->setActive(false);
            $em->persist($diary_topic);
            $em->flush();
            return new JsonResponse(array('success' => true));
        }


        return new JsonResponse(array('success' => false));
    }

    public function topicCommentAction(Request $request)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $topic_id = $request->request->get('id');
        $comment = $request->request->get('comment');

        $em = $this->getDoctrine()->getManager();

        if ($topic_id) {
            $topic = $em->getRepository('ElearningCoursesBundle:DiaryTopic')->find($topic_id);
        }

        if ($topic) {
            $employee = $topic->getEmployee();
            $current_user = $this->getUser();
            $current_manager = $em->getRepository("ElearningCompaniesBundle:Employee")->findOneBy(array('user_id' => $current_user->getId()));
            if (!$this->isGrantedGroup($current_manager, $employee)) {
                return new JsonResponse(array('success' => false));
            }

            $topic->setComment($comment);
            $em->persist($topic);
            $em->flush();
            return new JsonResponse(array('success' => true));
        }

        return new JsonResponse(array('success' => false));
    }

    private function prepareCriteriaGroups()
    {
        $em = $this->getDoctrine()->getManager();

        $criteriaGroups = $em->getRepository("ElearningCoursesBundle:DiaryCriterionGroup")->findBy(array('active' => 1), array('ordering' => 'ASC'));
        $criteriaWoGroup = $em->getRepository("ElearningCoursesBundle:DiaryCriterion")->findBy(array('active' => 1, 'criterion_group_id' => NULL), array('ordering' => 'ASC'));
        $emptyGroup = new DiaryCriterionGroup();
        $emptyGroup->setText('');
        foreach ($criteriaWoGroup as $criterion) {
            $emptyGroup->addCriterium($criterion);
        }

        array_unshift($criteriaGroups, $emptyGroup);

        return $criteriaGroups;
    }

    private function isGrantedGroup($current_manager, $current_employee)
    {
        if ($this->isGranted('ROLE_SUPERVISOR')) {
            return true;
        }

        if ($this->isGranted('ROLE_MANAGER')) {
            $employee_groups = $current_employee->getGroups();
            $manager_groups = $current_manager->getGroups();

            foreach ($employee_groups as $employee_group) {
                foreach ($manager_groups as $manager_group) {
                    if ($employee_group->getId() == $manager_group->getId()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function exportDiaryXLSX($params)
    {
        $excelService = $this->get('phpexcel');
        $translator = $this->get('translator');
        $phpExcelObject = $excelService->createPHPExcelObject();
        $phpExcelObject->setActiveSheetIndex(0);
        $sheet = $phpExcelObject->getActiveSheet();
        $employeeName = $params['current_employee']->getFieldValue("name") . ' ' . $params['current_employee']->getFieldValue("surname");
        $sheet->setTitle($employeeName);

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $row = 1;
        $column = 0;

        $sheet->setCellValueByColumnAndRow($column, $row, $translator->trans('diary.date'));
        $sheet->setCellValueByColumnAndRow($column, $row + 1, $translator->trans('diary.supervisor'));
        $sheet->setCellValueByColumnAndRow($column, $row + 2, $translator->trans('diary.topic'));

        $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        $sheet->getStyleByColumnAndRow($column, $row + 2)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $column = 1;
        foreach ($params['diary_topics'] as $topic) {

            $sheet->setCellValueByColumnAndRow($column, $row, $topic->getIssued()->format('Y-m-d'));
            if ($topic->getCreatorEmployee()->getFieldValue("name")) {
                $sheet->setCellValueByColumnAndRow($column, $row + 1, $topic->getCreatorEmployee()->getFieldValue("name") . ' ' . $topic->getCreatorEmployee()->getFieldValue("surname"));
            } else {
                $sheet->setCellValueByColumnAndRow($column, $row + 1, $topic->getCreatorEmployee()->getUser()->getUsername());
            }
            $sheet->setCellValueByColumnAndRow($column, $row + 2, $topic->getTopic()->getText());

            $sheet->getStyleByColumnAndRow($column, $row + 2)->getAlignment()->setWrapText(true);
            $sheet->getColumnDimensionByColumn($column)->setWidth(15);
            $sheet->getStyleByColumnAndRow($column, $row + 2)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($column, $row + 2)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $column++;
        }


        $row = 4;
        $column = 0;
        foreach ($params['criteria_groups'] as $group) {
            $column = 0;
            if ($group->getText()) {
                $groupTitle = $group->getText();
                if ($group->getDescription()) {
                    $groupTitle .= " \n" . $group->getDescription();
                }
                $sheet->setCellValueByColumnAndRow($column, $row, $groupTitle);
                $row++;
            }

            foreach ($group->getCriteria() as $criterion) {
                $column = 0;
                $sheet->setCellValueByColumnAndRow($column, $row, $criterion->getText());
                $column++;
                foreach ($params['diary_topics'] as $topic) {
                    $ndx = $topic->getId() . '_' . $criterion->getId();
                    if (isset($params['rates'][$ndx]) && $params['rates'][$ndx]) {
                        $sheet->setCellValueByColumnAndRow($column, $row, $params['rates'][$ndx]);
                    }
                    $column++;
                }
                $row++;
            }
        }

        $column = 0;
        $sheet->setCellValueByColumnAndRow($column, $row, $translator->trans('diary.comment'));

        $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $column++;
        foreach ($params['diary_topics'] as $topic) {
            $sheet->setCellValueByColumnAndRow($column, $row, $topic->getComment());

            $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setWrapText(true);
            $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $column++;
        }

        if ($column >= 1) {
            $sheet->getStyle('A1:' . \PHPExcel_Cell::stringFromColumnIndex($column - 1) . $row)->applyFromArray($styleArray);
        }

        $objWriter = $excelService->createWriter($phpExcelObject, 'Excel2007');
        $response = $excelService->createStreamedResponse($objWriter);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'diary.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    function studentDiaryAction()
    {
        if (!$this->isGranted('ROLE_STUDENT')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $em = $this->getDoctrine()->getManager();

        $currentUser = $this->getUser();
        $currentEmployee = $em->getRepository("ElearningCompaniesBundle:Employee")->findOneBy(array('user_id' => $currentUser->getId()));

        if (!$currentEmployee) {
            return $this->render('ElearningCoursesBundle:CourseLecturer:diary.html.twig', array(
                'current_employee' => null,
                'criteria_groups' => array(),
                'diary_topics' => array(),
                'rates' => array(),
                'granted' => false,
                'student' => true
            ));
        }

        $diaryTopics = $currentEmployee->getActiveDiaryTopics();
        $rates = array();
        foreach ($diaryTopics as $topic) {
            foreach ($topic->getRates() as $rate) {
                $rates[$topic->getId() . '_' . $rate->getCriterionId()] = $rate->getRate();
            }
        }

        $criteriaGroups = $this->prepareCriteriaGroups();

        $params['current_employee'] = $currentEmployee;
        $params['criteria_groups'] = $criteriaGroups;
        $params['diary_topics'] = $diaryTopics;
        $params['rates'] = $rates;
        $params['granted'] = false;
        $params['student'] = true;

        return $this->render('ElearningCoursesBundle:CourseLecturer:diary.html.twig', $params);
    }
}
