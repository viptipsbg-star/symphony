<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 */
class Category
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $parent_id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var \Elearning\CoursesBundle\Entity\Category
     */
    private $parent;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->main_page = false;
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
     * @return Category
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
     * Set parent_id
     *
     * @param integer $parentId
     * @return Category
     */
    public function setParentId($parentId)
    {
        $this->parent_id = $parentId;

        return $this;
    }

    /**
     * Get parent_id
     *
     * @return integer 
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * Add children
     *
     * @param \Elearning\CoursesBundle\Entity\Category $children
     * @return Category
     */
    public function addChild(\Elearning\CoursesBundle\Entity\Category $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Elearning\CoursesBundle\Entity\Category $children
     */
    public function removeChild(\Elearning\CoursesBundle\Entity\Category $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }


    /**
     * Get children with state published
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPublishedChildren()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('state', 'published'));
        return $this->children->matching($criteria);
    }

    /**
     * Set parent
     *
     * @param \Elearning\CoursesBundle\Entity\Category $parent
     * @return Category
     */
    public function setParent(\Elearning\CoursesBundle\Entity\Category $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Elearning\CoursesBundle\Entity\Category 
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function __toString()
    {
        return strval($this->name);
    }
    /**
     * @var integer
     */
    private $ordering;


    /**
     * Set ordering
     *
     * @param integer $ordering
     * @return Category
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
     * @var string
     */
    private $state;


    /**
     * Set state
     *
     * @param string $state
     * @return Category
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
     * @var boolean
     */
    private $main_page;


    /**
     * Set main_page
     *
     * @param boolean $mainPage
     * @return Category
     */
    public function setMainPage($mainPage)
    {
        $this->main_page = $mainPage;

        return $this;
    }

    /**
     * Get main_page
     *
     * @return boolean 
     */
    public function getMainPage()
    {
        return $this->main_page;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $courses;


    /**
     * Add courses
     *
     * @param \Elearning\CoursesBundle\Entity\Course $courses
     * @return Category
     */
    public function addCourse(\Elearning\CoursesBundle\Entity\Course $courses)
    {
        $this->courses[] = $courses;

        return $this;
    }

    /**
     * Remove courses
     *
     * @param \Elearning\CoursesBundle\Entity\Course $courses
     */
    public function removeCourse(\Elearning\CoursesBundle\Entity\Course $courses)
    {
        $this->courses->removeElement($courses);
    }

    /**
     * Get courses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCourses()
    {
        return $this->courses;
    }
}
