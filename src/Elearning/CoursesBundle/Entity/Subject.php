<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subject
 */
class Subject
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $group_id;

    /**
     * @var \DateTime
     */
    private $lesson_date;

    /**
     * @var string
     */
    private $description;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var \Elearning\CompaniesBundle\Entity\Group
     */
    private $group;

    /**
     * @var \Elearning\CoursesBundle\Entity\EmployeeSubject
     */
    private $employeeSubject;

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
     * Set group_id
     *
     * @param integer $groupId
     * @return Subject
     */
    public function setGroupId($groupId)
    {
        $this->group_id = $groupId;

        return $this;
    }

    /**
     * Get group_id
     *
     * @return integer 
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * Set descruption
     *
     * @param string $description
     * @return Subject
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get descruption
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set lesson_date
     *
     * @param \DateTime $lessonDate
     * @return Subject
     */
    public function setLessonDate($lessonDate)
    {
        $this->lesson_date = $lessonDate;

        return $this;
    }

    /**
     * Get lesson_date
     *
     * @return \DateTime 
     */
    public function getLessonDate()
    {
        return $this->lesson_date;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Subject
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set group
     *
     * @param \Elearning\CompaniesBundle\Entity\Group $group
     * @return Subject
     */
    public function setGroup(\Elearning\CompaniesBundle\Entity\Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \Elearning\CompaniesBundle\Entity\Group 
     */
    public function getGroup()
    {
        return $this->group;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->employeeSubject = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add employeeSubject
     *
     * @param \Elearning\CoursesBundle\Entity\EmployeeSubject $employeeSubject
     * @return Subject
     */
    public function addEmployeeSubject(\Elearning\CoursesBundle\Entity\EmployeeSubject $employeeSubject)
    {
        $this->employeeSubject[] = $employeeSubject;

        return $this;
    }

    /**
     * Remove employeeSubject
     *
     * @param \Elearning\CoursesBundle\Entity\EmployeeSubject $employeeSubject
     */
    public function removeEmployeeSubject(\Elearning\CoursesBundle\Entity\EmployeeSubject $employeeSubject)
    {
        $this->employeeSubject->removeElement($employeeSubject);
    }

    /**
     * Get employeeSubject
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEmployeeSubject()
    {
        return $this->employeeSubject;
    }
}
