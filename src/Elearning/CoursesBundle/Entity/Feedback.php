<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feedback
 */
class Feedback
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
    private $data;

    /**
     * @var string
     */
    private $version = 'edit';

    /**
     * @var \Elearning\CoursesBundle\Entity\Chapter
     */
    private $chapter;


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
     * @return Feedback
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
     * Set data
     *
     * @param array $data
     * @return Feedback
     */
    public function setData($data)
    {
        $this->data = json_encode($data);

        return $this;
    }

    /**
     * Get data
     *
     * @return object
     */
    public function getData()
    {
        return json_decode($this->data);
    }

    /**
     * Set version
     *
     * @param string $version
     * @return Feedback
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
     * Set chapter
     *
     * @param \Elearning\CoursesBundle\Entity\Chapter $chapter
     * @return Feedback
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
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $answers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add answers
     *
     * @param \Elearning\CoursesBundle\Entity\FeedbackData $answers
     * @return Feedback
     */
    public function addAnswer(\Elearning\CoursesBundle\Entity\FeedbackData $answers)
    {
        $this->answers[] = $answers;

        return $this;
    }

    /**
     * Remove answers
     *
     * @param \Elearning\CoursesBundle\Entity\FeedbackData $answers
     */
    public function removeAnswer(\Elearning\CoursesBundle\Entity\FeedbackData $answers)
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
}
