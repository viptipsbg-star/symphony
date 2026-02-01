<?php

namespace Elearning\CompaniesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * Group
 */
class Group
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \Elearning\CompaniesBundle\Entity\Company
     */
    private $company;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $employees;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $subjects;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->employees = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->title;
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
     * Set title
     *
     * @param string $title
     * @return Group
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Group
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set company
     *
     * @param \Elearning\CompaniesBundle\Entity\Company $company
     * @return Group
     */
    public function setCompany(\Elearning\CompaniesBundle\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \Elearning\CompaniesBundle\Entity\Company 
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Add employees
     *
     * @param \Elearning\CompaniesBundle\Entity\Employee $employees
     * @return Group
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
    
    /**
     * Add subject
     *
     * @param \Elearning\CoursesBundle\Entity\Subject $subjects
     * @return Group
     */
    public function addSubject(\Elearning\CoursesBundle\Entity\Subject $subject)
    {
        $this->subjects[] = $subject;

        return $this;
    }
    
    /**
     * Get subjects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSubjects()
    {
        return $this->subjects;
    }

    /**
     * Get active and sort subjects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActiveSubjects()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("active", 1))
            ->orderBy(array("lesson_date" => Criteria::ASC));

        return $this->subjects->matching($criteria);
    }
    
    /**
     * @var integer
     */
    private $company_id;


    /**
     * Set company_id
     *
     * @param integer $companyId
     * @return Group
     */
    public function setCompanyId($companyId)
    {
        $this->company_id = $companyId;

        return $this;
    }

    /**
     * Get company_id
     *
     * @return integer 
     */
    public function getCompanyId()
    {
        return $this->company_id;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupCourses;


    /**
     * Add groupCourses
     *
     * @param \Elearning\CoursesBundle\Entity\GroupCourse $groupCourses
     * @return Group
     */
    public function addGroupCourse(\Elearning\CoursesBundle\Entity\GroupCourse $groupCourses)
    {
        $this->groupCourses[] = $groupCourses;

        return $this;
    }

    /**
     * Remove groupCourses
     *
     * @param \Elearning\CoursesBundle\Entity\GroupCourse $groupCourses
     */
    public function removeGroupCourse(\Elearning\CoursesBundle\Entity\GroupCourse $groupCourses)
    {
        $this->groupCourses->removeElement($groupCourses);
    }

    /**
     * Get groupCourses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroupCourses()
    {
        return $this->groupCourses;
    }
    /**
     * @var string
     */
    private $state;


    /**
     * Set state
     *
     * @param string $state
     * @return Group
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }
}
