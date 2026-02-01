<?php

namespace Elearning\CompaniesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Company
 */
class Company
{
    /**
     * @var integer
     */
    private $id;


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
     * @var string
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $employees;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->employees = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Company
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add employees
     *
     * @param \Elearning\CompaniesBundle\Entity\Employee $employees
     * @return Company
     */
    public function addEmployee(\Elearning\CompaniesBundle\Entity\Employee $employees)
    {
        $this->employees[] = $employees;

        return $this;
    }

    /**
     * Remove employees
     *
     * @param \Elearning\CompaniesBundle\Entity\Employee $employees
     */
    public function removeEmployee(\Elearning\CompaniesBundle\Entity\Employee $employees)
    {
        $this->employees->removeElement($employees);
    }

    /**
     * Get employees
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEmployees()
    {
        return $this->employees;
    }

    public function __toString()
    {
        return $this->name;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groups;


    /**
     * Add groups
     *
     * @param \Elearning\CompaniesBundle\Entity\Group $groups
     * @return Company
     */
    public function addGroup(\Elearning\CompaniesBundle\Entity\Group $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Elearning\CompaniesBundle\Entity\Group $groups
     */
    public function removeGroup(\Elearning\CompaniesBundle\Entity\Group $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }
}
