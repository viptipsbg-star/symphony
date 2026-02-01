<?php

namespace Elearning\UserBundle\Entity;

use Sonata\UserBundle\Entity\BaseGroup as BaseGroup;

/**
 * Group
 */
class Group extends BaseGroup
{

    /**
     * @var integer
     */
     protected $id;


    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add users
     *
     * @param \Elearning\UserBundle\Entity\User $users
     * @return Group
     */
    public function addUser(\Elearning\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Elearning\UserBundle\Entity\User $users
     */
    public function removeUser(\Elearning\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}
