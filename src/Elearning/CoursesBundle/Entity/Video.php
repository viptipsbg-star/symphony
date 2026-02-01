<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Video
 */
class Video
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $file_id;

    /**
     * @var integer
     */
    private $endtime;

    /**
     * @var \Elearning\CoursesBundle\Entity\File
     */
    private $file;


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
     * Set file_id
     *
     * @param integer $fileId
     * @return Video
     */
    public function setFileId($fileId)
    {
        $this->file_id = $fileId;

        return $this;
    }

    /**
     * Get file_id
     *
     * @return integer 
     */
    public function getFileId()
    {
        return $this->file_id;
    }

    /**
     * Set endtime
     *
     * @param integer $endtime
     * @return Video
     */
    public function setEndtime($endtime)
    {
        $this->endtime = $endtime;

        return $this;
    }

    /**
     * Get endtime
     *
     * @return integer 
     */
    public function getEndtime()
    {
        return $this->endtime;
    }

    /**
     * Set file
     *
     * @param \Elearning\CoursesBundle\Entity\File $file
     * @return Video
     */
    public function setFile(\Elearning\CoursesBundle\Entity\File $file = null)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return \Elearning\CoursesBundle\Entity\File 
     */
    public function getFile()
    {
        return $this->file;
    }
}
