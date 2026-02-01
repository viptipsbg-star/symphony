<?php

namespace Elearning\CompaniesBundle\Controller;

use Elearning\CompaniesBundle\Entity\Group;
use Elearning\CompaniesBundle\Form\EmployeeProfileFieldsType;
use Elearning\CompaniesBundle\Form\GroupTitleType;
use Elearning\CompaniesBundle\Form\MessageType;
use Elearning\CoursesBundle\Entity\GroupCourse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;

class GroupsController extends Controller
{
    public function groupsAction($group_id = 0)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $adminA2 = $this->isGranted('ROLE_ADMIN_A2');
        $adminA1 = $this->isGranted('ROLE_ADMIN_A1');

        $em = $this->getDoctrine()->getManager();
        $params = array();
        $companies_manager = $this->get("elearning_companies.companies_manager");
        $company = $companies_manager->currentUserCompany();

        $groupsQb = $em->createQueryBuilder()
            ->select('g')
            ->from('ElearningCompaniesBundle:Group', 'g')
            ->leftJoin('g.employees', 'e')
            ->where('g.company_id = :company_id')
            ->andWhere('g.state = :state')
            ->setParameters(array(
                    'company_id' => $company->getId(),
                    'state' => 'published'
                )
            );

        if ($adminA2 || $adminA1) {
            $user = $this->getUser();
            $userIds = array($user->getId());
            $adminService = $this->get('elearning.admin_service');
            $adminIds = $adminService->getChildrenIds($user);
            $userIds = array_merge($userIds, $adminIds);
            if ($adminA2) {
                $groupsQb
                    ->andWhere('e.user_id in (:user_ids)')
                    ->setParameter('user_ids', $userIds);
            }
        }

        $groups = $groupsQb->getQuery()->getResult();

        $employeesQb = $em->createQueryBuilder()
            ->select('e, f')
            ->from('ElearningCompaniesBundle:Employee', 'e')
            ->join('e.fields', 'f')
            ->where('e.company_id = :company_id')
            ->andWhere('e.type in (:type)')
            ->setParameters(array(
                'company_id' => $company->getId(),
                'type' => array('employee')
            ));

        $params['employees'] = $employeesQb->getQuery()->getResult();

        $employeesQb->setParameters(array(
            'company_id' => $company->getId(),
            'type' => array('manager', 'administrator')
        ));

        if ($adminA2 || $adminA1) {
            $employeesQb
                ->andWhere('e.user_id IN (:user_ids)')
                ->setParameter('user_ids', $adminIds);
        }
        $params['managers'] = $employeesQb->getQuery()->getResult();


        if (!empty($groups)) {
            if ($group_id == 0) {
                $currentgroup = $groups[0];
            } else {
                $correctGroup = false;
                foreach ($groups as $group) {
                    if ($group_id == $group->getId()) {
                        $correctGroup = true;
                    }
                }
                if (!$correctGroup) {
                    throw $this->createAccessDeniedException('Unable to access this page!');
                }
                $currentgroup = $em->find("ElearningCompaniesBundle:Group", $group_id);
            }
            $params['currentgroup'] = $currentgroup;

            $groupUsersQb = $em->createQueryBuilder()
                ->select('e')
                ->from('ElearningCompaniesBundle:Employee', 'e')
                ->join('e.groups', 'g')
                ->where('g.id = :group_id')
                ->andWhere('e.type IN (:type)')
                ->setParameter('group_id', $currentgroup->getId());

            $currentgroupemployees = $groupUsersQb
                ->setParameter('type', array('employee'))
                ->getQuery()->getResult();

            if ($adminA2 || $adminA1) {
                $groupUsersQb
                    ->andWhere('e.user_id IN (:user_ids)')
                    ->setParameter('user_ids', $adminIds);
            }

            $currentgroupmanagers = $groupUsersQb
                ->setParameter('type', array('manager', 'administrator'))
                ->getQuery()->getResult();

            $params['currentgroupemployees'] = $currentgroupemployees;
            $params['currentgroupmanagers'] = $currentgroupmanagers;
        }
        $params['groups'] = $groups;

        $minLevel = 0;
        $rootLft = null;
        $rootRgt = null;
        
        if ($adminA1 || $adminA2) {
            $admin = $em->getRepository('ElearningCompaniesBundle:Administrator')
                ->findOneBy(array('user_id' => $user->getId()));
            $minLevel = $admin->getLvl();
            $rootLft = $admin->getLft();
            $rootRgt = $admin->getRgt();
        }
		
		$appConfig = $this->getParameter('app_config');		
		$showParent = !empty($appConfig['extended_admins_structure']) && $appConfig['extended_admins_structure'];		
		
        $formOptions = array(
            'isSupervisor' => $this->isGranted('ROLE_SUPERVISOR'),
            'canCreateAdmin' => !$adminA2,
            'minLevel' => $minLevel,
            'rootLft' => $rootLft,
            'rootRgt' => $rootRgt,
			'showParent' => $showParent
        );
        
        $form = $this->createForm(new EmployeeProfileFieldsType(), null, $formOptions);
        $params['employee_fields_form'] = $form->createView();


        $courses = $em->getRepository("ElearningCoursesBundle:Course")
            ->findBy(array('status' => "published"));

        $params['courses'] = $courses;


        $groupcourses = array();
        if (isset($currentgroup)) {
            $groupassignedcourses = $currentgroup->getGroupCourses();
            foreach ($groupassignedcourses as $groupcourse) {
                if ($groupcourse->getActive()) {
                    $groupcourses[] = $groupcourse->getCourse();
                }
            }
        }
        $params['groupassignedcourses'] = $groupcourses;


        $allUsersQb = $em->createQueryBuilder()
            ->select('DISTINCT ef.fieldvalue')
            ->from('ElearningCompaniesBundle:EmployeeProfileField', 'ef')
            ->join('ef.employee', 'e')
            ->where('ef.fieldname = :fieldname')
            ->andWhere('e.type IN (:type)');

        $employeesPositionsData = $allUsersQb->setParameter('fieldname', "position")
            ->setParameter("type", array("employee"))
            ->getQuery()->getResult();
        $employeesPositions = array();
        foreach ($employeesPositionsData as $field) {
            $employeesPositions[] = $field['fieldvalue'];
        }
        $params['employeespositions'] = $employeesPositions;

        $employeesAddressesData = $allUsersQb->setParameter('fieldname', "address")
            ->setParameter("type", array("employee"))
            ->getQuery()->getResult();
        $employeesAddresses = array();
        foreach ($employeesAddressesData as $field) {
            $employeesAddresses[] = $field['fieldvalue'];
        }
        $params['employeesaddresses'] = $employeesAddresses;

        $managersPositionsData = $allUsersQb->setParameter('fieldname', "position")
            ->setParameter("type", array("manager", "administrator"))
            ->getQuery()->getResult();
        $managersPositions = array();
        foreach ($managersPositionsData as $field) {
            $managersPositions[] = $field['fieldvalue'];
        }
        $params['managerspositions'] = $managersPositions;

        $managersAddressesData = $allUsersQb->setParameter('fieldname', "address")
            ->setParameter("type", array("manager", "administrator"))
            ->getQuery()->getResult();
        $managersAddresses = array();
        foreach ($managersAddressesData as $field) {
            $managersAddresses[] = $field['fieldvalue'];
        }
        $params['managersaddresses'] = $managersAddresses;

        $message_form = $this->createForm(new MessageType());
        $params['message_form'] = $message_form->createView();
        
        $groupTitleForm = $this->createForm(new GroupTitleType(), null, array(
            'method' => 'POST',
            'action' => $this->generateUrl('elearning_companies_group_title_update')
        ));
        $params['group_title_form'] = $groupTitleForm->createView();  

        return $this->render('ElearningCompaniesBundle:Groups:groups.html.twig', $params);
    }


    public function newAction()
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $params = array();
        $companies_manager = $this->get("elearning_companies.companies_manager");
        $company = $companies_manager->currentUserCompany();

        $session = $this->getRequest()->getSession();
        $session->set("ElearningCompaniesBundle.groupEmployees", array());

        $employeesQb = $em->createQueryBuilder()
            ->select('e, f')
            ->from('ElearningCompaniesBundle:Employee', 'e')
            ->join('e.fields', 'f')
            ->where('e.company_id = :company_id')
            ->andWhere('e.type in (:type)')
            ->setParameters(array(
                'company_id' => $company->getId(),
                'type' => array('employee')
            ));


        $params['employees'] = $employeesQb->getQuery()->getResult();

        $employeesQb->setParameters(array(
            'company_id' => $company->getId(),
            'type' => array('manager', 'administrator')
        ));

        $minLevel = 0;
        $rootLft = null;
        $rootRgt = null;
        
        if ($this->isGranted('ROLE_ADMIN_A1') || $this->isGranted('ROLE_ADMIN_A2')) {
            $user = $this->getUser();
            $adminService = $this->get('elearning.admin_service');
            $adminIds = $adminService->getChildrenIds($user);
            $employeesQb
                ->andWhere('e.user_id IN (:user_ids)')
                ->setParameter('user_ids', $adminIds);
            
            $admin = $em->getRepository('ElearningCompaniesBundle:Administrator')
                ->findOneBy(array('user_id' => $user->getId()));
            $minLevel = $admin->getLvl();
            $rootLft = $admin->getLft();
            $rootRgt = $admin->getRgt();
        }

		$appConfig = $this->getParameter('app_config');		
		$showParent = !empty($appConfig['extended_admins_structure']) && $appConfig['extended_admins_structure'];		
		
        $formOptions = array(
            'isSupervisor' => $this->isGranted('ROLE_SUPERVISOR'),
            'canCreateAdmin' => !$this->isGranted('ROLE_ADMIN_A2'),
            'minLevel' => $minLevel,
            'rootLft' => $rootLft,
            'rootRgt' => $rootRgt,
			'showParent' => $showParent
        );
        
        $params['managers'] = $employeesQb->getQuery()->getResult();

        $form = $this->createForm(new EmployeeProfileFieldsType(), null, $formOptions);
        $params['employee_fields_form'] = $form->createView();

        return $this->render('ElearningCompaniesBundle:Groups:new_group.html.twig', $params);
    }

    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $grouptitle = $request->request->get('group_title');

        $companies_manager = $this->get("elearning_companies.companies_manager");
        $company = $companies_manager->currentUserCompany();

        $session = $this->getRequest()->getSession();
        $employees = $session->get('ElearningCompaniesBundle.groupEmployees', array());
        $user = $this->getUser();
        if ($user->getEmployee()) {
            $employees[] = $user->getEmployee()->getId();
        }

        $group = new Group();
        $group->setTitle($grouptitle);
        $group->setCreated(new \Datetime());
        $group->setCompany($company);
        $group->setState("published");
        foreach ($employees as $empl_id) {
            $employee = $em->getReference("ElearningCompaniesBundle:Employee", $empl_id);
            $group->addEmployee($employee);
            $employee->addGroup($group);
        }
        $em->persist($group);
        $em->flush();

        return $this->redirectToRoute("elearning_companies_groups");
    }

    public function deleteAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $id = $request->request->get('id');

        $em = $this->getDoctrine()->getManager();
        $group = $em->find("ElearningCompaniesBundle:Group", $id);
        $group->setState("deleted");
        $em->persist($group);
        $em->flush();


        $response = array('success' => true);
        return new JsonResponse($response);
    }

    public function assignCourseAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $course_id = $request->request->get('id');
        $group_id = $request->request->get('group_id');

        $course = $em->find("ElearningCoursesBundle:Course", $course_id);
        $group = $em->find("ElearningCompaniesBundle:Group", $group_id);

        if (count($group->getEmployees()) == 0) {
            $translator = $this->get('translator');
            $response = array('success' => false, 'message' => $translator->trans("groups.course_assign_empty_group"));
            return new JsonResponse($response);
        }

        $groupcourse = new GroupCourse();
        $groupcourse->setCourse($course);
        $groupcourse->setGroup($group);
        $groupcourse->setCreated(new \DateTime());
        $groupcourse->setActive(true);
        $em->persist($groupcourse);

        foreach ($group->getEmployees() as $employee) {
            $email = $employee->getUser()->getEmail();
            if (strpos($email, "email.loc") !== FALSE) {
                continue;
            }

            $template = 'ElearningCoursesBundle:Emails:course_assigned_email.txt.twig';

            $viewlink = $this->generateUrl('elearning_course_general_info', array('id' => $course->getId()), true);
            $rendered = $this->get('templating')->render($template, array(
                'coursename' => $course->getName(),
                'viewlink' => $viewlink
            ));

            // Render the email, use the first line as the subject, and the rest as the body
            $renderedLines = explode("\n", trim($rendered));
            $subject = $renderedLines[0];
            $body = implode("\n", array_slice($renderedLines, 1));

            if (\Swift_Validate::email($email)) {

                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom(array($this->getParameter('mailer_from_email')['address'] => $this->getParameter('mailer_from_email')['sender_name']))
                    ->setTo($email)
                    ->setBody($body, 'text/html');
                $this->get('mailer')->send($message);
            }

        }

        $em->flush();

        $response = array('success' => true);
        return new JsonResponse($response);
    }

    public function cancelCourseAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $course_id = $request->request->get('id');
        $group_id = $request->request->get('group_id');

        $group = $em->find("ElearningCompaniesBundle:Group", $group_id);

        if (empty($group)) {
            $translator = $this->get('translator');
            $response = array('success' => false, 'message' => $translator->trans("groups.course_cancel_no_group"));
            return new JsonResponse($response);
        }

        $groupCourses = $em->getRepository("ElearningCoursesBundle:GroupCourse")
            ->findBy(array('course_id' => $course_id, 'group_id' => $group_id));
        foreach ($groupCourses as $groupCourse) {
            $groupCourse->setActive(false);
            $em->persist($groupCourse);
        }
        $em->flush();

        $response = array('success' => true);
        return new JsonResponse($response);
    }

    public function sendEmailAction(Request $request)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }
        
        $success = false;
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');
        $messageForm = $this->createForm(new MessageType());
        $groupId = $request->request->get('group_id');

        $messageForm->handleRequest($request);
        if ($messageForm->isValid()) {
            $group = $em->find('ElearningCompaniesBundle:Group', $groupId);
            if ($group) {
                $message = $messageForm->getData();
                $messageService = $this->get('elearning.message_service');
                $messageService->sendEmail($group, $message);
                $success = true;
            }
        }

        $params['success'] = $success;
        if ($success) {
            $params['message'] = $translator->trans('employees.send_email.success');
        }
        return new JsonResponse($params);
    }

    public function groupTitleUpdateAction(Request $request)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }
        
        $form = $this->createForm(new GroupTitleType());
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $group = $em->getRepository('ElearningCompaniesBundle:Group')->find($data['group_id']);
            if (!$group) {
                throw $this->createNotFoundException('Group not found');
            }
            
            $group->setTitle($data['title']);
            $em->persist($group);
            $em->flush();
        }
        
        return $this->redirect($this->generateUrl('elearning_companies_groups'));
    }
}
