<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupCourse
 */
class GroupCourse
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $course_id;

    /**
     * @var integer
     */
    private $group_id;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \Elearning\CoursesBundle\Entity\Course
     */
    private $course;

    /**
     * @var \Elearning\CompaniesBundle\Entity\Group
     */
    private $group;


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
     * Set course_id
     *
     * @param integer $courseId
     * @return GroupCourse
     */
    public function setCourseId($courseId)
    {
        $this->course_id = $courseId;

        return $this;
    }

    /**
     * Get course_id
     *
     * @return integer 
     */
    public function getCourseId()
    {
        return $this->course_id;
    }

    /**
     * Set group_id
     *
     * @param integer $groupId
     * @return GroupCourse
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
     * Set created
     *
     * @param \DateTime $created
     * @return GroupCourse
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
     * Set course
     *
     * @param \Elearning\CoursesBundle\Entity\Course $course
     * @return GroupCourse
     */
    public function setCourse(\Elearning\CoursesBundle\Entity\Course $course = null)
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get course
     *
     * @return \Elearning\CoursesBundle\Entity\Course 
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * Set group
     *
     * @param \Elearning\CompaniesBundle\Entity\Group $group
     * @return GroupCourse
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
     * @var boolean
     */
    private $active = true;


    /**
     * Set active
     *
     * @param boolean $active
     * @return GroupCourse
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
}
