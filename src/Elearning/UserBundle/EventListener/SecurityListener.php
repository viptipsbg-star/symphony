<?php

namespace Elearning\UserBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityListener
{
    protected $router;
    protected $authorizationChecker;
    protected $tokenStorage;
    protected $dispatcher;
    protected $session;

    public function __construct(Router $router, AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage, Session $session, EventDispatcherInterface $dispatcher)
    {
        $this->router = $router;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
        $this->session = $session;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->dispatcher->addListener(KernelEvents::RESPONSE, array($this, 'onKernelResponseLogin'));
    }

    public function onKernelResponseLogin(FilterResponseEvent $event)
    {
        $response = $this->getFrontPageRouteByRole();
        if ($response) {
            $event->setResponse($response);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $route = $event->getRequest()->get('_route');

        if (
            ($this->tokenStorage->getToken()) && ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY'))
            && $route != 'elearning_user_edit_credentials'
            && $this->tokenStorage->getToken()->getUser()->hasRole('ROLE_FORCECREDENTIALCHANGE')
        ) {
            $response = new RedirectResponse($this->router->generate('elearning_user_edit_credentials'));
            $event->setResponse($response);
        }

    }


    public function onKernelRequest(GetResponseEvent $event)
    {
        $route = $event->getRequest()->get('_route');
        if ($route == "elearning_main_homepage") {
            $response = $this->getFrontPageRouteByRole();
            if (!empty($response)) {
                $event->setResponse($response);
            }
        }
    }

    public function getFrontPageRouteByRole()
    {
        $response = null;
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $response = new RedirectResponse($this->router->generate('sonata_admin_redirect'));
        } elseif ($this->authorizationChecker->isGranted('ROLE_ADMIN_A1')) {
            $response = new RedirectResponse($this->router->generate('elearning_companies_employees_list'));
        } elseif ($this->authorizationChecker->isGranted('ROLE_COURSES_LECTURER')) {
            $response = new RedirectResponse($this->router->generate('elearning_courses_list_lecturer'));
        } elseif ($this->authorizationChecker->isGranted('ROLE_COURSES_MANAGER')) {
            $response = new RedirectResponse($this->router->generate('elearning_course_report_manager_groups'));
        } elseif ($this->authorizationChecker->isGranted('ROLE_COURSES_STUDENT')) {
            $response = new RedirectResponse($this->router->generate('elearning_my_courses_list_student'));
        } elseif ($this->authorizationChecker->isGranted('ROLE_COURSES_SUPERVISOR')) {
            $response = new RedirectResponse($this->router->generate('elearning_my_courses_list_student'));
        }
        return $response;
    }
}
