<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CourseListening
 */
class CourseListening
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
     * @var integer
     */
    private $course_id;

    /**
     * @var integer
     */
    private $user_id;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $started;

    /**
     * @var \DateTime
     */
    private $last_listen;

    /**
     * @var \Elearning\CoursesBundle\Entity\Course
     */
    private $course;

    /**
     * @var \Elearning\UserBundle\Entity\CoursesBundle
     */
    private $user;


    /**
     * Set course_id
     *
     * @param integer $courseId
     * @return CourseListening
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
     * Set user_id
     *
     * @param integer $userId
     * @return CourseListening
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
     * Set created
     *
     * @param \DateTime $created
     * @return CourseListening
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
     * Set started
     *
     * @param \DateTime $started
     * @return CourseListening
     */
    public function setStarted($started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * Get started
     *
     * @return \DateTime 
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * Set last_listen
     *
     * @param \DateTime $lastListen
     * @return CourseListening
     */
    public function setLastListen($lastListen)
    {
        $this->last_listen = $lastListen;

        return $this;
    }

    /**
     * Get last_listen
     *
     * @return \DateTime 
     */
    public function getLastListen()
    {
        return $this->last_listen;
    }

    /**
     * Set course
     *
     * @param \Elearning\CoursesBundle\Entity\Course $course
     * @return CourseListening
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
     * Set user
     *
     * @param \Elearning\UserBundle\Entity\CoursesBundle $user
     * @return CourseListening
     */
    public function setUser(\Elearning\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Elearning\UserBundle\Entity\CoursesBundle 
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @var integer
     */
    private $group_course_id;

    /**
     * @var \Elearning\CoursesBundle\Entity\GroupCourse
     */
    private $groupCourse;


    /**
     * Set group_course_id
     *
     * @param integer $groupCourseId
     * @return CourseListening
     */
    public function setGroupCourseId($groupCourseId)
    {
        $this->group_course_id = $groupCourseId;

        return $this;
    }

    /**
     * Get group_course_id
     *
     * @return integer 
     */
    public function getGroupCourseId()
    {
        return $this->group_course_id;
    }

    /**
     * Set groupCourse
     *
     * @param \Elearning\CoursesBundle\Entity\GroupCourse $groupCourse
     * @return CourseListening
     */
    public function setGroupCourse(\Elearning\CoursesBundle\Entity\GroupCourse $groupCourse = null)
    {
        $this->groupCourse = $groupCourse;

        return $this;
    }

    /**
     * Get groupCourse
     *
     * @return \Elearning\CoursesBundle\Entity\GroupCourse 
     */
    public function getGroupCourse()
    {
        return $this->groupCourse;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $completion;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->completion = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add completion
     *
     * @param \Elearning\CoursesBundle\Entity\CourseCompletion $completion
     * @return CourseListening
     */
    public function addCompletion(\Elearning\CoursesBundle\Entity\CourseCompletion $completion)
    {
        $this->completion[] = $completion;

        return $this;
    }

    /**
     * Remove completion
     *
     * @param \Elearning\CoursesBundle\Entity\CourseCompletion $completion
     */
    public function removeCompletion(\Elearning\CoursesBundle\Entity\CourseCompletion $completion)
    {
        $this->completion->removeElement($completion);
    }

    /**
     * Get completion
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCompletion()
    {
        return $this->completion;
    }
    /**
     * @var boolean
     */
    private $completed = false;


    /**
     * Set completed
     *
     * @param boolean $completed
     * @return CourseListening
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get completed
     *
     * @return boolean 
     */
    public function getCompleted()
    {
        return $this->completed;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $questions;


    /**
     * Add questions
     *
     * @param \Elearning\CoursesBundle\Entity\CourseQuestion $questions
     * @return CourseListening
     */
    public function addQuestion(\Elearning\CoursesBundle\Entity\CourseQuestion $questions)
    {
        $this->questions[] = $questions;

        return $this;
    }

    /**
     * Remove questions
     *
     * @param \Elearning\CoursesBundle\Entity\CourseQuestion $questions
     */
    public function removeQuestion(\Elearning\CoursesBundle\Entity\CourseQuestion $questions)
    {
        $this->questions->removeElement($questions);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestions()
    {
        return $this->questions;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $examAttempts;


    /**
     * Add examAttempts
     *
     * @param \Elearning\CoursesBundle\Entity\ExamAttempt $examAttempts
     * @return CourseListening
     */
    public function addExamAttempt(\Elearning\CoursesBundle\Entity\ExamAttempt $examAttempts)
    {
        $this->examAttempts[] = $examAttempts;

        return $this;
    }

    /**
     * Remove examAttempts
     *
     * @param \Elearning\CoursesBundle\Entity\ExamAttempt $examAttempts
     */
    public function removeExamAttempt(\Elearning\CoursesBundle\Entity\ExamAttempt $examAttempts)
    {
        $this->examAttempts->removeElement($examAttempts);
    }

    /**
     * Get examAttempts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getExamAttempts()
    {
        return $this->examAttempts;
    }


    public function isCoursePassed() {
        if (!$this->getCompleted()) {
            return false;
        }
        $exampassed = false;
        foreach ($this->getExamAttempts() as $attempt) {
            if ($attempt->getPassed()) {
                $exampassed = true;
            }
        }
        return ($exampassed || count($this->getExamAttempts()) == 0);
    }
    /**
     * @var \Elearning\CoursesBundle\Entity\Certificate
     */
    private $certificate;


    /**
     * Set certificate
     *
     * @param \Elearning\CoursesBundle\Entity\Certificate $certificate
     * @return CourseListening
     */
    public function setCertificate(\Elearning\CoursesBundle\Entity\Certificate $certificate = null)
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     * Get certificate
     *
     * @return \Elearning\CoursesBundle\Entity\Certificate 
     */
    public function getCertificate()
    {
        return $this->certificate;
    }
}
