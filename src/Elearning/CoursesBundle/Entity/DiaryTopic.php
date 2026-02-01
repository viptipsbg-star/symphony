<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DiaryTopic
 */
class DiaryTopic
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $topic_id;

    /**
     * @var \DateTime
     */
    private $issued;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var \Elearning\CoursesBundle\Entity\Topic
     */
    private $topic;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->active = true;
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
     * Set topic_id
     *
     * @param integer $topicId
     * @return DiaryTopic
     */
    public function setTopicId($topicId)
    {
        $this->topic_id = $topicId;

        return $this;
    }

    /**
     * Get topic_id
     *
     * @return integer 
     */
    public function getTopicId()
    {
        return $this->topic_id;
    }

    /**
     * Set issued
     *
     * @param \DateTime $issued
     * @return DiaryTopic
     */
    public function setIssued($issued)
    {
        $this->issued = $issued;

        return $this;
    }

    /**
     * Get issued
     *
     * @return \DateTime 
     */
    public function getIssued()
    {
        return $this->issued;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return DiaryTopic
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return DiaryTopic
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
     * Set topic
     *
     * @param \Elearning\CoursesBundle\Entity\Topic $topic
     * @return DiaryTopic
     */
    public function setTopic(\Elearning\CoursesBundle\Entity\Topic $topic = null)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     *
     * @return \Elearning\CoursesBundle\Entity\Topic 
     */
    public function getTopic()
    {
        return $this->topic;
    }
    /**
     * @var integer
     */
    private $employee_id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $rates;

    /**
     * @var \Elearning\CompaniesBundle\Entity\Employee
     */
    private $employee;


    /**
     * Set employee_id
     *
     * @param integer $employeeId
     * @return DiaryTopic
     */
    public function setEmployeeId($employeeId)
    {
        $this->employee_id = $employeeId;

        return $this;
    }

    /**
     * Get employee_id
     *
     * @return integer 
     */
    public function getEmployeeId()
    {
        return $this->employee_id;
    }

    /**
     * Add rates
     *
     * @param \Elearning\CoursesBundle\Entity\DiaryRate $rates
     * @return DiaryTopic
     */
    public function addRate(\Elearning\CoursesBundle\Entity\DiaryRate $rates)
    {
        $this->rates[] = $rates;

        return $this;
    }

    /**
     * Remove rates
     *
     * @param \Elearning\CoursesBundle\Entity\DiaryRate $rates
     */
    public function removeRate(\Elearning\CoursesBundle\Entity\DiaryRate $rates)
    {
        $this->rates->removeElement($rates);
    }

    /**
     * Get rates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * Set employee
     *
     * @param \Elearning\CompaniesBundle\Entity\Employee $employee
     * @return DiaryTopic
     */
    public function setEmployee(\Elearning\CompaniesBundle\Entity\Employee $employee = null)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get employee
     *
     * @return \Elearning\CompaniesBundle\Entity\Employee 
     */
    public function getEmployee()
    {
        return $this->employee;
    }
    /**
     * @var integer
     */
    private $creator_employee_id;


    /**
     * Set creator_employee_id
     *
     * @param integer $creatorEmployeeId
     * @return DiaryTopic
     */
    public function setCreatorEmployeeId($creatorEmployeeId)
    {
        $this->creator_employee_id = $creatorEmployeeId;

        return $this;
    }

    /**
     * Get creator_employee_id
     *
     * @return integer 
     */
    public function getCreatorEmployeeId()
    {
        return $this->creator_employee_id;
    }
    /**
     * @var \Elearning\CompaniesBundle\Entity\Employee
     */
    private $creator_employee;


    /**
     * Set creator_employee
     *
     * @param \Elearning\CompaniesBundle\Entity\Employee $creatorEmployee
     * @return DiaryTopic
     */
    public function setCreatorEmployee(\Elearning\CompaniesBundle\Entity\Employee $creatorEmployee = null)
    {
        $this->creator_employee = $creatorEmployee;

        return $this;
    }

    /**
     * Get creator_employee
     *
     * @return \Elearning\CompaniesBundle\Entity\Employee 
     */
    public function getCreatorEmployee()
    {
        return $this->creator_employee;
    }
}
