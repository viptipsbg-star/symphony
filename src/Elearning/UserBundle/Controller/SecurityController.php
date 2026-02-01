<?php

namespace Elearning\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Elearning\UserBundle\Entity\User as BaseUser;

class SecurityController extends BaseController
{
    /**
     * @return Response
     */
    public function loginAction(Request $request)
    {
        // $request is now passed as an argument, so we use it directly or ignore it if we fetch from stack.
        // But for compatibility with parent, we must accept it.
        // We can still use request_stack if we prefer consistent access, or use the argument.
        // Let's use the argument to avoid the deprecation warning about signature mismatch.
        $authUtils = $this->container->get('security.authentication_utils');

        // get the error if any (works with forward and redirect -- see below)
        $error = $authUtils->getLastAuthenticationError();

        if ($error) {
            $error = $error->getMessage();
        }

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        $csrfToken = $this->container->has('security.csrf.token_manager')
            ? $this->container->get('security.csrf.token_manager')->getToken('authenticate')->getValue()
            : null;

        return $this->renderLogin(array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ));
    }

    public function editCredentialsAction(Request $request)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        if (!is_object($user) || !$user instanceof BaseUser) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->container->get('elearning.user.form.credentials');
        $formHandler = $this->container->get('elearning.user.form.handler.credentials');

        $process = $formHandler->process($user);
        if ($process) {
            return new RedirectResponse($this->container->get('router')->generate('elearning_main_homepage'));
        }

        return $this->container->get('templating')->renderResponse(
            'ElearningUserBundle:Security:credentials.html.twig',
            array('form' => $form->createView())
        );
    }
}
