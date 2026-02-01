<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExamQuestion
 */
class ExamQuestion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $exam_id;

    /**
     * @var string
     */
    private $question;

    /**
     * @var integer
     */
    private $ordering;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $answers;

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
     * Set exam_id
     *
     * @param integer $examId
     * @return ExamQuestion
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
     * Set question
     *
     * @param string $question
     * @return ExamQuestion
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
     * Set ordering
     *
     * @param integer $ordering
     * @return ExamQuestion
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
     * Add answers
     *
     * @param \Elearning\CoursesBundle\Entity\ExamAnswer $answers
     * @return ExamQuestion
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
     * Set exam
     *
     * @param \Elearning\CoursesBundle\Entity\Exam $exam
     * @return ExamQuestion
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


    /*
     * Returns does the question contain multiple correct answers
     *
     * @return boolean
     */
    public function isMultipleCorrect() {
        $correctcount = 0;
        foreach ($this->getAnswers() as $answer) {
            if ($answer->getCorrect()) {
                $correctcount++;
            }
        }
        return $correctcount > 1;
    }
}
