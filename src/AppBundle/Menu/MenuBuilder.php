<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class MenuBuilder
{

    protected $factory;
    protected $authorizationChecker;
    protected $tokenStorage;
    protected $translator;

    public function __construct(FactoryInterface $factory, TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage)
    {
        $this->factory = $factory;
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public function createMainMenu(RequestStack $requestStack, $params, $systemId)
    {
        $userIsLoggedin = ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'));
        $userIsLecturer = ($this->authorizationChecker->isGranted('ROLE_COURSES_LECTURER'));
        $userIsManager = ($this->authorizationChecker->isGranted('ROLE_COURSES_MANAGER'));
        $userIsStudent = ($this->authorizationChecker->isGranted('ROLE_COURSES_STUDENT'));
        $userIsSupervisor = ($this->authorizationChecker->isGranted('ROLE_COURSES_SUPERVISOR'));
        $userIsAdminA3 = ($this->authorizationChecker->isGranted('ROLE_ADMIN_A3'));
        $userIsAdminA1 = ($this->authorizationChecker->isGranted('ROLE_ADMIN_A1'));

        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav pull-right');

        $menu->addChild($this->translator->trans("menu.home"), array('route' => 'elearning_main_homepage'))->setAttribute('class', 'nav-btn');

        if ($userIsLoggedin) {
            if ($userIsLecturer && !$userIsAdminA1) {
                if ($systemId === 'vl') {
                    $materialsMenuName = $this->translator->trans("menu.materials");
                    $menu->addChild($materialsMenuName)->setAttribute('dropdown', true)->setAttribute('class', 'nav-btn');
                    $menu[$materialsMenuName]->addChild($this->translator->trans("menu.vl_material_1"), array('uri' => 'https://www.dropbox.com/sh/nmxrntih6gldcb3/AADfwaMeoRc7bAG7I_vikt8Ia?dl=0', 'linkAttributes' => array('target' => '_blank')));
                    $menu[$materialsMenuName]->addChild($this->translator->trans("menu.vl_material_2"), array('uri' => 'https://www.dropbox.com/sh/i2j4xvkfu0iro05/AAB4EL84St1xvxlRfxVi1BSfa?dl=0', 'linkAttributes' => array('target' => '_blank')));
                }

                $courseMenuName = $this->translator->trans("menu.course");
                $menu->addChild($courseMenuName)->setAttribute('dropdown', true)->setAttribute('class', 'nav-btn');
                $menu[$courseMenuName]->addChild($this->translator->trans("menu.new_course"), array('route' => 'elearning_courses_new'));
                $menu[$courseMenuName]->addChild($this->translator->trans("menu.course_list"), array('route' => 'elearning_courses_list_lecturer'));
                $menu[$courseMenuName]->addChild($this->translator->trans("menu.categories"), array('route' => 'elearning_courses_categories'));

                // Add Reflections for Teachers
                $menu->addChild($this->translator->trans("reflection.teacher.list_title"), array('route' => 'teacher_reflection_list'))->setAttribute('class', 'nav-btn');


            } else if ($userIsStudent) {
                $courseMenuName = $this->translator->trans("menu.course");
                $menu->addChild($courseMenuName)->setAttribute('dropdown', true)->setAttribute('class', 'nav-btn');
                $menu[$courseMenuName]->addChild($this->translator->trans("menu.my_courses"), array('route' => 'elearning_my_courses_list_student'));
                $menu[$courseMenuName]->addChild($this->translator->trans("menu.available_courses"), array('route' => 'elearning_available_courses_list_student'));

                // Add Reflections for Students
                $menu->addChild($this->translator->trans("reflection.student.list_title"), array('route' => 'student_reflection_list'))->setAttribute('class', 'nav-btn');



                $reportsMenuName = $this->translator->trans("menu.reports");
                $menu->addChild($reportsMenuName)->setAttribute('dropdown', true)->setAttribute('class', 'nav-btn');
                $menu[$reportsMenuName]->addChild($this->translator->trans("menu.report.course_listening"), array('route' => 'elearning_course_report_student_listening'));

                if (isset($params['attendance']) && $params['attendance']) {
                    $menu[$reportsMenuName]->addChild($this->translator->trans("menu.report.attendance"), array('route' => 'elearning_courses_report_student_attendance'));
                }
                if (isset($params['diary']) && $params['diary']) {
                    $menu[$reportsMenuName]->addChild($this->translator->trans("menu.report.my_diary"), array('route' => 'elearning_courses_student_diary'));
                }
            }
            if ($userIsManager && !$userIsAdminA3) {
                if ($systemId === 'vl') {
                    $materialsMenuName = $this->translator->trans("menu.materials");
                    $menu->addChild($materialsMenuName)->setAttribute('dropdown', true)->setAttribute('class', 'nav-btn');
                    $menu[$materialsMenuName]->addChild($this->translator->trans("menu.vl_material_1"), array('uri' => 'https://www.dropbox.com/sh/nmxrntih6gldcb3/AADfwaMeoRc7bAG7I_vikt8Ia?dl=0', 'linkAttributes' => array('target' => '_blank')));
                    $menu[$materialsMenuName]->addChild($this->translator->trans("menu.vl_material_2"), array('uri' => 'https://www.dropbox.com/sh/i2j4xvkfu0iro05/AAB4EL84St1xvxlRfxVi1BSfa?dl=0', 'linkAttributes' => array('target' => '_blank')));
                }
                $reportsMenuName = $this->translator->trans("menu.reports");
                $menu->addChild($reportsMenuName)->setAttribute('dropdown', true)->setAttribute('class', 'nav-btn');
                $menu[$reportsMenuName]->addChild($this->translator->trans("menu.report.course_listening"), array('route' => 'elearning_course_report_manager_groups'));

                if (isset($params['attendance']) && $params['attendance']) {
                    $menu[$reportsMenuName]->addChild($this->translator->trans("menu.report.attendance"), array('route' => 'elearning_courses_report_supervisor_attendance'));
                }

                //$menu[$reportsMenuName]->addChild($this->translator->trans("menu.report.course"), array('route' => 'elearning_course_report_manager_courses'));
            }

            if ($userIsSupervisor) {
                $studentsMenuName = $this->translator->trans("menu.students");
                $menu->addChild($studentsMenuName)->setAttribute('dropdown', true)->setAttribute('class', 'nav-btn');
                $menu[$studentsMenuName]->addChild($this->translator->trans("menu.groups"), array('route' => 'elearning_companies_groups'));
                $menu[$studentsMenuName]->addChild($this->translator->trans("menu.employees"), array('route' => 'elearning_companies_employees_list'));
                $menu[$studentsMenuName]->addChild($this->translator->trans("menu.import"), array('route' => 'elearning_companies_import_employees'));
            }

            if (($userIsSupervisor && !$userIsAdminA1) || $userIsAdminA3) {
                $reportsMenuName = $this->translator->trans("menu.reports");
                $menu->addChild($reportsMenuName)->setAttribute('dropdown', true)->setAttribute('class', 'nav-btn');
                $menu[$reportsMenuName]->addChild($this->translator->trans("menu.report.course"), array('route' => 'elearning_courses_report_supervisor_courses'));
                $menu[$reportsMenuName]->addChild($this->translator->trans("menu.report.course_listening"), array('route' => 'elearning_courses_report_supervisor_listening_groups'));

                if (isset($params['attendance']) && $params['attendance']) {
                    $menu[$reportsMenuName]->addChild($this->translator->trans("menu.report.attendance"), array('route' => 'elearning_courses_report_supervisor_attendance'));
                }
            }


            $username = $this->tokenStorage->getToken()->getUser()->getUsername();
            $menu->addChild($username)->setAttribute('dropdown', true)->setAttribute('class', 'profile-btn nav-btn');

            //$menu[$username]->addChild($this->translator->trans("menu.profile"), array('route' => 'fos_user_profile_edit'));
            $menu[$username]->addChild($this->translator->trans("menu.profile"), array('route' => 'fos_user_profile_edit'));
            $menu[$username]->addChild($this->translator->trans("menu.change_password"), array('route' => 'fos_user_change_password'));

            $menu[$username]->addChild($this->translator->trans("menu.logout"), array('route' => 'fos_user_security_logout'));
        }

        // ... add more children

        return $menu;
    }
}
