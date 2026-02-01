<?php

namespace Elearning\CoursesBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Upload
 */
class Upload
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $original_filename;

    /**
     * @var string
     */
    private $location;

    /**
     * @var integer
     */
    private $filesize;

    /**
     * @var integer
     */
    private $course_id;

    /**
     * @var \Elearning\CoursesBundle\Entity\Course
     */
    private $course;


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
     * Set original_filename
     *
     * @param string $originalFilename
     * @return Upload
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->original_filename = $originalFilename;

        return $this;
    }

    /**
     * Get original_filename
     *
     * @return string 
     */
    public function getOriginalFilename()
    {
        return $this->original_filename;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Upload
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
     * Set filesize
     *
     * @param integer $filesize
     * @return Upload
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;

        return $this;
    }

    /**
     * Get filesize
     *
     * @return integer 
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * Set course_id
     *
     * @param integer $courseId
     * @return Upload
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
     * Set course
     *
     * @param \Elearning\CoursesBundle\Entity\Course $course
     * @return Upload
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



    private $file;

    /**
     * Sets file
     *
     * @param UploadedFile $file
     * @return File
     */
    public function setFile(UploadedFile $file = null) {
        $this->file = $file;
        return $this;
    }

    /**
     * Get file
     *
     * @return UploadedFile
     */
    public function getFile() {
        return $this->file;
    }


    public function upload() {
        if (null === $this->getFile()) {
            return;
        }


        $target = $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getLocation()
        );
        $filename = pathinfo($this->getLocation(), PATHINFO_FILENAME);
        $extension = pathinfo($this->getLocation(), PATHINFO_EXTENSION);

        $this->setLocation($this->getWebPath());

        $filesize = filesize($this->getAbsolutePath());
        $this->setFilesize($filesize);


        $mimetype = $this->getMimeType();

        $this->file = null;
    }

    public function removeFile() {
        $path = $this->getAbsolutePath();

        try {
            $fs = new Filesystem();
            if (!$fs->exists($path)) {
                return false;
            }
            $fs->remove($path);
        }
        catch (IOExceptionInterface $e) {
            var_dump("EXCEPTION:", $e);exit();
            /* TODO handle error */
            return false;
        }
        return true;
    }

    public function getMimeType() {
        $path = $this->getRootDir().$this->location;

        $file = new SymfonyFile($path);
        return $file->getMimeType();
    }


    /* TODO review and refactor all these paths */
    public function getAbsolutePath() {
        return null === $this->location
            ? null
            : $this->getRootDir().$this->location;
    }

    public function getWebPath() {
        return null === $this->location
            ? null
            : $this->getUploadDir()."/".$this->location;
    }

    protected function getUploadRootDir() {
        return $this->getRootDir().$this->getUploadDir();
    }

    protected function getRootDir() {
        return __DIR__."/../../../../";
    }

    protected function getUploadDir() {
        return "uploads/courseuploadfiles";
    }

}
