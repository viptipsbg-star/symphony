<?php

namespace Elearning\CompaniesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * Administrators
 */
class Administrator
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $user_id;

    /**
     * @var \Elearning\UserBundle\Entity\User
     */
    private $user;
    

    /**
     * @var integer
     */
    private $lft;

    /**
     * @var integer
     */
    private $rgt;

    /**
     * @var integer
     */
    private $lvl;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var \Elearning\CompaniesBundle\Entity\Administrator
     */
    private $root;

    /**
     * @var \Elearning\CompaniesBundle\Entity\Administrator
     */
    private $parent;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString(){
        $username = $this->user->getUsername();
        $level = $this->lvl;
        $adminName = "($username) A$level";
        $employee = $this->user->getEmployee();
        if ($employee) {
            $name = $employee->getFieldValue('name');
            $surname = $employee->getFieldValue('surname');
            $adminName = "$name $surname $adminName";
        }
        return $adminName;
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
     * Set user_id
     *
     * @param integer $userId
     * @return Administrator
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     * @return Administrator
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer 
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     * @return Administrator
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer 
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     * @return Administrator
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer 
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set user
     *
     * @param \Elearning\UserBundle\Entity\User $user
     * @return Administrator
     */
    public function setUser(\Elearning\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Elearning\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add children
     *
     * @param \Elearning\CompaniesBundle\Entity\Administrator $children
     * @return Administrator
     */
    public function addChild(\Elearning\CompaniesBundle\Entity\Administrator $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Elearning\CompaniesBundle\Entity\Administrator $children
     */
    public function removeChild(\Elearning\CompaniesBundle\Entity\Administrator $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set root
     *
     * @param \Elearning\CompaniesBundle\Entity\Administrator $root
     * @return Administrator
     */
    public function setRoot(\Elearning\CompaniesBundle\Entity\Administrator $root = null)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     *
     * @return \Elearning\CompaniesBundle\Entity\Administrator 
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set parent
     *
     * @param \Elearning\CompaniesBundle\Entity\Administrator $parent
     * @return Administrator
     */
    public function setParent(\Elearning\CompaniesBundle\Entity\Administrator $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Elearning\CompaniesBundle\Entity\Administrator 
     */
    public function getParent()
    {
        return $this->parent;
    }
}
