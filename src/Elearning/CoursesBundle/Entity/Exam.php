<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Exam
 */
class Exam
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $chapter_id;

    /**
     * @var string
     */
    private $options;

    /**
     * @var string
     */
    private $version = 'edit';

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $questions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $attempts;

    /**
     * @var \Elearning\CoursesBundle\Entity\Chapter
     */
    private $chapter;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attempts = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set chapter_id
     *
     * @param integer $chapterId
     * @return Exam
     */
    public function setChapterId($chapterId)
    {
        $this->chapter_id = $chapterId;

        return $this;
    }

    /**
     * Get chapter_id
     *
     * @return integer 
     */
    public function getChapterId()
    {
        return $this->chapter_id;
    }

    /**
     * Set options
     *
     * @param string $options
     * @return Exam
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options
     *
     * @return object
     */
    public function getOptions()
    {
        return json_decode($this->options);
    }

    /**
     * Set version
     *
     * @param string $version
     * @return Exam
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Add questions
     *
     * @param \Elearning\CoursesBundle\Entity\ExamQuestion $questions
     * @return Exam
     */
    public function addQuestion(\Elearning\CoursesBundle\Entity\ExamQuestion $questions)
    {
        $this->questions[] = $questions;

        return $this;
    }

    /**
     * Remove questions
     *
     * @param \Elearning\CoursesBundle\Entity\ExamQuestion $questions
     */
    public function removeQuestion(\Elearning\CoursesBundle\Entity\ExamQuestion $questions)
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
     * Add attempts
     *
     * @param \Elearning\CoursesBundle\Entity\ExamAttempt $attempts
     * @return Exam
     */
    public function addAttempt(\Elearning\CoursesBundle\Entity\ExamAttempt $attempts)
    {
        $this->attempts[] = $attempts;

        return $this;
    }

    /**
     * Remove attempts
     *
     * @param \Elearning\CoursesBundle\Entity\ExamAttempt $attempts
     */
    public function removeAttempt(\Elearning\CoursesBundle\Entity\ExamAttempt $attempts)
    {
        $this->attempts->removeElement($attempts);
    }

    /**
     * Get attempts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * Set chapter
     *
     * @param \Elearning\CoursesBundle\Entity\Chapter $chapter
     * @return Exam
     */
    public function setChapter(\Elearning\CoursesBundle\Entity\Chapter $chapter = null)
    {
        $this->chapter = $chapter;

        return $this;
    }

    /**
     * Get chapter
     *
     * @return \Elearning\CoursesBundle\Entity\Chapter 
     */
    public function getChapter()
    {
        return $this->chapter;
    }
}
