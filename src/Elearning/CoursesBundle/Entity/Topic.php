<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Topic
 */
class Topic
{
    /**
     * @var integer
     */
    private $id;
    
    /**
     * @var string
     */
    private $text;

    /**
     * @var boolean
     */
    private $active;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->active = true;
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
     * Set text
     *
     * @param string $text
     * @return Topic
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Topic
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $diaryTopics;


    /**
     * Add diaryTopics
     *
     * @param \Elearning\CoursesBundle\Entity\DiaryTopic $diaryTopics
     * @return Topic
     */
    public function addDiaryTopic(\Elearning\CoursesBundle\Entity\DiaryTopic $diaryTopics)
    {
        $this->diaryTopics[] = $diaryTopics;

        return $this;
    }

    /**
     * Remove diaryTopics
     *
     * @param \Elearning\CoursesBundle\Entity\DiaryTopic $diaryTopics
     */
    public function removeDiaryTopic(\Elearning\CoursesBundle\Entity\DiaryTopic $diaryTopics)
    {
        $this->diaryTopics->removeElement($diaryTopics);
    }

    /**
     * Get diaryTopics
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDiaryTopics()
    {
        return $this->diaryTopics;
    }
}
