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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use FOS\UserBundle\Mailer\MailerInterface;
use Elearning\UserBundle\UserEvents;
use Elearning\UserBundle\Event\RegistrationEvent;
use Symfony\Bridge\Monolog\Logger;


class EmailConfirmationListener implements EventSubscriberInterface
{
    private $mailer;
    private $logger;

    public function __construct(MailerInterface $mailer, Logger $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            UserEvents::USER_REGISTRATION => array(
                array('onRegistrationSuccess'),
            ),
        );
    }

    public function onRegistrationSuccess(RegistrationEvent $event)
    {
        /** @var $user \FOS\UserBundle\Model\UserInterface */
        $user = $event->getUser();

        // send details out to the user
        $email = $user->getEmail();
        if (!empty($email)) {
            $result = $this->mailer->sendCreatedUserEmail($user);
        }

        /*
        // Your route to show the admin that the user has been created
        $url = $this->router->generate('blah_blah_user_created');
        $event->setResponse(new RedirectResponse($url));
         */

        // Stop the later events propagting
        $event->stopPropagation();
    }
}

