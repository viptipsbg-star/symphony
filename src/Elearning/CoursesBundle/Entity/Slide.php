<?php

namespace Elearning\CoursesBundle\Entity;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

define("SLIDE_THUMBNAIL_WIDTH", 120);
define("SLIDE_THUMBNAIL_HEIGHT", 120);
/**
 * Slide
 */
class Slide
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
     * @var integer
     */
    private $chapter_id;

    /**
     * @var integer
     */
    private $ordering;

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
     * @return Slide
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
     * @param integer $chapterId
     * @return Slide
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
     * Set ordering
     *
     * @param integer $ordering
     * @return Slide
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
     * Set chapter
     *
     * @param \Elearning\CoursesBundle\Entity\Chapter $chapter
     * @return Slide
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
     * @var UploadedFile
     */
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


        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getLocation()
        );
        $filename = pathinfo($this->getLocation(), PATHINFO_FILENAME);
        $extension = pathinfo($this->getLocation(), PATHINFO_EXTENSION);

        $this->setLocation($this->getWebPath());

        $im = new \Imagick($this->getAbsolutePath());
        $im->cropThumbnailImage(SLIDE_THUMBNAIL_WIDTH, SLIDE_THUMBNAIL_HEIGHT);
        $im->writeImage($this->getUploadRootDir()."/".$filename."_thumb.".$extension);

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
        $type = null;
        if (strpos($mimetype, 'image') !== false) {
            $type = "image";
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

        $filename = pathinfo($this->getLocation(), PATHINFO_FILENAME);
        $extension = pathinfo($this->getLocation(), PATHINFO_EXTENSION);

        return null === $this->location
            ? null
            : $this->getUploadRootDir()."/".$filename."_thumb.".$extension;
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
        return "uploads/courseslides/".$this->getChapterId();
    }

    /**
     * @var string
     */
    private $original_filename;


    /**
     * Set original_filename
     *
     * @param string $originalFilename
     * @return Slide
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
     * Copies file to public folder
     */
    public function copyFileToPublic() {
        $fs = new FileSystem();
        $oldpath = $this->getAbsolutePath();
        $filename = basename($oldpath);
        $newpath = $this->getUploadRootDir()."/public/".$filename;
        if (!$fs->exists($this->getUploadRootDir()."/public/")) {
            $fs->mkdir($this->getUploadRootDir()."/public/");
        }
        if ($fs->exists($newpath)) {
            $fs->remove($newpath);
        }
        $fs->copy($oldpath, $newpath, true);
        $this->setLocation($this->getUploadDir()."/public/".$filename);
    }
}
