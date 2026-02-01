<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DiaryRate
 */
class DiaryRate
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $diary_topic_id;

    /**
     * @var integer
     */
    private $criterion_id;

    /**
     * @var string
     */
    private $rate;

    /**
     * @var \Elearning\CoursesBundle\Entity\DiaryTopic
     */
    private $diaryTopic;

    /**
     * @var \Elearning\CoursesBundle\Entity\DiaryCriterion
     */
    private $criterion;

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
     * Set diary_topic_id
     *
     * @param integer $diaryTopicId
     * @return DiaryRate
     */
    public function setDiaryTopicId($diaryTopicId)
    {
        $this->diary_topic_id = $diaryTopicId;

        return $this;
    }

    /**
     * Get diary_topic_id
     *
     * @return integer 
     */
    public function getDiaryTopicId()
    {
        return $this->diary_topic_id;
    }

    /**
     * Set criterion_id
     *
     * @param integer $criterionId
     * @return DiaryRate
     */
    public function setCriterionId($criterionId)
    {
        $this->criterion_id = $criterionId;

        return $this;
    }

    /**
     * Get criterion_id
     *
     * @return integer 
     */
    public function getCriterionId()
    {
        return $this->criterion_id;
    }

    /**
     * Set rate
     *
     * @param string $rate
     * @return DiaryRate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return string 
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set diaryTopic
     *
     * @param \Elearning\CoursesBundle\Entity\DiaryTopic $diaryTopic
     * @return DiaryRate
     */
    public function setDiaryTopic(\Elearning\CoursesBundle\Entity\DiaryTopic $diaryTopic = null)
    {
        $this->diaryTopic = $diaryTopic;

        return $this;
    }

    /**
     * Get diaryTopic
     *
     * @return \Elearning\CoursesBundle\Entity\DiaryTopic 
     */
    public function getDiaryTopic()
    {
        return $this->diaryTopic;
    }

    /**
     * Set criterion
     *
     * @param \Elearning\CoursesBundle\Entity\DiaryCriterion $criterion
     * @return DiaryRate
     */
    public function setCriterion(\Elearning\CoursesBundle\Entity\DiaryCriterion $criterion = null)
    {
        $this->criterion = $criterion;

        return $this;
    }

    /**
     * Get criterion
     *
     * @return \Elearning\CoursesBundle\Entity\DiaryCriterion 
     */
    public function getCriterion()
    {
        return $this->criterion;
    }
}
