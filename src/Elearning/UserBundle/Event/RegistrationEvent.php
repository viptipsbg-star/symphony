<?php

namespace Elearning\UserBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Elearning\UserBundle\Entity\User;

class RegistrationEvent extends Event
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
