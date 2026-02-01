<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DiaryCriterion
 */
class DiaryCriterion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $criterion_group_id;

    /**
     * @var string
     */
    private $text;

    /**
     * @var integer
     */
    private $max_rate;

    /**
     * @var integer
     */
    private $ordering;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var \Elearning\CoursesBundle\Entity\DiaryCriterionGroup
     */
    private $criterion_group;

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
     * Set criterion_group_id
     *
     * @param integer $criterionGroupId
     * @return DiaryCriterion
     */
    public function setCriterionGroupId($criterionGroupId)
    {
        $this->criterion_group_id = $criterionGroupId;

        return $this;
    }

    /**
     * Get criterion_group_id
     *
     * @return integer 
     */
    public function getCriterionGroupId()
    {
        return $this->criterion_group_id;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return DiaryCriterion
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
     * Set ordering
     *
     * @param integer $ordering
     * @return DiaryCriterion
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
     * @return DiaryCriterion
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
     * Set criterion_group
     *
     * @param \Elearning\CoursesBundle\Entity\DiaryCriterionGroup $criterionGroup
     * @return DiaryCriterion
     */
    public function setCriterionGroup(\Elearning\CoursesBundle\Entity\DiaryCriterionGroup $criterionGroup = null)
    {
        $this->criterion_group = $criterionGroup;

        return $this;
    }

    /**
     * Get criterion_group
     *
     * @return \Elearning\CoursesBundle\Entity\DiaryCriterionGroup
     */
    public function getCriterionGroup()
    {
        return $this->criterion_group;
    }

    /**
     * Set max_rate
     *
     * @param integer $maxRate
     * @return DiaryCriterion
     */
    public function setMaxRate($maxRate)
    {
        $this->max_rate = $maxRate;

        return $this;
    }

    /**
     * Get max_rate
     *
     * @return integer 
     */
    public function getMaxRate()
    {
        return $this->max_rate;
    }
}
