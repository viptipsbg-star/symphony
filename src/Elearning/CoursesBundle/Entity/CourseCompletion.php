<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CourseCompletion
 */
class CourseCompletion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $listen_id;

    /**
     * @var integer
     */
    private $chapter_id;

    /**
     * @var boolean
     */
    private $completed;

    /**
     * @var integer
     */
    private $completion;

    /**
     * @var \Elearning\CoursesBundle\Entity\CourseListening
     */
    private $listen;

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
     * Set listen_id
     *
     * @param integer $listenId
     * @return CourseCompletion
     */
    public function setListenId($listenId)
    {
        $this->listen_id = $listenId;

        return $this;
    }

    /**
     * Get listen_id
     *
     * @return integer 
     */
    public function getListenId()
    {
        return $this->listen_id;
    }

    /**
     * Set chapter_id
     *
     * @param integer $chapterId
     * @return CourseCompletion
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
     * Set completed
     *
     * @param boolean $completed
     * @return CourseCompletion
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
     * Set completion
     *
     * @param integer $completion
     * @return CourseCompletion
     */
    public function setCompletion($completion)
    {
        $this->completion = $completion;

        return $this;
    }

    /**
     * Get completion
     *
     * @return integer 
     */
    public function getCompletion()
    {
        return $this->completion;
    }

    /**
     * Set listen
     *
     * @param \Elearning\CoursesBundle\Entity\CourseListening $listen
     * @return CourseCompletion
     */
    public function setListen(\Elearning\CoursesBundle\Entity\CourseListening $listen = null)
    {
        $this->listen = $listen;

        return $this;
    }

    /**
     * Get listen
     *
     * @return \Elearning\CoursesBundle\Entity\CourseListening 
     */
    public function getListen()
    {
        return $this->listen;
    }

    /**
     * Set chapter
     *
     * @param \Elearning\CoursesBundle\Entity\Chapter $chapter
     * @return CourseCompletion
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
     * @var \DateTime
     */
    private $updatetime;


    /**
     * Set updatetime
     *
     * @param \DateTime $updatetime
     * @return CourseCompletion
     */
    public function setUpdatetime($updatetime)
    {
        $this->updatetime = $updatetime;

        return $this;
    }

    /**
     * Get updatetime
     *
     * @return \DateTime 
     */
    public function getUpdatetime()
    {
        return $this->updatetime;
    }
    /**
     * @var \Elearning\CoursesBundle\Entity\CourseListening
     */
    private $courseListen;


    /**
     * Set courseListen
     *
     * @param \Elearning\CoursesBundle\Entity\CourseListening $courseListen
     * @return CourseCompletion
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
}
