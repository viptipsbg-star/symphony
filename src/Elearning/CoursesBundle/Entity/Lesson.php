<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Lesson
 */
class Lesson
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $content;

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
     * Set content
     *
     * @param string $content
     * @return Lesson
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set chapter
     *
     * @param \Elearning\CoursesBundle\Entity\Chapter $chapter
     * @return Lesson
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
     * @var integer
     */
    private $chapter_id;


    /**
     * Set chapter_id
     *
     * @param integer $chapterId
     * @return Lesson
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
     * @var string
     */
    private $version = 'edit';


    /**
     * Set version
     *
     * @param string $version
     * @return Lesson
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
}
