<?php

namespace Elearning\CoursesBundle\Entity;

//use Proxies\__CG__\Elearning\CoursesBundle\Entity\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Material
 */
class Material
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

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
     * Set title
     *
     * @param string $title
     * @return Material
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Material
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
     * @return Material
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
     * @return Material
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
     * @return Material
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
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Get file
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Returns filename (with extension) of this file
     */
    public function getFilename() {
        $abspath = $this->getAbsolutePath();
        return basename($abspath);
    }

    /**
     * Uploads file. Used before flushing to DB
     * @return bool|void
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        $extension = $this->getFile()->getClientOriginalExtension();
        if (!in_array($extension, array("doc", "docx", "pdf", "xlsx", "xls", "ppt", "pptx", "txt", "jpg", "jpeg", "png", "gif", "mp4", "webm"))) {
            return false;
        }

        $uploadRootDir = $this->getUploadRootDir();
        $fs = new Filesystem();
        if (!$fs->exists($uploadRootDir)) {
            $fs->mkdir($uploadRootDir);
        }

        $newfilename = $this->getFile()->getClientOriginalName();
        $initialFilename = $newfilename;

        $index = 1;
        while ($fs->exists($uploadRootDir."/".$newfilename)) {
            $newfilename = pathinfo($initialFilename, PATHINFO_FILENAME).".".$index.".".$this->getFile()->getClientOriginalExtension();
            $index++;
        }


        $this->getFile()->move(
            $uploadRootDir,
            $newfilename
        );
        $this->setLocation($this->getUploadDir() . "/" . $newfilename);

        $this->file = null;

        return true;
    }


    public function removeFile()
    {
        $path = $this->getAbsolutePath();

        try {
            $fs = new Filesystem();
            if (!$fs->exists($path)) {
                return false;
            }
            $fs->remove($path);
        } catch (IOExceptionInterface $e) {
            /* TODO handle error */
            return false;
        }
        return true;
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

    public function getAbsolutePath()
    {
        return $this->getRootDir().$this->getLocation();
    }


    protected function getUploadRootDir()
    {
        return $this->getRootDir() . $this->getUploadDir();
    }

    protected function getRootDir()
    {
        return __DIR__ . "/../../../../";
    }

    protected function getUploadDir()
    {
        return "uploads/coursematerials/" . $this->getChapter()->getCourseId();
    }

}
