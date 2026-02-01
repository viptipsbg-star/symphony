<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExamAttempt
 */
class ExamAttempt
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
     * @var integer
     */
    private $exam_id;

    /**
     * @var integer
     */
    private $result;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $answers;

    /**
     * @var \Elearning\CoursesBundle\Entity\CourseListening
     */
    private $courseListen;

    /**
     * @var \Elearning\CoursesBundle\Entity\Exam
     */
    private $exam;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set listening_id
     *
     * @param integer $listeningId
     * @return ExamAttempt
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
     * Set exam_id
     *
     * @param integer $examId
     * @return ExamAttempt
     */
    public function setExamId($examId)
    {
        $this->exam_id = $examId;

        return $this;
    }

    /**
     * Get exam_id
     *
     * @return integer 
     */
    public function getExamId()
    {
        return $this->exam_id;
    }

    /**
     * Set result
     *
     * @param integer $result
     * @return ExamAttempt
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result
     *
     * @return integer 
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Add answers
     *
     * @param \Elearning\CoursesBundle\Entity\ExamAnswer $answers
     * @return ExamAttempt
     */
    public function addAnswer(\Elearning\CoursesBundle\Entity\ExamAnswer $answers)
    {
        $this->answers[] = $answers;

        return $this;
    }

    /**
     * Remove answers
     *
     * @param \Elearning\CoursesBundle\Entity\ExamAnswer $answers
     */
    public function removeAnswer(\Elearning\CoursesBundle\Entity\ExamAnswer $answers)
    {
        $this->answers->removeElement($answers);
    }

    /**
     * Get answers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Set courseListen
     *
     * @param \Elearning\CoursesBundle\Entity\CourseListening $courseListen
     * @return ExamAttempt
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

    /**
     * Set exam
     *
     * @param \Elearning\CoursesBundle\Entity\Exam $exam
     * @return ExamAttempt
     */
    public function setExam(\Elearning\CoursesBundle\Entity\Exam $exam = null)
    {
        $this->exam = $exam;

        return $this;
    }

    /**
     * Get exam
     *
     * @return \Elearning\CoursesBundle\Entity\Exam 
     */
    public function getExam()
    {
        return $this->exam;
    }
    /**
     * @var \DateTime
     */
    private $starttime;

    /**
     * @var \DateTime
     */
    private $endtime;


    /**
     * Set starttime
     *
     * @param \DateTime $starttime
     * @return ExamAttempt
     */
    public function setStarttime($starttime)
    {
        $this->starttime = $starttime;

        return $this;
    }

    /**
     * Get starttime
     *
     * @return \DateTime 
     */
    public function getStarttime()
    {
        return $this->starttime;
    }

    /**
     * Set endtime
     *
     * @param \DateTime $endtime
     * @return ExamAttempt
     */
    public function setEndtime($endtime)
    {
        $this->endtime = $endtime;

        return $this;
    }

    /**
     * Get endtime
     *
     * @return \DateTime 
     */
    public function getEndtime()
    {
        return $this->endtime;
    }
    /**
     * @var boolean
     */
    private $passed;


    /**
     * Set passed
     *
     * @param boolean $passed
     * @return ExamAttempt
     */
    public function setPassed($passed)
    {
        $this->passed = $passed;

        return $this;
    }

    /**
     * Get passed
     *
     * @return boolean 
     */
    public function getPassed()
    {
        return $this->passed;
    }

    public function getDistinctQuestions()
    {
        $distinctQuestions = array();
        foreach ($this->answers as $answer) {
            if (!isset($distinctQuestions[$answer->getQuestionId()])) {
                $distinctQuestions[$answer->getQuestionId()] = $answer;
            }
        }

        return $distinctQuestions;
    }
    /**
     * @var integer
     */
    private $spent_time;


    /**
     * Set spent_time
     *
     * @param integer $spentTime
     * @return ExamAttempt
     */
    public function setSpentTime($spentTime)
    {
        $this->spent_time = $spentTime;

        return $this;
    }

    /**
     * Get spent_time
     *
     * @return integer 
     */
    public function getSpentTime()
    {
        return $this->spent_time;
    }
}
