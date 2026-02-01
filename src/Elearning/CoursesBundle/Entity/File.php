<?php

namespace Elearning\CoursesBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Doctrine\ORM\Mapping as ORM;

define("THUMBNAIL_WIDTH", 120);
define("THUMBNAIL_HEIGHT", 120);
define("VIDEO_THUMBNAIL", "uploads/video-thumb.png");
define("AUDIO_THUMBNAIL", "uploads/audio-thumb.png");

/**
 * File
 */
class File
{
    /**
     * @var integer
     */
    private $id;


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
     * @var string
     */
    private $original_filename;

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
    private $ordering;

    /**
     * @var integer
     */
    private $published;


    /**
     * @var \Elearning\CoursesBundle\Entity\Chapter
     */
    private $chapter;



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


    /**
     * Set original_filename
     *
     * @param string $originalFilename
     * @return File
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
     * Set type
     *
     * @param string $type
     * @return File
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
     * Set location
     *
     * @param string $location
     * @return File
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
     * Set chapter_id
     *
     * @param integer $chapter_id
     * @return File
     */
    public function setChapterId($chapter_id)
    {
        $this->chapter_id = $chapter_id;

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
     * Set ordering
     *
     * @param integer $ordering
     * @return File
     */
    public function setOrdering($ordering = null)
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
     * @var \Elearning\CoursesBundle\Entity\Video
     */
    private $video;


    /**
     * Set video
     *
     * @param \Elearning\CoursesBundle\Entity\Video $video
     * @return File
     */
    public function setVideo(\Elearning\CoursesBundle\Entity\Video $video = null)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return \Elearning\CoursesBundle\Entity\Video 
     */
    public function getVideo()
    {
        return $this->video;
    }


    /**
     * Set published
     *
     * @param integer $published
     * @return File
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published
     *
     * @return integer
     */
    public function getPublished()
    {
        return $this->published;
    }


    /**
     * Set chapter
     *
     * @param \Elearning\CoursesBundle\Entity\Chapter $chapter
     * @return Chapter
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


    public function upload() {
        if (null === $this->getFile()) {
            return;
        }


        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getLocation()
        );
        $filename = pathinfo($this->getLocation(), PATHINFO_FILENAME);
        $extension = pathinfo($this->getLocation(), PATHINFO_EXTENSION);

        $this->setLocation($this->getWebPath());


        $type = null;
        $type = $this->getFileType();

        if ($type == "image") {
            $im = new \Imagick($this->getAbsolutePath());
            $im->cropThumbnailImage(THUMBNAIL_WIDTH, THUMBNAIL_HEIGHT);
            $im->writeImage($this->getUploadRootDir()."/".$filename."_thumb.".$extension);
        }

        $this->file = null;
    }

    public function removeFile() {
        $path = $this->getAbsolutePath();
        $thumb_path = $this->getAbsoluteThumbnailPath();

        try {
            $fs = new Filesystem();
            if (!$fs->exists($path)) {
                return false;
            }
            $fs->remove($path);
            if (!$fs->exists($thumb_path)) {
                return false;
            }
            $fs->remove($thumb_path);
        }
        catch (IOExceptionInterface $e) {
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

    public function getFileType() {
        $mimetype = $this->getMimeType();
        if (strpos($mimetype, "video") !== false) {
            $type = "video";
        }
        else if (strpos($mimetype, 'image') !== false) {
            $type = "image";
        }
        else if (strpos($mimetype, 'audio') !== false) {
            $type = "audio";
        }
        return $type;
    }

    public function getFileSize() {
        $path = $this->getRootDir().$this->location;
        if (is_file($path)) {
            return filesize($path);
        }
        return 0;
    }

    public function getElement() {
        /* TODO complete this */
        $mimetype = $this->getMimeType();
        $element = "";
        if (strpos($mimetype, "video") !== False) {
            $element = '<video src="'."ROUTE".'">';
        }
    }


    /* TODO review and refactor all these paths */
    public function getAbsolutePath() {
        return null === $this->location
            ? null
            : $this->getRootDir().$this->location;
    }

    public function getAbsoluteThumbnailPath() {
        if (empty($this->location)) {
            return null;
        }

        $dirname = pathinfo($this->getLocation(), PATHINFO_DIRNAME);
        $filename = pathinfo($this->getLocation(), PATHINFO_FILENAME);
        $extension = pathinfo($this->getLocation(), PATHINFO_EXTENSION);

        return null === $this->location
            ? null
            : $this->getRootDir().$dirname."/".$filename."_thumb.".$extension;
    }

    public function getAbsoluteVideoThumbnailPath() {
        return $this->getRootDir().VIDEO_THUMBNAIL;
    }

    public function getAbsoluteAudioThumbnailPath() {
        return $this->getRootDir().AUDIO_THUMBNAIL;
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
        return "uploads/coursefiles/".$this->getChapterId();
    }


    public function moveFileToNewLocation() {
        $oldpath = $this->getRootDir().$this->location;
        $basename = pathinfo($oldpath, PATHINFO_BASENAME);
        $extension = pathinfo($this->getLocation(), PATHINFO_EXTENSION);
        if (strpos($oldpath, $this->getUploadDir()) === False) { /* File is not in new location */
            if (is_file($oldpath)) {
                $newpath = $this->getUploadRootDir()."/".$basename;
                if (!file_exists($this->getUploadRootDir())) {
                    mkdir($this->getUploadRootDir());
                }
                rename($oldpath, $newpath);
            }

            $this->setLocation($this->getUploadDir()."/".$basename);
        }
        $filename = pathinfo($oldpath, PATHINFO_FILENAME);
        $thumbFilename = $filename."_thumb.".$extension;
        $oldDir = $this->getUploadRootDir()."/..";
        if (is_file($oldDir."/".$thumbFilename)) {
            rename($oldDir."/".$thumbFilename, $this->getUploadRootDir()."/".$thumbFilename);
        }
    }
}
