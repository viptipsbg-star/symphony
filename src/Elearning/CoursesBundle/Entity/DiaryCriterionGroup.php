<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DiaryCriterionGroup
 */
class DiaryCriterionGroup
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
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $ordering;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $criteria;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->criteria = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return DiaryCriterionGroup
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
     * Set description
     *
     * @param string $description
     * @return DiaryCriterionGroup
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set ordering
     *
     * @param integer $ordering
     * @return DiaryCriterionGroup
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
     * Set active
     *
     * @param boolean $active
     * @return DiaryCriterionGroup
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
     * Add criteria
     *
     * @param \Elearning\CoursesBundle\Entity\DiaryCriterion $criteria
     * @return DiaryCriterionGroup
     */
    public function addCriterium(\Elearning\CoursesBundle\Entity\DiaryCriterion $criteria)
    {
        $this->criteria[] = $criteria;

        return $this;
    }

    /**
     * Remove criteria
     *
     * @param \Elearning\CoursesBundle\Entity\DiaryCriterion $criteria
     */
    public function removeCriterium(\Elearning\CoursesBundle\Entity\DiaryCriterion $criteria)
    {
        $this->criteria->removeElement($criteria);
    }

    /**
     * Get criteria
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}
