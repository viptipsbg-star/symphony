<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use \Eventviva\ImageResize;
use Symfony\Component\HttpFoundation\File\UploadedFile;

defined("COURSE_IMAGE_WIDTH") or define("COURSE_IMAGE_WIDTH", 230);
defined("COURSE_IMAGE_HEIGHT") or define("COURSE_IMAGE_HEIGHT", 180);

/**
 * Course
 */
class Course
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
     * @var string
     */
    private $status;

    /**
     * @var integer
     */
    private $duration;

    /**
     * @var integer
     */
    private $creator_user_id;

    /**
     * @var integer
     */
    private $category_id;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \Elearning\CoursesBundle\Entity\Category
     */
    private $category;

    /**
     * @var \Elearning\UserBundle\Entity\User
     */
    private $creator;

    /**
     * @var integer
     */
    private $ordering;

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
     * @return Course
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
     * Set status
     *
     * @param string $status
     * @return Course
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     * @return Course
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }


    /**
     * Set user_id
     *
     * @param integer $userId
     * @return Course
     */
    public function setCreatorUserId($creator_user_id)
    {
        $this->creator_user_id = $creator_user_id;

        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer
     */
    public function getCreatorUserId()
    {
        return $this->creator_user_id;
    }

    /**
     * Set category_id
     *
     * @param integer $categoryId
     * @return Course
     */
    public function setCategoryId($categoryId)
    {
        $this->category_id = $categoryId;

        return $this;
    }

    /**
     * Get category_id
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Course
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
     * Set category
     *
     * @param \Elearning\CoursesBundle\Entity\Category $category
     * @return Course
     */
    public function setCategory(\Elearning\CoursesBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Elearning\CoursesBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set creator
     *
     * @param \Elearning\UserBundle\Entity\User $creator
     * @return Course
     */
    public function setCreator(\Elearning\UserBundle\Entity\User $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \Elearning\UserBundle\Entity\User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @var string
     */
    private $image;


    /**
     * Set image
     *
     * @param string $image
     * @return Course
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @var UploadedFile
     */
    private $imagefile;


    /**
     * Set imagefile
     *
     * @param UploadedFile $imagefile
     * @return Course
     */
    public function setImageFile($imagefile)
    {
        $this->imagefile = $imagefile;

        return $this;
    }

    /**
     * Get UploadedFile entity
     *
     * @return UploadedFile
     */
    public function getImageFile()
    {
        return $this->imagefile;
    }


    public function preUpload()
    {
        if (null !== $this->imagefile) {
            $mimetype = $this->getImageFile()->getMimeType();
            if (strpos($mimetype, "image/") === FALSE) {
                return false;
            }
            $extension = $this->getImageFile()->guessClientExtension();
            if (!in_array($extension, array("png", "jpg", "jpeg", "gif", "bmp"))) {
                return false;
            }
            if (!empty($this->image)) {
                $this->postRemove();
            }
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->image = $filename . '.' . $this->imagefile->guessExtension();
        }
        return true;
    }


    public function postRemove()
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

    public function upload()
    {
        // The file property can be empty if the field is not required
        if (null === $this->imagefile) {
            return;
        }

        // Use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->imagefile->move(
            $this->getUploadRootDir(),
            $this->image
        );

        // Set the path property to the filename where you've saved the file
        //$this->image = $this->imagefile->getClientOriginalName();

        $image = new ImageResize($this->getAbsolutePath());
        $image->resizeToBestFit(COURSE_IMAGE_WIDTH, COURSE_IMAGE_HEIGHT);
        $image->save($this->getAbsolutePath());

        // Clean up the file property as you won't need it anymore
        $this->imagefile = null;
    }


    public function getAbsolutePath()
    {
        return null === $this->image
            ? null
            : $this->getUploadRootDir() . '/' . $this->image;
    }

    public function getWebPath()
    {
        return null === $this->image
            ? null
            : "/" . $this->getUploadDir() . '/' . $this->image;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/courseimages';
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupCourses;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groupCourses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->can_pause_exams = false;
        $this->show_category = true;
    }

    /**
     * Add groupCourses
     *
     * @param \Elearning\CoursesBundle\Entity\GroupCourse $groupCourses
     * @return Course
     */
    public function addGroupCourse(\Elearning\CoursesBundle\Entity\GroupCourse $groupCourses)
    {
        $this->groupCourses[] = $groupCourses;

        return $this;
    }

    /**
     * Remove groupCourses
     *
     * @param \Elearning\CoursesBundle\Entity\GroupCourse $groupCourses
     */
    public function removeGroupCourse(\Elearning\CoursesBundle\Entity\GroupCourse $groupCourses)
    {
        $this->groupCourses->removeElement($groupCourses);
    }

    /**
     * Get groupCourses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupCourses()
    {
        return $this->groupCourses;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $questions;


    /**
     * Add questions
     *
     * @param \Elearning\CoursesBundle\Entity\CourseFaq $questions
     * @return Course
     */
    public function addQuestion(\Elearning\CoursesBundle\Entity\CourseFaq $questions)
    {
        $this->questions[] = $questions;

        return $this;
    }

    /**
     * Remove questions
     *
     * @param \Elearning\CoursesBundle\Entity\CourseFaq $questions
     */
    public function removeQuestion(\Elearning\CoursesBundle\Entity\CourseFaq $questions)
    {
        $this->questions->removeElement($questions);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $chapters;


    /**
     * Add chapters
     *
     * @param \Elearning\CoursesBundle\Entity\Chapter $chapters
     * @return Course
     */
    public function addChapter(\Elearning\CoursesBundle\Entity\Chapter $chapters)
    {
        $this->chapters[] = $chapters;

        return $this;
    }

    /**
     * Remove chapters
     *
     * @param \Elearning\CoursesBundle\Entity\Chapter $chapters
     */
    public function removeChapter(\Elearning\CoursesBundle\Entity\Chapter $chapters)
    {
        $this->chapters->removeElement($chapters);
    }

    /**
     * Get chapters
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChapters()
    {
        return $this->chapters;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $uploads;


    /**
     * Add uploads
     *
     * @param \Elearning\CoursesBundle\Entity\Upload $uploads
     * @return Course
     */
    public function addUpload(\Elearning\CoursesBundle\Entity\Upload $uploads)
    {
        $this->uploads[] = $uploads;

        return $this;
    }

    /**
     * Remove uploads
     *
     * @param \Elearning\CoursesBundle\Entity\Upload $uploads
     */
    public function removeUpload(\Elearning\CoursesBundle\Entity\Upload $uploads)
    {
        $this->uploads->removeElement($uploads);
    }

    /**
     * Get uploads
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUploads()
    {
        return $this->uploads;
    }

    /**
     * @var integer
     */
    private $listenperiod;


    /**
     * Set listenperiod
     *
     * @param integer $listenperiod
     * @return Course
     */
    public function setListenperiod($listenperiod)
    {
        $this->listenperiod = $listenperiod;

        return $this;
    }

    /**
     * Get listenperiod
     *
     * @return integer
     */
    public function getListenperiod()
    {
        return $this->listenperiod;
    }

    /**
     * @var boolean
     */
    private $certificate_needed;


    /**
     * Set certificate_needed
     *
     * @param boolean $certificateNeeded
     * @return Course
     */
    public function setCertificateNeeded($certificateNeeded)
    {
        $this->certificate_needed = $certificateNeeded;

        return $this;
    }

    /**
     * Get certificate_needed
     *
     * @return boolean 
     */
    public function getCertificateNeeded()
    {
        return $this->certificate_needed;
    }
    /**
     * @var boolean
     */
    private $flexible_order;


    /**
     * Set flexible_order
     *
     * @param boolean $flexibleOrder
     * @return Course
     */
    public function setFlexibleOrder($flexibleOrder)
    {
        $this->flexible_order = $flexibleOrder;

        return $this;
    }

    /**
     * Get flexible_order
     *
     * @return boolean 
     */
    public function getFlexibleOrder()
    {
        return $this->flexible_order;
    }
    /**
     * @var boolean
     */
    private $main_page;


    /**
     * Set main_page
     *
     * @param boolean $mainPage
     * @return Course
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
     * Set ordering
     *
     * @param integer $ordering
     * @return Course
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
     * @var boolean
     */
    private $can_pause_exams;


    /**
     * Set can_pause_exams
     *
     * @param boolean $canPauseExams
     * @return Course
     */
    public function setCanPauseExams($canPauseExams)
    {
        $this->can_pause_exams = $canPauseExams;

        return $this;
    }

    /**
     * Get can_pause_exams
     *
     * @return boolean 
     */
    public function getCanPauseExams()
    {
        return $this->can_pause_exams;
    }
    /**
     * @var boolean
     */
    private $show_exams_count;

    /**
     * @var boolean
     */
    private $show_category = true;

    /**
     * @var boolean
     */
    private $show_certificate_needed;

    /**
     * @var \DateTime
     */
    private $active_date_to;

    /**
     * @var boolean
     */
    private $show_active_date_to;


    /**
     * Set show_exams_count
     *
     * @param boolean $showExamsCount
     * @return Course
     */
    public function setShowExamsCount($showExamsCount)
    {
        $this->show_exams_count = $showExamsCount;

        return $this;
    }

    /**
     * Get show_exams_count
     *
     * @return boolean 
     */
    public function getShowExamsCount()
    {
        return $this->show_exams_count;
    }

    /**
     * Set show_category
     *
     * @param boolean $showCategory
     * @return Course
     */
    public function setShowCategory($showCategory)
    {
        $this->show_category = $showCategory;

        return $this;
    }

    /**
     * Get show_category
     *
     * @return boolean 
     */
    public function getShowCategory()
    {
        return $this->show_category;
    }

    /**
     * Set show_certificate_needed
     *
     * @param boolean $showCertificateNeeded
     * @return Course
     */
    public function setShowCertificateNeeded($showCertificateNeeded)
    {
        $this->show_certificate_needed = $showCertificateNeeded;

        return $this;
    }

    /**
     * Get show_certificate_needed
     *
     * @return boolean 
     */
    public function getShowCertificateNeeded()
    {
        return $this->show_certificate_needed;
    }

    /**
     * Set active_date_to
     *
     * @param \DateTime $activeDateTo
     * @return Course
     */
    public function setActiveDateTo($activeDateTo)
    {
        $this->active_date_to = $activeDateTo;

        return $this;
    }

    /**
     * Get active_date_to
     *
     * @return \DateTime 
     */
    public function getActiveDateTo()
    {
        return $this->active_date_to;
    }

    /**
     * Set show_active_date_to
     *
     * @param boolean $showActiveDateTo
     * @return Course
     */
    public function setShowActiveDateTo($showActiveDateTo)
    {
        $this->show_active_date_to = $showActiveDateTo;

        return $this;
    }

    /**
     * Get show_active_date_to
     *
     * @return boolean 
     */
    public function getShowActiveDateTo()
    {
        return $this->show_active_date_to;
    }
}
