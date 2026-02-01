<?php

namespace Elearning\UserBundle\Mailer;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\Mailer as BaseMailer;

class Mailer extends BaseMailer
{
    /**
     * @param UserInterface $user
     */
    public function sendCreatedUserEmail(UserInterface $user)
    {
        $template = 'ElearningUserBundle:Registration:created_user_email.txt.twig';
        $rendered = $this->templating->render($template, array(
            'user' => $user,
            'password' => $user->getPlainPassword(),
        ));
        $this->sendEmailMessage($rendered,
            array($this->parameters['from_email']['address'] => $this->parameters['from_email']['sender_name']), $user->getEmail());
    }
}
