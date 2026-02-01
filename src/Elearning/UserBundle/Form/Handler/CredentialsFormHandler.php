<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elearning\UserBundle\Form\Handler;

use Elearning\UserBundle\Form\Model\Credentials;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CredentialsFormHandler
{
    protected $request;
    protected $userManager;
    protected $form;

    public function __construct(FormInterface $form, RequestStack $requestStack, UserManagerInterface $userManager)
    {
        $this->form = $form;
        $this->request = $requestStack->getCurrentRequest();
        $this->userManager = $userManager;
    }

    /**
     * @return string
     */
    public function getNewPassword()
    {
        return $this->form->getData()->new;
    }

    public function getNewEmail()
    {
        return $this->form->getData()->email;
    }

    public function process(UserInterface $user)
    {
        $this->form->setData(new Credentials());

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    protected function onSuccess(UserInterface $user)
    {
        $user->setEmail($this->getNewEmail());
        $user->setPlainPassword($this->getNewPassword());
        $user->removeRole("ROLE_FORCECREDENTIALCHANGE");
        $this->userManager->updateUser($user);
    }
}
