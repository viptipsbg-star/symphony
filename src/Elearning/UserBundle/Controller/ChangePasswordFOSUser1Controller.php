<?php

namespace Elearning\UserBundle\Controller;

use Sonata\UserBundle\Controller\ChangePasswordFOSUser1Controller as BaseController;
use FOS\UserBundle\Model\UserInterface;

class ChangePasswordFOSUser1Controller extends BaseController
{
    /**
     * {@inheritdoc}
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('fos_user_change_password');
    }

}
