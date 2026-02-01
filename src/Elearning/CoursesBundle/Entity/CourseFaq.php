<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CourseFaq
 */
class CourseFaq
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
     * @var \Elearning\CoursesBundle\Entity\Course
     */
    private $course;


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
     * @return CourseFaq
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
     * Set question
     *
     * @param string $question
     * @return CourseFaq
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
     * @return CourseFaq
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
     * @return CourseFaq
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
     * Set course
     *
     * @param \Elearning\CoursesBundle\Entity\Course $course
     * @return CourseFaq
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
}
