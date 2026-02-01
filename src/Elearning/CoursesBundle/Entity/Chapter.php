<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chapter
 */
class Chapter
{
    const TYPE_EXAM = 'exam';

    const STATE_PUBLISHED = 'published';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $course_id;

    /**
     * @var integer
     */
    private $ordering;

    /**
     * @var integer
     */
    private $process_id;

    /**
     * @var integer
     */
    private $progress;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $coursefiles;

    /**
     * @var \Elearning\CoursesBundle\Entity\Course
     */
    private $course;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->coursefiles = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Chapter
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Chapter
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
     * Set course_id
     *
     * @param integer $courseId
     * @return Chapter
     */
    public function setCourseId($courseId)
    {
        $this->course_id = $courseId;

        return $this;
    }

    /**
     * Get course_id
     *
     * @return integer 
     */
    public function getCourseId()
    {
        return $this->course_id;
    }

    /**
     * Set ordering
     *
     * @param integer $ordering
     * @return Chapter
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
     * Set process_id
     *
     * @param integer $processId
     * @return Chapter
     */
    public function setProcessId($processId)
    {
        $this->process_id = $processId;

        return $this;
    }

    /**
     * Get process_id
     *
     * @return integer 
     */
    public function getProcessId()
    {
        return $this->process_id;
    }

    /**
     * Set progress
     *
     * @param integer $progress
     * @return Chapter
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get progress
     *
     * @return integer 
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Add coursefiles
     *
     * @param \Elearning\CoursesBundle\Entity\CourseFile $coursefiles
     * @return Chapter
     */
    public function addCoursefile(\Elearning\CoursesBundle\Entity\CourseFile $coursefiles)
    {
        $this->coursefiles[] = $coursefiles;

        return $this;
    }

    /**
     * Remove coursefiles
     *
     * @param \Elearning\CoursesBundle\Entity\CourseFile $coursefiles
     */
    public function removeCoursefile(\Elearning\CoursesBundle\Entity\CourseFile $coursefiles)
    {
        $this->coursefiles->removeElement($coursefiles);
    }

    /**
     * Get coursefiles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCoursefiles()
    {
        return $this->coursefiles;
    }

    /**
     * Set course
     *
     * @param \Elearning\CoursesBundle\Entity\Course $course
     * @return Chapter
     */
    public function setCourse(\Elearning\CoursesBundle\Entity\Course $course = null)
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get course
     *
     * @return \Elearning\CoursesBundle\Entity\Course 
     */
    public function getCourse()
    {
        return $this->course;
    }
    /**
     * @var string
     */
    private $state;


    /**
     * Set state
     *
     * @param string $state
     * @return Chapter
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }
    /**
     * @var integer
     */
    private $old_id;

    /**
     * @var \Elearning\CoursesBundle\Entity\Chapter
     */
    private $oldChapter;


    /**
     * Set old_id
     *
     * @param integer $oldId
     * @return Chapter
     */
    public function setOldId($oldId)
    {
        $this->old_id = $oldId;

        return $this;
    }

    /**
     * Get old_id
     *
     * @return integer 
     */
    public function getOldId()
    {
        return $this->old_id;
    }

    /**
     * Set oldChapter
     *
     * @param \Elearning\CoursesBundle\Entity\Chapter $oldChapter
     * @return Chapter
     */
    public function setOldChapter(\Elearning\CoursesBundle\Entity\Chapter $oldChapter = null)
    {
        $this->oldChapter = $oldChapter;

        return $this;
    }

    /**
     * Get oldChapter
     *
     * @return \Elearning\CoursesBundle\Entity\Chapter 
     */
    public function getOldChapter()
    {
        return $this->oldChapter;
    }
    /**
     * @var boolean
     */
    private $dirty;


    /**
     * Set dirty
     *
     * @param boolean $dirty
     * @return Chapter
     */
    public function setDirty($dirty)
    {
        $this->dirty = $dirty;

        return $this;
    }

    /**
     * Get dirty
     *
     * @return boolean 
     */
    public function getDirty()
    {
        return $this->dirty;
    }
}
