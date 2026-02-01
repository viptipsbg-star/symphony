<?php

namespace Elearning\CompaniesBundle\Controller;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\QueryBuilder;
use Elearning\CompaniesBundle\Entity\Administrator;
use Elearning\CompaniesBundle\Entity\Employee;
use Elearning\CompaniesBundle\Entity\EmployeeProfileField;
use Elearning\CompaniesBundle\Form\EmployeeProfileFieldsType;
use Elearning\CompaniesBundle\Form\MessageType;
use Elearning\UserBundle\Entity\User;
use Elearning\UserBundle\Event\RegistrationEvent;
use Elearning\UserBundle\UserEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EmployeesController extends Controller
{

    /**
     * Creates new employee
     * @param Request $request
     * @return JsonResponse Success state and message
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $translator = $this->get('translator');

        $employee_data = $request->request->get('employee');

        $companies_manager = $this->get("elearning_companies.companies_manager");
        $company = $companies_manager->currentUserCompany();

        if (!isset($employee_data['email'])) {
            $message = $translator->trans("new_employee.no_email_error");
            $response = array(
                'success' => false,
                'message' => $message
            );
            return new JsonResponse($response);
        }

        $password = $employee_data['password'];
        $passwordRepeat = $employee_data['password_repeat'];
        if (!empty($password) && $password != $passwordRepeat) {
            $message = $translator->trans("new_employee.password_not_equal_error");
            $response = array(
                'success' => false,
                'message' => $message
            );
            return new JsonResponse($response);
        }

        $username = $employee_data['username'];
        $userManager = $this->container->get('fos_user.user_manager');
        if (!empty($userManager->findUserByUsername($username))) {
            $message = $translator->trans("new_employee.incorrect_username");
            $response = array(
                'success' => false,
                'message' => $message
            );
            return new JsonResponse($response);
        }

        $em = $this->getDoctrine()->getManager();
        $parent = null;
        if (!empty($employee_data['parent'])) {
            $parent = $em->getRepository('ElearningCompaniesBundle:Administrator')
                ->find($employee_data['parent']);
            $employee_data['type'] = in_array($parent->getLvl(), [0, 1]) ? 'administrator' : 'manager';
        }


        $email = $employee_data['email'];
        $type = $employee_data['type'];
        $active = $employee_data['active'] == "active";

        $user = $this->createUser($username, $email, $type, $active, $password);
        if (!$user) {
            $message = $translator->trans("new_employee.cant_create_user");
            $response = array(
                'success' => false,
                'message' => $message
            );
            return new JsonResponse($response);
        }

        if ($this->isGranted('ROLE_ADMIN_A2') && $employee_data['type'] == 'administrator') {
            //error
        }

        if ($employee_data['type'] == 'administrator' && !$parent) {
            $admin = new Administrator();
            $admin->setUser($user);
            $em->persist($admin);
        }

        if ($parent) {
            $admin = new Administrator();
            switch ($parent->getLvl()) {
                case 0:
                    $user->addRole("ROLE_ADMIN_A1");
                    break;
                case 1:
                    $user->addRole("ROLE_ADMIN_A2");
                    break;
                default:
                    $user->addRole("ROLE_ADMIN_A3");
            }
            $admin->setParent($parent);
            $admin->setUser($user);
            $em->persist($admin);
        }

        $employee = new Employee ();
        $employee->setCompany($company);
        $employee->setUser($user);
        $employee->setType($type);
        $employee->setState('published');
        $em->persist($employee);
        $form = $this->createForm(new EmployeeProfileFieldsType ());
        foreach ($form->all() as $field) {
            $fieldname = $field->getName();
            if (in_array($fieldname, array("active", "type", "email", "username", "parent"))) {
                continue;
            }

            if ($fieldname == 'imagefile') {
                $file = $request->files->get('employee')['imagefile'];
                if ($file instanceof UploadedFile) {
                    $oldField = $employee->getField('image');
                    if ($oldField) {
                        $em->remove($oldField);
                    }
                    $field = new EmployeeProfileField();
                    $field->setEmployee($employee);
                    $field->setFieldname('image');
                    $image = $field->preUpload($file);
                    $field->upload($file, $image);
                    $field->setFieldvalue($image);
                    $em->persist($field);
                }
            }

            if (isset($employee_data[$fieldname])) {
                $field = new EmployeeProfileField();
                $field->setEmployee($employee);
                $field->setFieldname($fieldname);
                if ($fieldname == "birthday") {
                    $parts = $employee_data[$fieldname];
                    foreach ($parts as &$part) {
                        $part = sprintf("%02d", $part);
                    }
                    $value = implode(".", $parts);
                    $field->setFieldvalue($value);
                    $employee_data[$fieldname] = $value;
                } else {
                    $field->setFieldvalue($employee_data[$fieldname]);
                }
                $em->persist($field);
            }
        }
        $em->flush();

        $message = $translator->trans("new_employee.employee_created");

        $employee_data['id'] = $employee->getId();
        $employee_data['username'] = $user->getUsername();
        $response = array(
            'success' => true,
            'message' => $message,
            'employee' => $employee_data,
            'type' => $type
        );
        return new JsonResponse($response);
    }

    /**
     * Prepares name from given name and surname
     * @param $name
     * @param $surname
     * @return mixed|string
     */
    private function prepareUsername($name, $surname)
    {
        $names = explode(" ", $name);
        if (count($names) > 1) {
            $name = $names[0];
        }
        $concatted = $name . "." . $surname;
        $concatted = strtolower($concatted);
        $concatted = str_replace(array('ą', 'č', 'ę', 'ė', 'į', 'š', 'ų', 'ū', 'ž'),
            array('a', 'c', 'e', 'e', 'i', 's', 'u', 'u', 'z'),
            $concatted);

        $userManager = $this->container->get('fos_user.user_manager');
        $suffix = "";
        $found = false;
        $number = 1;
        while (!$found) {
            $user = $userManager->findUserByUsername($concatted . $suffix);
            $found = empty($user);
            if (!$found) {
                $suffix = "." . $number++;
            }
        }
        $concatted = $concatted . $suffix;
        return $concatted;
    }

    /**
     * Creates fos_user entity
     * @param $username
     * @param $email
     * @param $type
     * @param bool $active
     * @param null $password
     * @return \FOS\UserBundle\Model\UserInterface
     */
    private function createUser($username, $email, $type, $active = true, $password = null, $flush = true)
    {
        $em = $this->getDoctrine()->getManager();

        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);
        if (!empty($user)) {
            return false;
        }
        $newUser = $userManager->createUser();
        if (!$newUser) {
            return $newUser;
        }
        $newUser->setUsername($username);
        $newUser->setUsernameCanonical($username);
        if (!empty($password)) {
            $newUser->setPlainPassword($password);
        } else {
            $newUser->setPlainPassword($username);
            $newUser->addRole("ROLE_FORCECREDENTIALCHANGE");
        }
        $newUser->setEnabled($active);

        $encoder = $this->container->get('security.encoder_factory')->getEncoder($newUser);
        $password = $encoder->encodePassword($username, $newUser->getSalt());
        $newUser->setPassword($password);
        $newUser->setEmail($email);

        $appConfig = $this->getParameter('app_config');
        $extendedAS = !empty($appConfig['extended_admins_structure']) && $appConfig['extended_admins_structure'];
        switch ($type) {
            case 'manager':
                $newUser->addRole("ROLE_MANAGER");
                if ($extendedAS) {
                    $newUser->addRole("ROLE_ADMIN_A3");
                }
                break;
            case 'administrator':
                $newUser->addRole("ROLE_SUPERVISOR");
                $newUser->addRole("ROLE_LECTURER");
                break;
            default:
                $newUser->addRole("ROLE_STUDENT");
        }
        
        $newUser->setEnabled(true);

        $dispatcher = $this->get('event_dispatcher');
        $event = new RegistrationEvent($newUser);
        $dispatcher->dispatch(UserEvents::USER_REGISTRATION, $event);

        $em->persist($newUser);
        if ($flush) {
            $em->flush();
        }


        return $newUser;
    }


    /**
     * Adds employee to the group
     * @param Request $request
     * @return JsonResponse
     */
    public function addToGroupAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $employee_id = $request->request->get('id');
        $new = $request->request->get('new');
        $group_id = $request->request->get('group_id');
        $newcount = 0;

        if ($new == "true") {
            $session = $this->getRequest()->getSession();
            $employees = $session->get('ElearningCompaniesBundle.groupEmployees', array());
            $employees[] = $employee_id;
            $session->set("ElearningCompaniesBundle.groupEmployees", $employees);
        } else {
            $em = $this->getDoctrine()->getManager();
            $employee = $em->find("ElearningCompaniesBundle:Employee", $employee_id);
            $group = $em->find("ElearningCompaniesBundle:Group", $group_id);
            $employee->addGroup($group);
            $group->addEmployee($employee);
            $em->persist($employee);
            $em->flush();

            $query = $em->createQuery(
                'SELECT COUNT(e.id)
                FROM ElearningCompaniesBundle:Employee e
                JOIN e.groups g
                WHERE g.id = :group_id
                AND e.type = :type'
            )->setParameter('group_id', $group->getId())
                ->setParameter('type', 'employee');
            $newcount = $query->getSingleScalarResult();

        }

        $response = array('success' => true, 'newcount' => $newcount);
        return new JsonResponse($response);
    }


    /**
     * Removes employee from group
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromGroupAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $employee_id = $request->request->get('id');
        $group_id = $request->request->get('group_id');
        $new = $request->request->get('new');


        if ($new == "true") {
            $session = $this->getRequest()->getSession();
            $employees = $session->get('ElearningCompaniesBundle.groupEmployees', array());
            $index = array_search($employee_id, $employees);
            unset($employees[$index]);
            $session->set("ElearningCompaniesBundle.groupEmployees", $employees);
            $newcount = 0;
        } else {
            $em = $this->getDoctrine()->getManager();
            $employee = $em->find("ElearningCompaniesBundle:Employee", $employee_id);
            $group = $em->find("ElearningCompaniesBundle:Group", $group_id);
            $employee->removeGroup($group);
            $group->removeEmployee($employee);
            $em->persist($employee);
            $em->persist($group);
            $em->flush();
            $newcount = count($group->getEmployees());
        }

        $response = array('success' => true, 'newcount' => $newcount);
        return new JsonResponse($response);
    }


    /**
     * Shows import page
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');
        $params = array();
        return $this->render('ElearningCompaniesBundle:Employees:import.html.twig', $params);
    }

    /**
     * Performs employees import from excel file
     * @param Request $request
     * @return JsonResponse
     * @throws \PHPExcel_Exception
     */
    public function uploadAction(Request $request)
    {
        set_time_limit(600);

        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');
        $response = array();
        $userManager = $this->container->get('fos_user.user_manager');

        $uploaded_file = $request->files->get('file');

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject($uploaded_file->getRealPath());
        $phpExcelObject->setActiveSheetIndex(0);
        $sheet = $phpExcelObject->getActiveSheet();
        $employees = array();
        $fieldValues = array(
            'employed' => array(
                'true' => 'employed',
                'false' => 'unemployed'
            ),
        );

        foreach ($sheet->getRowIterator() as $row) {
            if ($row->getRowIndex() == 1) { /* Header row */
                continue;
            }

            $additionalFields = array();
            /* TODO hardcoded structure, move to config */
            $companycode = $sheet->getCell("A" . $row->getRowIndex())->getValue();
            $name = $sheet->getCell("B" . $row->getRowIndex())->getValue();
            $surname = $sheet->getCell("C" . $row->getRowIndex())->getValue();
            $username = $sheet->getCell("D" . $row->getRowIndex())->getValue();
            $position = $sheet->getCell("E" . $row->getRowIndex())->getValue();
            $address = $sheet->getCell("F" . $row->getRowIndex())->getValue();
            $birthday = $sheet->getCell("G" . $row->getRowIndex())->getValue();

            $additionalFields['active'] = $sheet->getCell("H" . $row->getRowIndex())->getValue();
            $additionalFields['employed'] = $sheet->getCell("I" . $row->getRowIndex())->getValue();
            $additionalFields['ckk_number'] = $sheet->getCell("J" . $row->getRowIndex())->getValue();
            $additionalFields['mpk_number'] = $sheet->getCell("K" . $row->getRowIndex())->getValue();
            $additionalFields['departament'] = $sheet->getCell("L" . $row->getRowIndex())->getValue();
            $additionalFields['region'] = $sheet->getCell("M" . $row->getRowIndex())->getValue();

            if (empty($username)) {
                break;
            }
            $employee = array(
                'code' => $companycode,
                'name' => $name,
                'username' => $username,
                'surname' => $surname,
                'position' => $position,
                'address' => $address,
                'birthday' => $birthday,
            );

            foreach ($additionalFields as $field => $value) {
                if (is_null($value)) {
                    continue;
                }

                if (array_key_exists($field, $fieldValues)) {
                    $employee[$field] = $value ? $fieldValues[$field]['true'] : $fieldValues[$field]['false'];
                } else {
                    $employee[$field] = $value;
                }
            }

            $employees[] = $employee;

        }

        $companies_manager = $this->get("elearning_companies.companies_manager");
        $company = $companies_manager->currentUserCompany();
        $dummyemail = "no@email.loc"; /* TODO: move to config */
        $em = $this->getDoctrine()->getManager();
        $success = true;
        $message = "";
        $count = 0;
        try {
            foreach ($employees as $employeedata) {
                $employee = null;
                $username = $employeedata['username'];
                /** @var User $user */
                if ($user = $userManager->findUserByUsername($username)) {
                    //Try to find user
                    $employee = $user->getEmployee();
                } elseif ($user = $this->createUser($username, $dummyemail, 'employee', true, null, false)) {
                    //Try to create user
                    $employee = new Employee();
                    $employee->setCompany($company);
                    $employee->setType('employee');
                    $employee->setUser($user);
                    $employee->setState('published');
                    $em->persist($employee);
                }

                if (array_key_exists('active', $employeedata)) {
                    $user->setEnabled((bool) $employeedata['active']);
                    $em->persist($user);
                }

                if (!$employee) {
                    continue;
                }

                $count++;

                foreach ($employee->getFields() as $oldField) {
                    $em->remove($oldField);
                }

                $form = $this->createForm(new EmployeeProfileFieldsType());
                foreach ($form->all() as $field) {
                    $fieldname = $field->getName();
                    if (in_array($fieldname, array("type", "email", "username", "active"))) {
                        continue;
                    }
                    if (isset($employeedata[$fieldname])) {
                        $field = new EmployeeProfileField();
                        $field->setEmployee($employee);
                        $field->setFieldname($fieldname);
                        $field->setFieldvalue($employeedata[$fieldname]);
                        $em->persist($field);
                    }
                }
            }
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }

        if ($success) {
            $em->flush();
        }

        $response['count'] = $count;
        $response['success'] = $success;
        $response['message'] = $message;

        return new JsonResponse($response);
    }

    /**
     * Lists all employees
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');
        $params = array();
        $em = $this->getDoctrine()->getManager();

        $search = $request->request->get('search');
        $type = $request->request->get('listtype');
        $employees_page = $request->query->getInt('employees-page', 1);
        $managers_page = $request->query->getInt('managers-page', 1);
        $administrators_page = $request->query->getInt('administrators-page', 1);

        $key = "_filter_employee";
        if (!is_null($search)) {
            if ($search) {
                $this->get('session')->set($key, array('keyword' => $search, 'type' => $type));
            } else {
                $this->get('session')->remove($key);
            }
            $employees_page = 1;
            $managers_page = 1;
        }

        $filter = $this->get('session')->has($key) ? $this->get('session')->get($key) : null;

        $companies_manager = $this->get("elearning_companies.companies_manager");
        $company = $companies_manager->currentUserCompany();

        $paginator = $this->get('knp_paginator');

        $qb = $em->createQueryBuilder();
        $basequery = $qb->select(array('e', 'f', 'u'))
            ->from('ElearningCompaniesBundle:Employee', 'e')
            ->join('e.fields', 'f')
            ->leftJoin('e.user', 'u')
            ->where($qb->expr()->eq('e.company_id', ':company_id'))
            ->andWhere($qb->expr()->eq('e.type', ':type'))
            ->andWhere("e.state IN ('','published')")
            ->setParameter('company_id', $company->getId());

        $minLevel = 0;
        $rootLft = null;
        $rootRgt = null;

        if ($this->isGranted('ROLE_ADMIN_A1') || $this->isGranted('ROLE_ADMIN_A2')) {
            $user = $this->getUser();
            $admin = $em->getRepository('ElearningCompaniesBundle:Administrator')
                ->findOneBy(array('user_id' => $user->getId()));
            $minLevel = $admin->getLvl();
            $rootLft = $admin->getLft();
            $rootRgt = $admin->getRgt();
            $basequery
                ->leftJoin('ElearningCompaniesBundle:Administrator', 'a', 'WITH', 'a.user_id = u.id')
                ->andWhere('(a.lft IS NULL OR a.lft >= :lft)')
                ->andWhere('(a.rgt IS NULL OR a.rgt <= :rgt)')
                ->setParameter('lft', $rootLft)
                ->setParameter('rgt', $rootRgt);
        }

        $query = clone $basequery;

        if (!empty($filter) && $filter['type'] == "employees") {
            $this->applyEmployeeFilter($query, $filter['keyword']);
        }

        $employees = $query->setParameter('type', 'employee')->getQuery();
        $employees = $paginator->paginate(
            $employees,
            $employees_page /*page number*/,
            10/*limit per page*/,
            array(
                'pageParameterName' => 'employees-page'
            )
        );
        $params['employees'] = $employees;

        $query = clone $basequery;
        if (!empty($filter) && $filter['type'] == "managers") {
            $this->applyEmployeeFilter($query, $filter['keyword']);
        }

        $managers = $query->setParameter('type', 'manager')->getQuery();
        $managers = $paginator->paginate(
            $managers,
            $managers_page /*page number*/,
            10/*limit per page*/,
            array(
                'pageParameterName' => 'managers-page'
            )
        );
        
        $params['managers'] = $managers;

        $query = clone $basequery;
        if (!empty($filter) && $filter['type'] == "administrators") {
            $this->applyEmployeeFilter($query, $filter['keyword']);
        }

        $params['administrators'] = array();
        if ($this->isGranted('ROLE_SUPERVISOR')) {
            $administrators = $query->setParameter('type', 'administrator')->getQuery();
            $administrators = $paginator->paginate(
                $administrators,
                $administrators_page /*page number*/,
                10/*limit per page*/,
                array(
                    'pageParameterName' => 'administrators-page'
                )
            );

            $params['administrators'] = $administrators;
        }
		
		$appConfig = $this->getParameter('app_config');		
		$showParent = !empty($appConfig['extended_admins_structure']) && $appConfig['extended_admins_structure'];
		$params['showTree'] = $showParent;
        
        $form_options = array(
            'isSupervisor' => $this->isGranted('ROLE_SUPERVISOR'),
            'canCreateAdmin' => !$this->isGranted('ROLE_ADMIN_A2'),
            'minLevel' => $minLevel,
            'rootLft' => $rootLft,
            'rootRgt' => $rootRgt,
			'showParent' => $showParent
        );
        $form = $this->createForm(new EmployeeProfileFieldsType(), null, $form_options);
        $message_form = $this->createForm(new MessageType());
        //$form->remove('type');
        $params['employee_fields_form'] = $form->createView();
        $params['message_form'] = $message_form->createView();

        $form = $this->createForm(new EmployeeProfileFieldsType(), null, $form_options);
        $params['new_employee_fields_form'] = $form->createView();

        $params['search'] = $filter;

        $qb = $em
            ->createQueryBuilder()
            ->select('a')
            ->from('ElearningCompaniesBundle:Administrator', 'a')
            ->join('a.user', 'u')
            ->where('u.enabled = 1')
            ->orderBy('a.root, a.lft', 'ASC');
        
        $params['adminsTree'] = $qb->getQuery()->getResult(); 

        return $this->render('ElearningCompaniesBundle:Employees:list.html.twig', $params);
    }

    private function applyEmployeeFilter(QueryBuilder $qb, $keyword)
    {
        $filterSubquery = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('epf.employee_id')
            ->from('ElearningCompaniesBundle:EmployeeProfileField', 'epf')
            ->where('epf.fieldvalue LIKE :keyword');

        $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('u.username', ':keyword'),
                    $qb->expr()->in('e.id', $filterSubquery->getDql())
                )
            )
            ->setParameter('keyword', "%$keyword%");
    }

    /**
     * Gets single employee info
     * @param Request $request
     * @param $employee_id
     * @return JsonResponse
     */
    public function getAction(Request $request, $employee_id)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $params = array();
        $em = $this->getDoctrine()->getManager();

        $employee = $em->find("ElearningCompaniesBundle:Employee", $employee_id);
        if (empty($employee)) {
            $params['success'] = false;
            return new JsonResponse($params);
        }

        $employee_data = array();

        $form = $this->createForm(new EmployeeProfileFieldsType());
        foreach ($form->all() as $field) {
            $fieldname = $field->getName();
            $value = null;
            if (in_array($fieldname, array('type', 'email'))) {
                if ($fieldname == "type") {
                    $value = $employee->getType();
                } else if ($fieldname == "email") {
                    $value = $employee->getUser()->getEmail();
                }
            } else {
                $value = $employee->getFieldValue($fieldname);
            }
            $employee_data[$fieldname] = $value;
        }
        $employee_data['username'] = $employee->getUser()->getUsername();
        $employee_data['active'] = $employee->getUser()->isEnabled() ? "active" : "inactive";

        $image = $employee->getField('image');
        $employee_data['image'] = '';
        if ($image) {
            $employee_data['image'] = $image->getWebPath($image->getFieldvalue());
        }

        $groups = $employee->getGroups();
        $groupTitles = array();
        $groupIds = array();
        foreach ($groups as $group) {
            if ($group->getState() == 'published') {
                $groupTitles[] = $group->getTitle();
                $groupIds[] = $group->getId();
            }
        }

        $qb = $em->createQueryBuilder();
        $qb
            ->select('DISTINCT c.name')
            ->from("ElearningCoursesBundle:GroupCourse", 'gc')
            ->join("ElearningCoursesBundle:Course", 'c', 'WITH', 'gc.course_id = c.id')
            ->where('gc.group IN (:groups)')
            ->andWhere('gc.active = 1')
            ->andWhere("c.status = :state")
            ->setParameter('state', 'published')
            ->setParameter('groups', $groupIds);
        $courses = $qb->getQuery()->getResult();
        $courseTitles = array();
        foreach ($courses as $course) {
            $courseTitles[] = $course['name'];
        }

        $employee_data['groups'] = $groupTitles;
        $employee_data['courses'] = $courseTitles;

        $admin = $em->getRepository('ElearningCompaniesBundle:Administrator')
            ->findOneBy(array('user_id' => $employee->getUserId()));
        if ($admin && $admin->getParent()) {
            $employee_data['parent'] = $admin->getParent()->getId();
        }

        $params['success'] = true;
        $params['employee'] = $employee_data;
        return new JsonResponse($params);
    }


    /**
     * Updates employees info
     * @param Request $request
     * @param $employee_id
     * @return JsonResponse
     */
    public function updateAction(Request $request, $employee_id)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }
        $params = array();
        $em = $this->getDoctrine()->getManager();
        $employee = $em->find('ElearningCompaniesBundle:Employee', $employee_id);
        $companies_manager = $this->get("elearning_companies.companies_manager");
        $company = $companies_manager->currentUserCompany();

        if (empty($employee) || $company->getId() != $employee->getCompany()->getId()) {
            $params['success'] = false;
            return new JsonResponse($params);
        }

        $data = $request->request->get('employee');
        $user = $employee->getUser();
        $user->setEmail($data['email']);

        $userManager = $this->container->get('fos_user.user_manager');

        if (isset($data['username']) && $data['username'] != $user->getUsername()) {
            if (!$this->isGranted('ROLE_SUPERVISOR') || !empty($userManager->findUserByUsername($data['username']))) {
                $params['success'] = false;
                return new JsonResponse($params);
            }
            $user->setUsername($data['username']);
            $user->setUsernameCanonical($data['username']);
        }

        if (isset($data['password']) && !empty($data['password'])) {
            if ($data['password'] != $data['password_repeat']) {
                $params['success'] = false;
                return new JsonResponse($params);
            }
            $user->setPlainPassword($data['password']);
            $this->notifyNewPassword($user, $data['password']);
        }
        $newrole = "";
        $rolechanged = false;

        $admin = $em->getRepository('ElearningCompaniesBundle:Administrator')
            ->findOneBy(array('user_id' => $user->getId()));

        if ($data['type'] == 'administrator' && empty($data['parent'])) {
            if ($admin) {
                $admin->setParent(null);
            } else {
                $admin = new Administrator();
                $admin->setUser($user);
            }
            $em->persist($admin);
        }

        if (!empty($data['parent'])) {
            if (!$admin) {
                $admin = new Administrator();
            }
            $parent = $em->getReference('ElearningCompaniesBundle:Administrator', $data['parent']);
            switch ($parent->getLvl()) {
                case 0:
                    $user->addRole("ROLE_ADMIN_A1");
                    $user->removeRole("ROLE_ADMIN_A2");
                    $user->removeRole("ROLE_ADMIN_A3");
                    $data['type'] = 'administrator';
                    break;
                case 1:
                    $user->addRole("ROLE_ADMIN_A2");
                    $user->removeRole("ROLE_ADMIN_A1");
                    $user->removeRole("ROLE_ADMIN_A3");
                    $data['type'] = 'administrator';
                    break;
                default:
                    $user->addRole("ROLE_ADMIN_A3");
                    $user->removeRole("ROLE_ADMIN_A1");
                    $user->removeRole("ROLE_ADMIN_A2");
                    $data['type'] = 'manager';
            }

            $admin->setParent($parent);
            $admin->setUser($user);
            $em->persist($admin);
        }

        if (isset($data['type']) && $data['type'] != $employee->getType()) {
            $employee->setType($data['type']);
            if ($data['type'] == "manager") {
                $user->addRole("ROLE_MANAGER");
                $user->removeRole("ROLE_STUDENT");
                $user->removeRole("ROLE_LECTURER");
                $user->removeRole("ROLE_SUPERVISOR");
            }
            else if ($data['type'] == "employee") {
                $user->addRole("ROLE_STUDENT");
                $user->removeRole("ROLE_MANAGER");
                $user->removeRole("ROLE_LECTURER");
                $user->removeRole("ROLE_SUPERVISOR");
            } else if ($data['type'] == "administrator") {
                $user->removeRole("ROLE_STUDENT");
                $user->removeRole("ROLE_MANAGER");
                $user->addRole("ROLE_LECTURER");
                $user->addRole("ROLE_SUPERVISOR");
            }
            $newrole = $data['type'];
            $rolechanged = true;
        }

        $userManager->updateUser($user);

        foreach ($employee->getFields() as $field) {
            if ($field->getFieldname() != 'image') {
                $em->remove($field);
            }
        }

        $employee->getUser()->setEnabled($data['active'] == "active");

        $form = $this->createForm(new EmployeeProfileFieldsType());
        foreach ($form->all() as $field) {
            $fieldname = $field->getName();
            if (in_array($fieldname, array("active", "type", "email", "username", "parent"))) {
                continue;
            }

            if ($fieldname == "imagefile") {
                $file = $request->files->get('employee')['imagefile'];
                if ($file instanceof UploadedFile) {
                    $oldField = $employee->getField('image');
                    if ($oldField) {
                        $em->remove($oldField);
                    }
                    $field = new EmployeeProfileField();
                    $field->setEmployee($employee);
                    $field->setFieldname('image');
                    $image = $field->preUpload($file);
                    $field->upload($file, $image);
                    $field->setFieldvalue($image);
                    $em->persist($field);
                }
            }

            if (isset($data[$fieldname])) {
                $field = new EmployeeProfileField();
                $field->setEmployee($employee);
                $field->setFieldname($fieldname);
                if ($fieldname == "birthday") {
                    $parts = $data[$fieldname];
                    foreach ($parts as &$part) {
                        $part = sprintf("%02d", $part);
                    }
                    $value = implode(".", $parts);
                    $field->setFieldvalue($value);
                    $data[$fieldname] = $value;
                } else {
                    $field->setFieldvalue($data[$fieldname]);
                }
                $em->persist($field);
            }
        }

        $em->flush();
        $params['success'] = true;
        $params['employee'] = $data;
        $params['employee_id'] = $employee_id;
        $params['rolechanged'] = $rolechanged;
        $params['newrole'] = $newrole;

        return new JsonResponse($params);
    }

    /**
     * Sends email notifying about changed user's password
     * @param $user
     * @param $password
     * @return int
     * @throws \Twig_Error
     */
    private function notifyNewPassword($user, $password)
    {
        if (strpos($user->getEmail(), "email.loc") !== FALSE) {
            return false;
        }
        $siteurl = $this->getParameter('siteurl');
        /* Sending email for user notifying about password change */
        $template = 'ElearningCompaniesBundle:Employees:new_password_email.txt.twig';
        $rendered = $this->get('templating')->render($template, array(
            'username' => $user->getUsername(),
            'password' => $password,
            'siteurl' => $siteurl
        ));

        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($rendered));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array($this->getParameter('mailer_from_email')['address'] => $this->getParameter('mailer_from_email')['sender_name']))
            ->setTo($user->getEmail())
            ->setBody($body);
        $result = $this->get('mailer')->send($message);
        return $result;
    }

    /**
     * Deletes employee (marks as deleted)
     * @param Request $request
     * @param $employee_id
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $employee_id)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $params = array();
        $em = $this->getDoctrine()->getManager();
        $employee = $em->find('ElearningCompaniesBundle:Employee', $employee_id);
        $companies_manager = $this->get("elearning_companies.companies_manager");
        $company = $companies_manager->currentUserCompany();

        if (empty($employee) || $company->getId() != $employee->getCompany()->getId()) {
            $params['success'] = false;
            return new JsonResponse($params);
        }
        $params['success'] = true;

        //immediately change the login name, just in case
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $employee->getUser();
        $newUsername = uniqid('DELETED-', true);
        $user->setUsername($newUsername);
        $user->setUsernameCanonical($newUsername);
        $user->setEnabled(false);
        $userManager->updateUser($user, false);
        $employee->setState('deleted');
        $em->flush();

        $em->remove($employee);
        try {
            $em->flush();
        } catch (\Exception $e) {

        }

        return new JsonResponse($params);
    }

    /**
     * Changes employees user's password
     * @param Request $request
     * @return JsonResponse
     */
    public function changePasswordAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $translator = $this->get('translator');
        $employee_id = $request->request->get('employee_id');
        $password = $request->request->get('password');
        $password_repeat = $request->request->get('password_repeat');
        $inform_user = $request->request->get('inform_user');

        $em = $this->getDoctrine()->getManager();
        $employee = $em->find("ElearningCompaniesBundle:Employee", $employee_id);
        if (empty($employee)) {
            $params['success'] = false;
            $params['message'] = $translator->trans("employees.password.employee_not_found");
            return new JsonResponse($params);
        }

        $params = array();

        if ($password != $password_repeat) {
            $params['success'] = false;
            $params['message'] = $translator->trans("employees.password.not_equal_passwords");
            return new JsonResponse($params);
        }

        if (empty($password) || strlen($password) < 4) {
            $params['success'] = false;
            $params['message'] = $translator->trans("employees.password.password_min_length");
            return new JsonResponse($params);
        }

        $userManager = $this->container->get('fos_user.user_manager');
        $user = $employee->getUser();
        $user->setPlainPassword($password);
        $userManager->updateUser($user, true);


        $sendresult = true;
        if ($inform_user) {
            $sendresult = $this->notifyNewPassword($user, $password);
        }


        $params['success'] = true;
        $params['message'] = $translator->trans('employees.password.success');
        $params['sendresult'] = $sendresult;
        return new JsonResponse($params);
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
        $employeeId = $request->request->get('employee_id');

        $messageForm->handleRequest($request);
        if ($messageForm->isValid()) {
            $employee = $em->find('ElearningCompaniesBundle:Employee', $employeeId);
            if ($employee) {
                $message = $messageForm->getData();
                $messageService = $this->get('elearning.message_service');
                $messageService->sendEmail($employee, $message);
                $success = true;
            }
        }

        $params['success'] = $success;
        if ($success) {
            $params['message'] = $translator->trans('employees.send_email.success');
        }
        return new JsonResponse($params);
    }
}
