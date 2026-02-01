<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedbackData
 */
class FeedbackData
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $feedback_id;

    /**
     * @var string
     */
    private $data;

    /**
     * @var \DateTime
     */
    private $submitdate;

    /**
     * @var \Elearning\CoursesBundle\Entity\Feedback
     */
    private $feedback;


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
     * Set feedback_id
     *
     * @param integer $feedbackId
     * @return FeedbackData
     */
    public function setFeedbackId($feedbackId)
    {
        $this->feedback_id = $feedbackId;

        return $this;
    }

    /**
     * Get feedback_id
     *
     * @return integer 
     */
    public function getFeedbackId()
    {
        return $this->feedback_id;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return FeedbackData
     */
    public function setData($data)
    {
        $this->data = json_encode($data);

        return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return json_decode($this->data, true);
    }

    /**
     * Set submitdate
     *
     * @param \DateTime $submitdate
     * @return FeedbackData
     */
    public function setSubmitdate($submitdate)
    {
        $this->submitdate = $submitdate;

        return $this;
    }

    /**
     * Get submitdate
     *
     * @return \DateTime 
     */
    public function getSubmitdate()
    {
        return $this->submitdate;
    }

    /**
     * Set feedback
     *
     * @param \Elearning\CoursesBundle\Entity\Feedback $feedback
     * @return FeedbackData
     */
    public function setFeedback(\Elearning\CoursesBundle\Entity\Feedback $feedback = null)
    {
        $this->feedback = $feedback;

        return $this;
    }

    /**
     * Get feedback
     *
     * @return \Elearning\CoursesBundle\Entity\Feedback 
     */
    public function getFeedback()
    {
        return $this->feedback;
    }
    /**
     * @var integer
     */
    private $listen_id;

    /**
     * @var \Elearning\CoursesBundle\Entity\CourseListening
     */
    private $courseListen;


    /**
     * Set listen_id
     *
     * @param integer $listenId
     * @return FeedbackData
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
     * Set courseListen
     *
     * @param \Elearning\CoursesBundle\Entity\CourseListening $courseListen
     * @return FeedbackData
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
