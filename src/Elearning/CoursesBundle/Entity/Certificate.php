<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Certificate
 */
class Certificate
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $listen_id;

    /**
     * @var \DateTime
     */
    private $issue_date;

    /**
     * @var string
     */
    private $code;

    /**
     * @var \Elearning\CoursesBundle\Entity\CourseListening
     */
    private $courseListen;


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
     * Set listen_id
     *
     * @param integer $listenId
     * @return Certificate
     */
    public function setListenId($listenId)
    {
        $this->listen_id = $listenId;

        return $this;
    }

    /**
     * Get listen_id
     *
     * @return integer 
     */
    public function getListenId()
    {
        return $this->listen_id;
    }

    /**
     * Set issue_date
     *
     * @param \DateTime $issueDate
     * @return Certificate
     */
    public function setIssueDate($issueDate)
    {
        $this->issue_date = $issueDate;

        return $this;
    }

    /**
     * Get issue_date
     *
     * @return \DateTime 
     */
    public function getIssueDate()
    {
        return $this->issue_date;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Certificate
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set courseListen
     *
     * @param \Elearning\CoursesBundle\Entity\CourseListening $courseListen
     * @return Certificate
     */
    public function setCourseListen(\Elearning\CoursesBundle\Entity\CourseListening $courseListen = null)
    {
        $this->courseListen = $courseListen;

        return $this;
    }

    /**
     * Get courseListen
     *
     * @return \Elearning\CoursesBundle\Entity\CourseListening 
     */
    public function getCourseListen()
    {
        return $this->courseListen;
    }
}
