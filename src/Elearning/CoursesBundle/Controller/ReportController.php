<?php

namespace Elearning\CoursesBundle\Controller;

use Elearning\CompaniesBundle\Form\EmployeeProfileFieldsType;
use Elearning\CoursesBundle\Entity\EmployeeSubject;
use Elearning\CoursesBundle\Entity\ManualCourseEntry;
use Elearning\CoursesBundle\Entity\Subject;
use Elearning\CoursesBundle\Entity\SubjectStatus;
use Elearning\CoursesBundle\Form\SubjectType;
use Elearning\CoursesBundle\Form\SubjectStatusType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Doctrine\ORM\Query;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ReportController extends Controller
{
    public function studentListeningAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');
        $params = array();

        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $listenings = $em->getRepository("ElearningCoursesBundle:CourseListening")
            ->findBy(array('completed' => 1, 'user_id' => $user->getId()));


        $categories_hierarchy = array();
        $totalcoursehours = 0;
        foreach ($listenings as $listening) {
            $course = $listening->getCourse();
            if (!isset($categories_hierarchy[$course->getId()])) {
                $categoriesHierarchy[$course->getId()] = $this->getCategoriesHierarchy($course->getId());
            }
            if ($listening->isCoursePassed()) {
                $totalcoursehours += $course->getDuration();
            }
        }
        $params['totalcoursehours'] = $totalcoursehours;
        $params['categoriesHierarchy'] = $categories_hierarchy;


        $paginator = $this->get('knp_paginator');
        $listenings = $paginator->paginate(
            $listenings,
            $request->query->getInt('page', 1)/*page number*/ ,
            10/*limit per page*/
        );

        $params['listenings'] = $listenings;
        $employee = $user->getEmployee();

        if (!empty($employee)) {
            $query = $em->createQueryBuilder()
                ->select("mch.hours AS hours")
                ->from('ElearningCompaniesBundle:Employee', 'e', 'e.id')
                ->join('e.manualCourseHours', 'mch')
                ->leftJoin('e.manualCourseHours', 'mch2', 'WITH', 'mch2.id > mch.id')
                ->where('e.id = :employee_id')
                ->andWhere('mch2.id IS NULL')
                ->groupBy('e')
                ->setParameter('employee_id', $user->getEmployee()->getId())
                ->getQuery();
            try {
                $manualcoursehours = $query->getSingleScalarResult();
            } catch (ORMException $e) {
                $manualcoursehours = 0;
            }
        } else {
            $manualcoursehours = 0;
        }


        $params['manualhours'] = $manualcoursehours;


        return $this->render(sprintf("ElearningCoursesBundle:Reports:student_listening.html.twig"), $params);
    }


    public function supervisorCoursesAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPERVISOR') && !$this->isGranted('ROLE_ADMIN_A3')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $view = $request->query->get('view') ? $request->query->get('view') : 'html';
        $version = $this->getVersion();

        $params = array();

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $notRootAdmin = $this->isGranted('ROLE_ADMIN_A2') || $this->isGranted('ROLE_ADMIN_A3');

        $groupIds = [];
        if ($notRootAdmin) {
            $companies_manager = $this->get("elearning_companies.companies_manager");
            $company = $companies_manager->currentUserCompany();
            $adminService = $this->get('elearning.admin_service');
            $groupIds = $adminService->getGroups($user, $company, true);
        }

        $coursesQb = $em->createQueryBuilder()
            ->select('c')
            ->from('ElearningCoursesBundle:Course', 'c')
            ->where('c.status = :status')
            ->setParameter('status', 'published');

        if ($notRootAdmin) {
            $coursesQb
                ->join('c.groupCourses', 'gc')
                ->andWhere('gc.group IN (:groups)')
                ->andWhere('gc.active = 1')
                ->setParameter('groups', $groupIds);
        }

        $courses = $coursesQb->getQuery()->getResult();

        $avgresults = [];
        if ($version == 'extended' && count($courses)) {
            $examResultsQb = $em->createQueryBuilder()
                ->select(['l.course_id', 'AVG(ea.result) as avg_result'])
                ->from('ElearningCoursesBundle:CourseListening', 'l', 'l.course_id')
                ->join('l.examAttempts', 'ea')
                ->where('l.course IN (:courses)')
                ->andWhere('ea.result IS NOT NULL')
                ->groupBy('l.course_id')
                ->setParameter(':courses', $courses);

            $avgresults = $examResultsQb->getQuery()->getResult();
        }
        $params['avgresults'] = $avgresults;

        $categories_hierarchy = array();
        foreach ($courses as $course) {
            if (!isset($categories_hierarchy[$course->getId()])) {
                $categoriesHierarchy[$course->getId()] = $this->getCategoriesHierarchy($course->getId());
            }
        }
        $params['categoriesHierarchy'] = $categories_hierarchy;


        $assignedcounts = array();
        $startedcounts = array();
        $completedcounts = array();
        $studentquestionscounts = array();

        $qb = $em->createQueryBuilder()
            ->select('l')
            ->from('ElearningCoursesBundle:CourseListening', 'l')
            ->where('l.course_id = :course_id');

        if ($groupIds) {
            $qb
                ->join('l.groupCourse', 'gc')
                ->andWhere('gc.group_id IN (:group_ids)')
                ->andWhere('gc.active = 1')
                ->setParameter('group_ids', $groupIds);
        }

        foreach ($courses as $course) {
            $qb
                ->setParameter('course_id', $course->getId());
            $listenings = $qb->getQuery()->getResult();
            $assignedcounts[$course->getId()] = count($listenings);
            $startedcount = 0;
            $completedcount = 0;
            $studentquestionscount = 0;
            foreach ($listenings as $listening) {
                $started = $listening->getStarted();
                $completed = $listening->getCompleted();
                if (!empty($started)) {
                    $startedcount++;
                }
                if ($completed) {
                    $completedcount++;
                }
                $studentquestionscount += count($listening->getQuestions());
            }
            $startedcounts[$course->getId()] = $startedcount;
            $completedcounts[$course->getId()] = $completedcount;
            $studentquestionscounts[$course->getId()] = $studentquestionscount;
        }

        $params['assignedcounts'] = $assignedcounts;
        $params['startedcounts'] = $startedcounts;
        $params['completedcounts'] = $completedcounts;
        $params['studentquestionscounts'] = $studentquestionscounts;

        $params['view'] = $view;

        $template = $this->getReportTemplate('supervisor_courses.html.twig', $version);

        if ($view == 'xlsx') {
            $params['courses'] = $courses;
            $html = $this->renderView($template, $params);
            return $this->returnExcel($this->createExcelFromHtml($html), 'courses.xlsx');
        }

        $paginator = $this->get('knp_paginator');
        $courses = $paginator->paginate(
            $courses,
            $request->query->getInt('page', 1),
            10
        );


        $params['courses'] = $courses;

        return $this->render($template, $params);
    }


    private function getCategoriesHierarchy($course_id)
    {
        $em = $this->getDoctrine()->getManager();
        $course = $em->find("ElearningCoursesBundle:Course", $course_id);
        $categories = array();
        $category = $course->getCategory()->getParent();
        while (!empty($category)) {
            array_unshift($categories, $category->getName());
            $category = $category->getParent();
        }

        return $categories;
    }


    public function supervisorCourseAction(Request $request, $course_id)
    {
        if (!$this->isGranted('ROLE_SUPERVISOR') && !$this->isGranted('ROLE_ADMIN_A3')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $view = $request->query->get('view') ? $request->query->get('view') : 'html';
        $version = $this->getVersion();
        $params = array();

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $notRootAdmin = $this->isGranted('ROLE_ADMIN_A2') || $this->isGranted('ROLE_ADMIN_A3');

        $groupIds = [];
        if ($notRootAdmin) {
            $companies_manager = $this->get("elearning_companies.companies_manager");
            $company = $companies_manager->currentUserCompany();
            $adminService = $this->get('elearning.admin_service');
            $groupIds = $adminService->getGroups($user, $company, true);
        }

        $course = $em->find("ElearningCoursesBundle:Course", $course_id);

        $params['course'] = $course;

        $qb = $em->createQueryBuilder()
            ->select('l, cert')
            ->from('ElearningCoursesBundle:CourseListening', 'l')
            ->leftJoin('l.certificate', 'cert')
            ->where('l.course_id = :course_id')
            ->setParameter('course_id', $course_id);

        if ($groupIds) {
            $qb
                ->join('l.groupCourse', 'gc')
                ->andWhere('gc.group_id IN (:group_ids)')
                ->andWhere('gc.active = 1')
                ->setParameter('group_ids', $groupIds);
        }

        $listenings = $qb->getQuery()->getResult();

        //exams attempts
        $params['courseExams'] = $em->getRepository('ElearningCoursesBundle:Exam')
            ->getCourseExams($course->getId(), true);

        if ($listenings) {
            $examsInfo = $em->getRepository('ElearningCoursesBundle:Exam')
                ->getExamsAttemptsCntByListenings($listenings);

            $params['examsInfo'] = $examsInfo;

            // accumulate old and new attempts
            $examsOldVersions =
                $em->getRepository('ElearningCoursesBundle:Exam')->getActualExamsWithOldVersionsByCourse($course, true);

            $actualExamsInfo = array();

            foreach ($examsInfo as $listeningId => $attempts) {
                foreach ($attempts as $examId => $info) {
                    $actualExamId = $examsOldVersions[$examId];

                    if (empty($actualExamsInfo[$listeningId][$actualExamId])) {
                        $actualExamsInfo[$listeningId][$actualExamId] = array('cnt' => 0, 'completed_cnt' => 0);
                    }

                    $actualExamsInfo[$listeningId][$actualExamId]['cnt'] += $info['cnt'];
                    $actualExamsInfo[$listeningId][$actualExamId]['completed_cnt'] +=
                        $info['completed_cnt'];
                }
            }

            $params['examsInfo'] = $actualExamsInfo;

        }

        if ($version == 'extended' && count($listenings)) {
            $exams = array();
            $lastExams = $em->createQueryBuilder()
                ->select('MAX(ea.id)')
                ->from('ElearningCoursesBundle:ExamAttempt', 'ea')
                ->where('ea.courseListen IN (:listenings)')
                ->andWhere('ea.result IS NOT NULL')
                ->groupBy('ea.listening_id')
                ->setParameter('listenings', $listenings)
                ->getQuery()
                ->getResult();

            $questionsCorrectAnswersCnt = $em->createQueryBuilder()
                ->select(array('a.question_id', 'SUM(CASE WHEN a.correct = 1 THEN 1 ELSE 0 END) as correct'))
                ->from('ElearningCoursesBundle:ExamAnswer', 'a', 'a.question_id')
                ->groupBy('a.question_id')
                ->getQuery()
                ->getResult();

            $examResults = array();
            if (count($lastExams)) {
                $examResults = $em->createQueryBuilder()
                    ->select(
                        array(
                            'ea.listening_id',
                            'eans.question_id',
                            'AVG(ea.result) as result',
                            'SUM(CASE WHEN ans.correct = 1 THEN 1 ELSE 0 END) as correct',
                            'SUM(CASE WHEN COALESCE(ans.correct, 0) = 0 THEN 1 ELSE 0 END) as incorrect',
                            'ea.starttime',
                            'ea.endtime'
                        )
                    )
                    ->from('ElearningCoursesBundle:ExamAttempt', 'ea')
                    ->join('ea.answers', 'eans')
                    ->leftJoin('eans.answer', 'ans')
                    ->where('ea.id IN (:last_exams)')
                    ->groupBy('ea.listening_id, eans.question_id')
                    ->setParameter('last_exams', $lastExams)
                    ->getQuery()
                    ->getResult();
            }

            $curListeningId = null;
            foreach ($examResults as $examResult) {
                if ($curListeningId != $examResult['listening_id']) {
                    $curListeningId = $examResult['listening_id'];
                    $exams[$curListeningId]['result'] = $examResult['result'];
                    $exams[$curListeningId]['starttime'] = $examResult['starttime'];
                    $exams[$curListeningId]['endtime'] = $examResult['endtime'];
                    $exams[$curListeningId]['correct'] = 0;
                    $exams[$curListeningId]['incorrect'] = 0;
                }

                if (
                    $examResult['incorrect'] == 0 &&
                    $examResult['correct'] == $questionsCorrectAnswersCnt[$examResult['question_id']]['correct']
                ) {
                    $exams[$curListeningId]['correct']++;
                } else {
                    $exams[$curListeningId]['incorrect']++;
                }
            }
            $params['exams'] = $exams;

            $employees = $em->createQueryBuilder()
                ->select('e, g')
                ->from('ElearningCompaniesBundle:Employee', 'e')
                ->join('e.groups', 'g')
                ->join('e.user', 'u')
                ->join('u.courseListenings', 'l')
                ->where('l IN (:listenings)')
                ->setParameter('listenings', $listenings)
                ->getQuery()->getResult();
            $groupsAdmins = $this->getGroupsAdmins();
            $employeeAdmins = array();
            foreach ($employees as $employee) {
                foreach ($employee->getGroups() as $group) {
                    if (isset($groupsAdmins[$group->getId()])) {
                        foreach ($groupsAdmins[$group->getId()] as $groupAdmin) {
                            $employeeAdmins[$employee->getId()][$groupAdmin['eid']] =
                                $groupAdmin;
                        }
                    }
                }
            }
            $params['employeeAdmins'] = $employeeAdmins;
        }

        $template = $this->getReportTemplate("supervisor_courses_course.html.twig", $version);
        $params['view'] = $view;

        if ($view == 'xlsx') {
            $params['listenings'] = $listenings;
            $html = $this->renderView($template, $params);
            return $this->returnExcel($this->createExcelFromHtml($html), 'course.xlsx');
        }

        $paginator = $this->get('knp_paginator');
        $listenings = $paginator->paginate(
            $listenings,
            $request->query->getInt('page', 1)/*page number*/ ,
            10/*limit per page*/
        );
        $params['listenings'] = $listenings;

        return $this->render($template, $params);
    }


    public function supervisorListeningGroupsAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPERVISOR') && !$this->isGranted('ROLE_ADMIN_A3')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $view = $request->query->get('view') ? $request->query->get('view') : 'html';
        $version = $this->getVersion();
        $params = array();

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $notRootAdmin = $this->isGranted('ROLE_ADMIN_A2') || $this->isGranted('ROLE_ADMIN_A3');

        if ($notRootAdmin) {
            $companies_manager = $this->get("elearning_companies.companies_manager");
            $company = $companies_manager->currentUserCompany();
            $adminService = $this->get('elearning.admin_service');
            $groups = $adminService->getGroups($user, $company, false);
        } else {
            $groups = $em->getRepository('ElearningCompaniesBundle:Group')->findBy(
                array('state' => 'published')
            );
        }

        $paginator = $this->get('knp_paginator');

        $query = $em->createQuery(
            'SELECT g.id, COUNT(e.id) AS num
            FROM ElearningCompaniesBundle:Group g
            INDEX BY g.id
            JOIN g.employees e
            WHERE e.type = :type 
            AND g.state = :state
            GROUP BY g'
        );

        $employeescounts = $query
            ->setParameters(
                array(
                    'type' => 'employee',
                    'state' => 'published'
                )
            )
            ->getResult(Query::HYDRATE_ARRAY);
        $params['employeescounts'] = $employeescounts;

        $managerscounts = $query
            ->setParameter('type', 'manager')
            ->getResult(Query::HYDRATE_ARRAY);
        $params['managerscounts'] = $managerscounts;
        $params['view'] = $view;

        if ($version == 'extended' && count($groups)) {
            $coursesCnt = $em->createQueryBuilder()
                ->select(array('gc.group_id', 'COUNT(DISTINCT c.id) as cnt'))
                ->from("ElearningCoursesBundle:GroupCourse", 'gc', 'gc.group_id')
                ->join("ElearningCoursesBundle:Course", 'c', 'WITH', 'gc.course_id = c.id')
                ->where('gc.group IN (:groups)')
                ->andWhere('gc.active = 1')
                ->andWhere("c.status = :state")
                ->groupBy('gc.group_id')
                ->setParameter('state', 'published')
                ->setParameter('groups', $groups)
                ->getQuery()->getResult();

            $params['courses'] = $coursesCnt;

            $avgResults = $em->createQueryBuilder()
                ->select(array('g.id', 'AVG(a.result) as avg_result'))
                ->from('ElearningCompaniesBundle:Group', 'g', 'g.id')
                ->join('g.employees', 'e')
                ->join('e.user', 'u')
                ->join('u.courseListenings', 'l')
                ->join('l.examAttempts', 'a')
                ->where('a.result IS NOT NULL')
                ->andWhere('g.id IN (:groups)')
                ->groupBy('g.id')
                ->setParameter('groups', $groups)
                ->getQuery()->getResult();

            $params['avgResults'] = $avgResults;

            $attendance = $em->createQueryBuilder()
                ->select(array('g.id', 'COUNT(l.id) as total', 'SUM(l.completed) as completed'))
                ->from('ElearningCompaniesBundle:Group', 'g', 'g.id')
                ->join('g.employees', 'e')
                ->join('e.user', 'u')
                ->join('u.courseListenings', 'l')
                ->where('g.id IN (:groups)')
                ->groupBy('g.id')
                ->setParameter('groups', $groups)
                ->getQuery()->getResult();

            $params['attendance'] = $attendance;
        }

        $template = $this->getReportTemplate("supervisor_listening.html.twig", $version);

        if ($view == 'xlsx') {
            $params['groups'] = $groups;
            $html = $this->renderView($template, $params);
            return $this->returnExcel($this->createExcelFromHtml($html), 'listenings.xlsx');
        }

        $groups = $paginator->paginate(
            $groups,
            $request->query->getInt('page', 1)/*page number*/ ,
            10/*limit per page*/
        );
        $params['groups'] = $groups;

        return $this->render($template, $params);
    }


    public function supervisorListeningGroupAction(Request $request, $group_id)
    {
        if (!$this->isGranted('ROLE_SUPERVISOR') && !$this->isGranted('ROLE_ADMIN_A3')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $view = $request->query->get('view') ? $request->query->get('view') : 'html';
        $version = $this->getVersion();
        $params = array();

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $notRootAdmin = $this->isGranted('ROLE_ADMIN_A2') || $this->isGranted('ROLE_ADMIN_A3');

        if ($notRootAdmin) {
            $companies_manager = $this->get("elearning_companies.companies_manager");
            $company = $companies_manager->currentUserCompany();
            $adminService = $this->get('elearning.admin_service');
            $groupIds = $adminService->getGroups($user, $company, true);
            if (!in_array($group_id, $groupIds)) {
                throw $this->createAccessDeniedException('Unable to access this page!');
            }
        }

        $group = $em->find('ElearningCompaniesBundle:Group', $group_id);
        $params['group'] = $group;


        $query = $em->createQuery(
            'SELECT e
            FROM ElearningCompaniesBundle:Employee e
            JOIN e.groups g
            WHERE g.id = :group_id
            AND e.type = :type'
        )->setParameter('group_id', $group->getId())
            ->setParameter('type', 'employee');
        $employees = $query->getResult();

        $query = $em->createQuery(
            'SELECT e.id, COUNT(c.id) AS num
            FROM ElearningCompaniesBundle:Employee e
            INDEX BY e.id
            JOIN e.groups g
            JOIN g.groupCourses gc
            JOIN gc.course c
            WHERE g.id = :group_id
            GROUP BY e'
        )->setParameter("group_id", $group_id);
        $coursecounts = $query->getResult(Query::HYDRATE_ARRAY);
        $params['coursecounts'] = $coursecounts;


        $query = $em->createQueryBuilder()
            ->select("e.id, COUNT(cl.id) AS num")
            ->from('ElearningCompaniesBundle:Employee', 'e', 'e.id')
            ->join('e.groups', 'g')
            ->join('ElearningCoursesBundle:CourseListening', 'cl', 'WITH', 'cl.user_id=e.user_id')
            ->where('g.id = :group_id')
            ->andWhere('cl.completed = 1')
            ->groupBy('e')
            ->getQuery()
            ->setParameter("group_id", $group_id);

        $completedcoursecounts = $query->getResult(Query::HYDRATE_ARRAY);
        $params['completedcoursecounts'] = $completedcoursecounts;

        $query = $em->createQueryBuilder()
            ->select("e.id, SUM(c.duration) AS hours")
            ->from('ElearningCompaniesBundle:Employee', 'e', 'e.id')
            ->join('e.groups', 'g')
            ->join('ElearningCoursesBundle:CourseListening', 'cl', 'WITH', 'cl.user_id=e.user_id')
            ->join('cl.course', 'c')
            ->leftJoin('ElearningCoursesBundle:ExamAttempt', 'ea', 'WITH', 'ea.listening_id=cl.id')
            ->where('g.id = :group_id')
            ->andWhere('cl.completed = 1')
            ->andWhere('(ea.passed = 1 OR ea.id IS NULL)')
            ->groupBy('e')
            ->getQuery()
            ->setParameter("group_id", $group_id);
        $completedcoursehours = $query->getResult(Query::HYDRATE_ARRAY);
        $params['completedcoursehours'] = $completedcoursehours;


        $query = $em->createQueryBuilder()
            ->select("e.id, AVG(ea.result) AS average")
            ->from('ElearningCompaniesBundle:Employee', 'e', 'e.id')
            ->join('e.groups', 'g')
            ->join('ElearningCoursesBundle:CourseListening', 'cl', 'WITH', 'cl.user_id=e.user_id')
            ->join('cl.examAttempts', 'ea')
            ->where('g.id = :group_id')
            ->andWhere('ea.result IS NOT NULL')
            ->groupBy('e')
            ->getQuery()
            ->setParameter("group_id", $group_id);
        $avgexamresults = $query->getResult(Query::HYDRATE_ARRAY);
        $params['avgexamresults'] = $avgexamresults;

        $query = $em->createQueryBuilder()
            ->select("e.id, MAX(cl.last_listen) AS last_action")
            ->from('ElearningCompaniesBundle:Employee', 'e', 'e.id')
            ->join('e.groups', 'g')
            ->join('ElearningCoursesBundle:CourseListening', 'cl', 'WITH', 'cl.user_id=e.user_id')
            ->where('g.id = :group_id')
            ->groupBy('e')
            ->getQuery()
            ->setParameter("group_id", $group_id);
        $lastactions = $query->getResult(Query::HYDRATE_ARRAY);
        $params['lastactions'] = $lastactions;


        $query = $em->createQueryBuilder()
            ->select("e.id, mch.hours AS hours")
            ->from('ElearningCompaniesBundle:Employee', 'e', 'e.id')
            ->join('e.manualCourseHours', 'mch')
            ->leftJoin('e.manualCourseHours', 'mch2', 'WITH', 'mch2.id > mch.id')
            ->join('e.groups', 'g')
            ->where('g.id = :group_id')
            ->andWhere('mch2.id IS NULL')
            ->groupBy('e')
            ->getQuery()
            ->setParameter("group_id", $group_id);
        $manualcoursehours = $query->getResult(Query::HYDRATE_ARRAY);
        $params['manualcoursehours'] = $manualcoursehours;

        $params['view'] = $view;

        if ($version == 'extended' && count($employees)) {
            $questionsCnt = $em->createQueryBuilder()
                ->select(array('e.id', 'COUNT(q.id) as cnt'))
                ->from('ElearningCoursesBundle:CourseListening', 'l')
                ->join('l.questions', 'q')
                ->join('l.user', 'u')
                ->join('u.employee', 'e')
                ->where('e IN (:employees)')
                ->groupBy('e')
                ->setParameter('employees', $employees)
                ->getQuery()->getResult();
            $questions = array();
            foreach ($questionsCnt as $cnt) {
                $questions[$cnt['id']] = $cnt['cnt'];
            }
            $params['questions'] = $questions;

            $groupsAdmins = $this->getGroupsAdmins();
            $employeeAdmins = array();
            foreach ($employees as $employee) {
                foreach ($employee->getGroups() as $group) {
                    if (isset($groupsAdmins[$group->getId()])) {
                        foreach ($groupsAdmins[$group->getId()] as $groupAdmin) {
                            $employeeAdmins[$employee->getId()][$groupAdmin['eid']] =
                                $groupAdmin;
                        }
                    }
                }
            }
            $params['employeeAdmins'] = $employeeAdmins;
        }

        $template = $this->getReportTemplate("supervisor_listening_group.html.twig", $version);

        if ($view == 'xlsx') {
            $params['employees'] = $employees;
            $html = $this->renderView($template, $params);
            return $this->returnExcel($this->createExcelFromHtml($html), 'listening_group.xlsx');
        }

        $paginator = $this->get('knp_paginator');
        $employees = $paginator->paginate(
            $employees,
            $request->query->getInt('page', 1)/*page number*/ ,
            10/*limit per page*/
        );
        $params['employees'] = $employees;

        return $this->render($template, $params);
    }

    /**
     * @Security("has_role('ROLE_MANAGER') or has_role('ROLE_SUPERVISOR')")
     */
    public function supervisorListeningEmployeeAction(Request $request, $group_id, $employee_id)
    {
        //$this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $view = $request->query->get('view') ? $request->query->get('view') : 'html';
        $params = array();

        $em = $this->getDoctrine()->getManager();

        $employee = $em->find("ElearningCompaniesBundle:Employee", $employee_id);
        $params['employee'] = $employee;

        $query = $em->createQuery(
            'SELECT l
            FROM ElearningCoursesBundle:CourseListening l
            JOIN l.groupCourse gc
            WHERE gc.group_id = :group_id
            AND l.user_id = :user_id'
        )->setParameter('group_id', $group_id)
            ->setParameter('user_id', $employee->getUser()->getId());
        $listenings = $query->getResult();
        $params['listenings'] = $listenings;

        $query = $em->createQueryBuilder()
            ->select("AVG(ea.result)")
            ->from('ElearningCompaniesBundle:Employee', 'e', 'e.id')
            ->join('e.groups', 'g')
            ->join('ElearningCoursesBundle:CourseListening', 'cl', 'WITH', 'cl.user_id=e.user_id')
            ->join('cl.examAttempts', 'ea')
            ->where('g.id = :group_id')
            ->andWhere('ea.result IS NOT NULL')
            ->andWhere('cl.user_id = :user_id')
            ->getQuery()
            ->setParameter("group_id", $group_id)
            ->setParameter('user_id', $employee->getUser()->getId());
        try {
            $avgexamresult = $query->getSingleScalarResult();
        } catch (ORMException $e) {
            $avgexamresult = 0;
        }
        $params['avgexamresult'] = $avgexamresult;

        $query = $em->createQueryBuilder()
            ->select("SUM(c.duration)")
            ->from('ElearningCompaniesBundle:Employee', 'e', 'e.id')
            ->join('e.groups', 'g')
            ->join('ElearningCoursesBundle:CourseListening', 'cl', 'WITH', 'cl.user_id=e.user_id')
            ->join('cl.course', 'c')
            ->leftJoin('ElearningCoursesBundle:ExamAttempt', 'ea', 'WITH', 'ea.listening_id=cl.id')
            ->where('g.id = :group_id')
            ->andWhere('cl.completed = 1')
            ->andWhere('cl.user_id = :user_id')
            ->andWhere('(ea.passed = 1 OR ea.id IS NULL)')
            ->groupBy('e')
            ->getQuery()
            ->setParameter("group_id", $group_id)
            ->setParameter('user_id', $employee->getUser()->getId());
        try {
            $completedcoursehours = $query->getSingleScalarResult();
        } catch (ORMException $e) {
            $completedcoursehours = 0;
        }
        $params['completedcoursehours'] = $completedcoursehours;

        $manualhours = $em->getRepository("ElearningCoursesBundle:ManualCourseEntry")
            ->findBy(array('employee_id' => $employee->getId()), array('updatetime' => 'DESC'));
        $params['manualhours'] = !empty($manualhours) ? $manualhours[0] : 0;
        $params['view'] = $view;
        $params['group_id'] = $group_id;

        $template = "ElearningCoursesBundle:Reports/_tables:supervisor_listening_employee.html.twig";

        if ($view == 'xlsx') {
            $html = $this->renderView($template, $params);
            return $this->returnExcel($this->createExcelFromHtml($html), 'employee.xlsx');
        }

        return $this->render($template, $params);
    }


    public function managerListeningAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $company = $user->getEmployee()->getCompany();

        $query = $em->createQuery(
            'SELECT l
            FROM ElearningCoursesBundle:CourseListening l
            JOIN l.groupCourse gc
            JOIN gc.group g
            WHERE g.company_id = :company_id'
        )->setParameter('company_id', $company->getId());
        $listenings = $query->getResult();


        $paginator = $this->get('knp_paginator');
        $listenings = $paginator->paginate(
            $listenings,
            $request->query->getInt('page', 1)/*page number*/ ,
            10/*limit per page*/
        );
        $params['listenings'] = $listenings;

        return $this->render(sprintf("ElearningCoursesBundle:Reports:manager_listening.html.twig"), $params);
    }


    /**
     * @Security("has_role('ROLE_MANAGER') or has_role('ROLE_SUPERVISOR')")
     */
    public function managerListeningEmployeeAction(Request $request, $listening_id)
    {
        //$this->denyAccessUnlessGranted('ROLE_MANAGER', null, 'Unable to access this page!');

        $params = array();

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $company = $user->getEmployee()->getCompany();

        $listening = $em->find("ElearningCoursesBundle:CourseListening", $listening_id);
        if ($listening->getUser()->getEmployee()->getCompany()->getId() != $company->getId()) {
            throw new AccessDeniedException("Not allowed");
        }

        $course = $listening->getCourse();

        $query = $em->createQueryBuilder()
            ->select(array('cc.id', 'cc.completed', 'cc.updatetime', 'c.name', 'c.type'))
            ->from('ElearningCoursesBundle:CourseCompletion', 'cc')
            ->leftJoin('cc.chapter', 'c')
            ->where('c.course_id = :course_id')
            ->andWhere("c.state = 'published' OR c.state IS NULL")
            ->setParameter('course_id', $course->getId())
            ->getQuery();

        $chapters = $query->getResult();

        $params['chaptersinfo'] = $chapters;

        $params['user'] = $listening->getUser();

        $params['listening'] = $listening;


        $examcorrectanswerscounts = array();

        foreach ($listening->getExamAttempts() as $attempt) {

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

            $correctcount = 0;
            foreach ($questionsResult as $question_id => $answered_count) {
                if ($answered_count == $questionsCorrectCount[$question_id] && !isset($questionsWithIncorrectAnswers[$question_id])) {
                    $correctcount++;
                }
            }
            $examcorrectanswerscounts[$attempt->getId()] = $correctcount;
        }

        $params['examcorrectanswerscounts'] = $examcorrectanswerscounts;


        return $this->render(sprintf("ElearningCoursesBundle:Reports:manager_listening_employee.html.twig"), $params);
    }


    public function managerGroupsAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER', null, 'Unable to access this page!');

        $view = $request->query->get('view') ? $request->query->get('view') : 'html';
        $params = array();

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $userIds = array($user->getId());
        $adminService = $this->get('elearning.admin_service');

        if ($this->isGranted('ROLE_ADMIN_A2') || $this->isGranted('ROLE_ADMIN_A3')) {
            $userIds = array_merge($userIds, $adminService->getChildrenIds($user));
        }

        $query = $em->createQuery(
            'SELECT g
            FROM ElearningCompaniesBundle:Group g
            JOIN g.employees e
            WHERE e.user_id in (:user_ids)'
        )->setParameter('user_ids', $userIds);
        $groups = $query->getResult();
        $params['groups'] = $groups;

        $reportparams = array();

        foreach ($groups as $group) {
            $groupparams = array();

            $query = $em->createQuery(
                'SELECT e
                FROM ElearningCompaniesBundle:Employee e
                JOIN e.groups g
                WHERE g.id = :group_id
                AND e.type = :type'
            )->setParameter('group_id', $group->getId())
                ->setParameter('type', 'employee');
            $employees = $query->getResult();
            $groupparams['employees'] = $employees;

            $query = $em->createQuery(
                'SELECT e.id, COUNT(c.id) AS num
                FROM ElearningCompaniesBundle:Employee e
                INDEX BY e.id
                JOIN e.groups g
                JOIN g.groupCourses gc
                JOIN gc.course c
                WHERE g.id = :group_id
                GROUP BY e'
            )->setParameter("group_id", $group->getId());
            $coursecounts = $query->getResult(Query::HYDRATE_ARRAY);
            $groupparams['coursecounts'] = $coursecounts;


            $query = $em->createQueryBuilder()
                ->select("e.id, COUNT(cl.id) AS num")
                ->from('ElearningCompaniesBundle:Employee', 'e', 'e.id')
                ->join('e.groups', 'g')
                ->join('ElearningCoursesBundle:CourseListening', 'cl', 'WITH', 'cl.user_id=e.user_id')
                ->where('g.id = :group_id')
                ->andWhere('cl.completed = 1')
                ->groupBy('e')
                ->getQuery()
                ->setParameter("group_id", $group->getId());

            $completedcoursecounts = $query->getResult(Query::HYDRATE_ARRAY);
            $groupparams['completedcoursecounts'] = $completedcoursecounts;


            $query = $em->createQueryBuilder()
                ->select("e.id, AVG(ea.result) AS average")
                ->from('ElearningCompaniesBundle:Employee', 'e', 'e.id')
                ->join('e.groups', 'g')
                ->join('ElearningCoursesBundle:CourseListening', 'cl', 'WITH', 'cl.user_id=e.user_id')
                ->join('cl.examAttempts', 'ea')
                ->where('g.id = :group_id')
                ->andWhere('ea.result IS NOT NULL')
                ->groupBy('e')
                ->getQuery()
                ->setParameter("group_id", $group->getId());
            $avgexamresults = $query->getResult(Query::HYDRATE_ARRAY);
            $groupparams['avgexamresults'] = $avgexamresults;

            $query = $em->createQueryBuilder()
                ->select("e.id, MAX(cl.last_listen) AS last_action")
                ->from('ElearningCompaniesBundle:Employee', 'e', 'e.id')
                ->join('e.groups', 'g')
                ->join('ElearningCoursesBundle:CourseListening', 'cl', 'WITH', 'cl.user_id=e.user_id')
                ->where('g.id = :group_id')
                ->groupBy('e')
                ->getQuery()
                ->setParameter("group_id", $group->getId());
            $lastactions = $query->getResult(Query::HYDRATE_ARRAY);
            $groupparams['lastactions'] = $lastactions;

            $reportparams[$group->getId()] = $groupparams;
        }
        $params['reportparams'] = $reportparams;
        $params['view'] = $view;

        $template = "ElearningCoursesBundle:Reports/_tables:manager_listening_groups.html.twig";

        if ($view == 'xlsx') {
            $html = $this->renderView($template, $params);
            return $this->returnExcel($this->createExcelFromHtml($html), 'groups.xlsx');
        }

        return $this->render($template, $params);
    }

    /**
     * @Security("has_role('ROLE_MANAGER') or has_role('ROLE_SUPERVISOR')")
     */
    public function enterManualHoursAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $employee_id = $request->request->get('employee_id');
        $employee = $em->find('ElearningCompaniesBundle:Employee', $employee_id);

        $hours = $request->request->get('hours');

        $entry = new ManualCourseEntry();
        $entry->setEmployee($employee);
        $entry->setHours($hours);
        $entry->setUpdatetime(new \DateTime());
        $em->persist($entry);
        $em->flush();

        return new JsonResponse(array('success' => true, 'hours' => $hours));
    }

    public function supervisorAttendanceAction($group_id, $state, $format)
    {
        if ((!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) || $this->isGranted('ROLE_ADMIN_A3')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $current_user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $params = array();
        $companies_manager = $this->get("elearning_companies.companies_manager");
        $company = $companies_manager->currentUserCompany();

        $rep = $em->getRepository("ElearningCompaniesBundle:Employee");

        $current_manager = $rep->findOneBy(array('user_id' => $current_user->getId()));

        $groupsrep = $em->getRepository("ElearningCompaniesBundle:Group");
        $groups = $groupsrep->findBy(array('company_id' => $company->getId(), 'state' => 'published'));

        $active = true;

        if ($state == 'expelled') {
            $active = false;
        }

        if (!empty($groups)) {

            $groups_managers = array();

            foreach ($groups as $group) {
                $managers = array();

                $query = $em->createQuery(
                    'SELECT e
                FROM ElearningCompaniesBundle:Employee e
                JOIN e.groups g
                WHERE g.id = :group_id
                AND e.type = :type'
                )->setParameter('group_id', $group->getId())
                    ->setParameter('type', 'manager');

                $managers = $query->getResult();
                $groups_managers[$group->getId()] = $managers;
            }

            $params['managers'] = $groups_managers;

            if ($group_id == 0) {
                $currentgroup = $groups[0];
            } else {
                $currentgroup = $em->find("ElearningCompaniesBundle:Group", $group_id);
            }
            $params['currentgroup'] = $currentgroup;

            $params['granted'] = $this->isGrantedGroup($currentgroup, $current_manager);

            $query = $em->createQuery(
                'SELECT e
                FROM ElearningCompaniesBundle:Employee e
                JOIN e.groups g
                WHERE g.id = :group_id
                AND e.type = :type
                AND e.state = :state
                AND e.active = :active'
            )->setParameter('group_id', $currentgroup->getId())
                ->setParameter('state', 'published')
                ->setParameter('active', $active);

            $currentgroupemployees = $query
                ->setParameter('type', 'employee')
                ->getResult();

            $params['currentgroupemployees'] = $currentgroupemployees;
        }

        $params['groups'] = $groups;
        $params['statuses'] = $em->getRepository("ElearningCoursesBundle:SubjectStatus")->findBy(array('active' => 1));

        //$subjects = $currentgroup->getSubjects();

        $params['subjects'] = $currentgroup->getActiveSubjects();

        $subject_form = $this->createForm(new SubjectType(), new Subject(), array(
            'action' => $this->generateUrl('elearning_courses_report_subject_add'),
            'method' => 'POST'
        ));

        $params['subject_form'] = $subject_form->createView();

        $subject_status_form = $this->createForm(new SubjectStatusType(), new SubjectStatus(), array(
            'action' => $this->generateUrl('elearning_courses_report_subject_status_add'),
            'method' => 'POST'
        ));

        $params['subject_status_form'] = $subject_status_form->createView();

        $query = $em->createQuery(
            'SELECT es
                FROM ElearningCoursesBundle:EmployeeSubject es
                JOIN es.subject s 
                WHERE s.group_id = :group_id
                AND s.active = 1'
        )->setParameter('group_id', $currentgroup->getId());
        $saved_statuses = $query->getResult();

        $app_config = $this->container->getParameter('app_config');
        $status_table = array();
        $attandance = array();
        $params['resultDim'] = '';
        $calcStrategy = isset($app_config['attendance_calc_strategy']) ? $app_config['attendance_calc_strategy'] : null;


        foreach ($saved_statuses as $status) {

            $status_table[$status->getEmployeeId() . '_' . $status->getSubjectId()] = $status->getSubjectStatusId();

            if ($calcStrategy == 'percent') {
                if ($status->getSubjectStatus() && in_array($status->getSubjectStatus()->getCode(), array('0', '1'))) {
                    $attendance[$status->getEmployeeId()]['cnt'] = isset($attendance[$status->getEmployeeId()]['cnt']) ? $attendance[$status->getEmployeeId()]['cnt'] + 1 : 1;
                    if ($status->getSubjectStatus()->getCode() == '1') {
                        $attendance[$status->getEmployeeId()]['visits'] = isset($attendance[$status->getEmployeeId()]['visits']) ? $attendance[$status->getEmployeeId()]['visits'] + 1 : 1;
                    }
                }
            } else {
                if (isset($params['attendance'][$status->getEmployeeId()])) {
                    $params['attendance'][$status->getEmployeeId()] += (int) $status->getSubjectStatus()->getCode();
                } else {
                    $params['attendance'][$status->getEmployeeId()] = (int) $status->getSubjectStatus()->getCode();
                }
            }
        }

        if ($calcStrategy == 'percent') {
            if ($attendance) {
                foreach ($attendance as $employee => $data) {
                    $perc = isset($data['visits']) ? round($data['visits'] / $data['cnt'] * 100) : 0;
                    $params['attendance'][$employee] = $perc;
                }
            }
            $params['resultDim'] = '%';
        }

        $params['status_table'] = $status_table;
        $params['state'] = $state;

        $showParent = !empty($app_config['extended_admins_structure']) && $app_config['extended_admins_structure'];

        $form = $this->createForm(new EmployeeProfileFieldsType(), null, array('showParent' => $showParent));
        $params['employee_fields_form'] = $form->createView();
        $params['diary_enabled'] = isset($app_config['diary']) ? $app_config['diary'] : false;

        if ($format == 'excel') {
            return $this->exportAttendanceXLSX($params);
        }

        return $this->render('ElearningCoursesBundle:CourseLecturer:attendance.html.twig', $params);
    }

    public function addSubjectAction(Request $request)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $subject = new Subject();
        $group_id = $request->get('subject')['group_id'];

        $em = $this->getDoctrine()->getManager();
        $group = $em->getReference('ElearningCompaniesBundle:Group', $group_id);

        $form = $this->createForm(new SubjectType(), $subject);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $subject->setGroup($group);
            $subject->setActive(true);
            $em->persist($subject);
            $em->flush();
            return new JsonResponse(array('success' => true));
        }

        return new JsonResponse(array('success' => false));
    }

    public function addStatusAction($employee_id, $subject_id, $subject_status_id = null)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $em = $this->getDoctrine()->getManager();

        $employee_subject = $em->getRepository('ElearningCoursesBundle:EmployeeSubject')->findOneBy(array('employee_id' => $employee_id, 'subject_id' => $subject_id));

        if (!$employee_subject) {
            $employee_subject = new EmployeeSubject();
            $employee_subject->setEmployee($em->getReference('ElearningCompaniesBundle:Employee', $employee_id));
            $employee_subject->setSubject($em->getReference('ElearningCoursesBundle:Subject', $subject_id));
        }

        $current_user = $this->getUser();
        $current_manager = $em->getRepository("ElearningCompaniesBundle:Employee")->findOneBy(array('user_id' => $current_user->getId()));

        if (!$this->isGrantedGroup($employee_subject->getSubject()->getGroup(), $current_manager)) {
            return new JsonResponse(array('success' => false));
        }

        if ($subject_status_id != 'null') {
            $employee_subject->setSubjectStatus($em->getReference('ElearningCoursesBundle:SubjectStatus', $subject_status_id));
            $em->persist($employee_subject);
        } else {
            $em->remove($employee_subject);
        }

        $em->flush();
        return new JsonResponse(array('success' => true));
    }

    public function expellAction($employee_id)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $em = $this->getDoctrine()->getManager();
        $employee = $em->getReference('ElearningCompaniesBundle:Employee', $employee_id);
        $active = $employee->getActive();
        $employee->setActive(!$active);
        $em->persist($employee);
        $em->flush();
        return new JsonResponse(array('success' => true));
    }

    public function toggleSubjectAction($subject_id)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $em = $this->getDoctrine()->getManager();
        $subject = $em->getReference('ElearningCoursesBundle:Subject', $subject_id);

        $current_user = $this->getUser();
        $current_manager = $em->getRepository("ElearningCompaniesBundle:Employee")->findOneBy(array('user_id' => $current_user->getId()));

        if (!$this->isGrantedGroup($subject->getGroup(), $current_manager)) {
            return new JsonResponse(array('success' => false));
        }

        $active = $subject->getActive();
        $subject->setActive(!$active);
        $em->persist($subject);
        $em->flush();
        return new JsonResponse(array('success' => true));
    }

    public function subjectUpdateAction(Request $request)
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $subject_id = $request->request->get('subject_id');
        $description = $request->request->get('description');

        if (!$subject_id) {
            return new JsonResponse(array('success' => false));
        }

        $em = $this->getDoctrine()->getManager();
        $subject = $em->getReference('ElearningCoursesBundle:Subject', $subject_id);

        $current_user = $this->getUser();
        $current_manager = $em->getRepository("ElearningCompaniesBundle:Employee")->findOneBy(array('user_id' => $current_user->getId()));

        if (!$this->isGrantedGroup($subject->getGroup(), $current_manager)) {
            return new JsonResponse(array('success' => false));
        }

        $subject->setDescription($description);
        $em->persist($subject);
        $em->flush();

        return new JsonResponse(array('success' => true));
    }

    public function subjectStatusAddAction(Request $request)
    {

        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $subject_status = new SubjectStatus();

        $form = $this->createForm(new SubjectStatusType(), $subject_status);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $subject_status->setActive(true);
            $em->persist($subject_status);
            $em->flush();
            return new JsonResponse(array('success' => true));
        }

        return new JsonResponse(array('success' => false));

    }

    public function subjectStatusEditAction(Request $request)
    {

        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $action = $request->request->get('action');
        $status_id = $request->request->get('status_id');

        if ($action == 'delete' && $status_id) {
            $em = $this->getDoctrine()->getManager();
            $status = $em->getReference('ElearningCoursesBundle:SubjectStatus', $status_id);
            $status->setActive(false);
            $em->persist($status);
            $em->flush();
            return new JsonResponse(array('success' => true));
        }

        return new JsonResponse(array('success' => false));
    }

    public function studentAttendanceAction()
    {

        $current_user = $this->getUser();

        $params = array();
        $em = $this->getDoctrine()->getManager();

        $rep = $em->getRepository("ElearningCompaniesBundle:Employee");
        $current_student = $rep->findOneBy(array('user_id' => $current_user->getId()));

        if (!$current_student) {
            return $this->render('ElearningCoursesBundle:CourseStudent:attendance.html.twig', array(
                'groups' => array(),
                'status_table' => array(),
                'resultDim' => ''
            ));
        }

        $groups = $current_student->getGroups();

        $params['groups'] = $groups;

        $saved_statuses = $current_student->getEmployeeSubject();
        $status_table = array();
        $attendance = array();

        $params['resultDim'] = '';
        $app_config = $this->container->getParameter('app_config');
        $calcStrategy = isset($app_config['attendance_calc_strategy']) ? $app_config['attendance_calc_strategy'] : null;

        foreach ($saved_statuses as $status) {

            $status_table[$status->getSubjectId()] = $status->getSubjectStatus()->getCode();

            if ($status->getSubject()->getActive()) {
                if ($calcStrategy == 'percent') {
                    if ($status->getSubjectStatus() && in_array($status->getSubjectStatus()->getCode(), array('0', '1'))) {
                        $attendance[$status->getSubject()->getGroupId()]['cnt'] = isset($attendance[$status->getSubject()->getGroupId()]['cnt']) ? $attendance[$status->getSubject()->getGroupId()]['cnt'] + 1 : 1;
                        if ($status->getSubjectStatus()->getCode() == '1') {
                            $attendance[$status->getSubject()->getGroupId()]['visits'] = isset($attendance[$status->getSubject()->getGroupId()]['visits']) ? $attendance[$status->getSubject()->getGroupId()]['visits'] + 1 : 1;
                        }
                    }
                } else {
                    if (isset($params['attendance'][$status->getSubject()->getGroupId()])) {
                        $params['attendance'][$status->getSubject()->getGroupId()] += (int) $status->getSubjectStatus()->getCode();
                    } else {
                        $params['attendance'][$status->getSubject()->getGroupId()] = (int) $status->getSubjectStatus()->getCode();
                    }
                }
            }
        }
        if ($calcStrategy == 'percent') {
            if ($attendance) {
                foreach ($attendance as $group => $data) {
                    $perc = round($data['visits'] / $data['cnt'] * 100);
                    $params['attendance'][$group] = $perc;
                }
            }
            $params['resultDim'] = '%';
        }

        $params['status_table'] = $status_table;

        return $this->render('ElearningCoursesBundle:CourseStudent:attendance.html.twig', $params);
    }

    private function isGrantedGroup($current_group, $manager)
    {
        if ($this->isGranted('ROLE_SUPERVISOR')) {
            return true;
        }

        if ($this->isGranted('ROLE_MANAGER')) {
            $allowed_groups = $manager->getGroups();
            foreach ($allowed_groups as $allowed_group) {
                if ($current_group->getId() == $allowed_group->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function exportAttendanceXLSX($params)
    {
        $excelService = $this->get('phpexcel');
        $translator = $this->get('translator');
        $phpExcelObject = $excelService->createPHPExcelObject();
        $phpExcelObject->setActiveSheetIndex(0);
        $sheet = $phpExcelObject->getActiveSheet();
        $sheet->setTitle($params['state']);

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $statuses = array();
        foreach ($params['statuses'] as $status) {
            $statuses[$status->getId()] = $status->getCode();
        }

        $row = 1;
        $column = 0;

        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 1, $row);
        $sheet->mergeCellsByColumnAndRow($column, $row + 1, $column + 1, $row + 1);
        $sheet->setCellValueByColumnAndRow($column, $row, $translator->trans('reports.attendance.subject'));
        $sheet->setCellValueByColumnAndRow($column, $row + 1, $translator->trans('reports.attendance.name'));
        $sheet->getColumnDimensionByColumn($column)->setWidth(22);

        $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setWrapText(true);
        $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $column = 2;
        foreach ($params['subjects'] as $subject) {
            $sheet->setCellValueByColumnAndRow($column, $row, $subject->getDescription());
            $sheet->setCellValueByColumnAndRow($column, $row + 1, $subject->getLessonDate()->format('Y-m-d'));

            $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setWrapText(true);
            $sheet->getColumnDimensionByColumn($column)->setWidth(12);
            $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $column++;
        }


        $row = 3;
        $column = 0;
        foreach ($params['currentgroupemployees'] as $employee) {
            $column = 0;
            $employeeName = $employee->getFieldValue("name") . ' ' . $employee->getFieldValue("surname");

            $sheet->setCellValueByColumnAndRow($column, $row, $employeeName);
            $column++;
            if (isset($params['attendance'][$employee->getId()])) {
                $sheet->setCellValueByColumnAndRow($column, $row, $params['attendance'][$employee->getId()] . '%');
            }
            $column++;
            foreach ($params['subjects'] as $subject) {
                if (isset($params['status_table'][$employee->getId() . '_' . $subject->getId()])) {
                    $statusId = $params['status_table'][$employee->getId() . '_' . $subject->getId()];
                    if (isset($statuses[$statusId])) {
                        $sheet->setCellValueByColumnAndRow($column, $row, $statuses[$statusId]);
                    }
                }
                $column++;
            }

            $row++;
        }

        if ($column >= 1 && $row >= 1) {
            $sheet->getStyle('A1:' . \PHPExcel_Cell::stringFromColumnIndex($column - 1) . ($row - 1))->applyFromArray($styleArray);
        }

        return $this->returnExcel($phpExcelObject, 'attendance.xlsx');
    }

    private function createExcelFromHtml($html)
    {
        $excelService = $this->get('phpexcel');
        $file = tempnam(sys_get_temp_dir(), 'excel_');
        $handle = fopen($file, "w");
        fwrite($handle, $html);
        $objPHPExcelReader = \PHPExcel_IOFactory::createReader('HTML');
        $objPHPExcel = $objPHPExcelReader->load($file);
        fclose($handle);
        unlink($file);

        $sheet = $objPHPExcel->setActiveSheetIndex(0);

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        $dimension = $sheet->calculateWorksheetDimension();
        $sheet->getStyle($dimension)->applyFromArray($styleArray);
        $sheet->getDefaultColumnDimension()->setWidth(20);
        $sheet->getStyle($dimension)->getAlignment()->setWrapText(true);
        return $objPHPExcel;
    }

    private function returnExcel(\PHPExcel $excel, $fileName, $format = "Excel2007")
    {
        $excelService = $this->get('phpexcel');
        $objWriter = $excelService->createWriter($excel, $format);
        $response = $excelService->createStreamedResponse($objWriter);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    private function getReportTemplate($templateName, $version = '')
    {
        $basePath = 'ElearningCoursesBundle:Reports/_tables';
        $template = "$basePath/$version:$templateName";
        if ($this->container->get('templating')->exists($template)) {
            return $template;
        }

        return "$basePath:$templateName";
    }

    private function getGroupsAdmins()
    {
        return $this->get('elearning.admin_service')->getGroupAdmins();
    }

    private function getVersion()
    {
        $allowedVersions = array('standard', 'extended');

        $version = $this->getRequest()->query->get('version');
        if (in_array($version, $allowedVersions)) {
            return $version;
        }

        $appConfig = $this->container->getParameter('app_config');
        $version = isset($appConfig['reports_default_version']) ?
            $appConfig['reports_default_version'] : 'standard';
        if (in_array($version, $allowedVersions)) {
            return $version;
        }

        return 'standard';
    }
}
