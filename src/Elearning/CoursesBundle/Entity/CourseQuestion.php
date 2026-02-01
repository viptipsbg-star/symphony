<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CourseQuestion
 */
class CourseQuestion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $listening_id;

    /**
     * @var string
     */
    private $question;

    /**
     * @var string
     */
    private $answer;

    /**
     * @var \DateTime
     */
    private $createdtime;

    /**
     * @var \DateTime
     */
    private $answeredtime;

    /**
     * @var \Elearning\CoursesBundle\Entity\CourseListening
     */
    private $courseListening;


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
     * Set listening_id
     *
     * @param integer $listeningId
     * @return CourseQuestion
     */
    public function setListeningId($listeningId)
    {
        $this->listening_id = $listeningId;

        return $this;
    }

    /**
     * Get listening_id
     *
     * @return integer 
     */
    public function getListeningId()
    {
        return $this->listening_id;
    }

    /**
     * Set question
     *
     * @param string $question
     * @return CourseQuestion
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return string 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set answer
     *
     * @param string $answer
     * @return CourseQuestion
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer
     *
     * @return string 
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set createdtime
     *
     * @param \DateTime $createdtime
     * @return CourseQuestion
     */
    public function setCreatedtime($createdtime)
    {
        $this->createdtime = $createdtime;

        return $this;
    }

    /**
     * Get createdtime
     *
     * @return \DateTime 
     */
    public function getCreatedtime()
    {
        return $this->createdtime;
    }

    /**
     * Set answeredtime
     *
     * @param \DateTime $answeredtime
     * @return CourseQuestion
     */
    public function setAnsweredtime($answeredtime)
    {
        $this->answeredtime = $answeredtime;

        return $this;
    }

    /**
     * Get answeredtime
     *
     * @return \DateTime 
     */
    public function getAnsweredtime()
    {
        return $this->answeredtime;
    }

    /**
     * Set courseListening
     *
     * @param \Elearning\CoursesBundle\Entity\CourseListening $courseListening
     * @return CourseQuestion
     */
    public function setCourseListening(\Elearning\CoursesBundle\Entity\CourseListening $courseListening = null)
    {
        $this->courseListening = $courseListening;

        return $this;
    }

    /**
     * Get courseListening
     *
     * @return \Elearning\CoursesBundle\Entity\CourseListening 
     */
    public function getCourseListening()
    {
        return $this->courseListening;
    }
    /**
     * @var boolean
     */
    private $viewed;


    /**
     * Set viewed
     *
     * @param boolean $viewed
     * @return CourseQuestion
     */
    public function setViewed($viewed)
    {
        $this->viewed = $viewed;

        return $this;
    }

    /**
     * Get viewed
     *
     * @return boolean 
     */
    public function getViewed()
    {
        return $this->viewed;
    }
}
