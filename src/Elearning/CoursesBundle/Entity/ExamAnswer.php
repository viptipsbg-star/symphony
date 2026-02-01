<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExamAnswer
 */
class ExamAnswer
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $question_id;

    /**
     * @var string
     */
    private $answer;

    /**
     * @var boolean
     */
    private $correct;

    /**
     * @var integer
     */
    private $ordering;

    /**
     * @var \Elearning\CoursesBundle\Entity\ExamQuestion
     */
    private $question;


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
     * Set question_id
     *
     * @param integer $questionId
     * @return ExamAnswer
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
     * Set answer
     *
     * @param string $answer
     * @return ExamAnswer
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
     * Set correct
     *
     * @param boolean $correct
     * @return ExamAnswer
     */
    public function setCorrect($correct)
    {
        $this->correct = $correct;

        return $this;
    }

    /**
     * Get correct
     *
     * @return boolean 
     */
    public function getCorrect()
    {
        return $this->correct;
    }

    /**
     * Set ordering
     *
     * @param integer $ordering
     * @return ExamAnswer
     */
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;

        return $this;
    }

    /**
     * Get ordering
     *
     * @return integer 
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * Set question
     *
     * @param \Elearning\CoursesBundle\Entity\ExamQuestion $question
     * @return ExamAnswer
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
