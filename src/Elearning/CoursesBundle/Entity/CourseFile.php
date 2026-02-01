<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CourseFile
 */
class CourseFile
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $chapter_id;

    /**
     * @var integer
     */
    private $duration;

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
     * Set location
     *
     * @param string $location
     * @return CourseFile
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return CourseFile
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set chapter_id
     *
     * @param integer $chapterId
     * @return CourseFile
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
     * Set duration
     *
     * @param integer $duration
     * @return CourseFile
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return integer 
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set chapter
     *
     * @param \Elearning\CoursesBundle\Entity\Chapter $chapter
     * @return CourseFile
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


    public function getAbsolutePath() {
        return null === $this->location
            ? null
            : $this->getRootDir().$this->location;
    }

    protected function getRootDir() {
        return __DIR__."/../../../";
    }

}
