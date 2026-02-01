<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExamAttemptAnswer
 */
class ExamAttemptAnswer
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $attempt_id;

    /**
     * @var integer
     */
    private $answer_id;

    /**
     * @var \Elearning\CoursesBundle\Entity\ExamAttempt
     */
    private $attempt;

    /**
     * @var \Elearning\CoursesBundle\Entity\ExamAnswer
     */
    private $answer;


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
     * Set attempt_id
     *
     * @param integer $attemptId
     * @return ExamAttemptAnswer
     */
    public function setAttemptId($attemptId)
    {
        $this->attempt_id = $attemptId;

        return $this;
    }

    /**
     * Get attempt_id
     *
     * @return integer 
     */
    public function getAttemptId()
    {
        return $this->attempt_id;
    }

    /**
     * Set answer_id
     *
     * @param integer $answerId
     * @return ExamAttemptAnswer
     */
    public function setAnswerId($answerId)
    {
        $this->answer_id = $answerId;

        return $this;
    }

    /**
     * Get answer_id
     *
     * @return integer 
     */
    public function getAnswerId()
    {
        return $this->answer_id;
    }

    /**
     * Set attempt
     *
     * @param \Elearning\CoursesBundle\Entity\ExamAttempt $attempt
     * @return ExamAttemptAnswer
     */
    public function setAttempt(\Elearning\CoursesBundle\Entity\ExamAttempt $attempt = null)
    {
        $this->attempt = $attempt;

        return $this;
    }

    /**
     * Get attempt
     *
     * @return \Elearning\CoursesBundle\Entity\ExamAttempt 
     */
    public function getAttempt()
    {
        return $this->attempt;
    }

    /**
     * Set answer
     *
     * @param \Elearning\CoursesBundle\Entity\ExamAnswer $answer
     * @return ExamAttemptAnswer
     */
    public function setAnswer(\Elearning\CoursesBundle\Entity\ExamAnswer $answer = null)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer
     *
     * @return \Elearning\CoursesBundle\Entity\ExamAnswer 
     */
    public function getAnswer()
    {
        return $this->answer;
    }
    /**
     * @var integer
     */
    private $question_id;

    /**
     * @var \Elearning\CoursesBundle\Entity\ExamQuestion
     */
    private $question;


    /**
     * Set question_id
     *
     * @param integer $questionId
     * @return ExamAttemptAnswer
     */
    public function setQuestionId($questionId)
    {
        $this->question_id = $questionId;

        return $this;
    }

    /**
     * Get question_id
     *
     * @return integer 
     */
    public function getQuestionId()
    {
        return $this->question_id;
    }

    /**
     * Set question
     *
     * @param \Elearning\CoursesBundle\Entity\ExamQuestion $question
     * @return ExamAttemptAnswer
     */
    public function setQuestion(\Elearning\CoursesBundle\Entity\ExamQuestion $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return \Elearning\CoursesBundle\Entity\ExamQuestion 
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
