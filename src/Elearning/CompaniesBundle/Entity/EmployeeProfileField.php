<?php

namespace Elearning\CompaniesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use \Eventviva\ImageResize;

defined("EMPLOYEE_IMAGE_WIDTH") or define("EMPLOYEE_IMAGE_WIDTH", 130);
defined("EMPLOYEE_IMAGE_HEIGHT") or define("EMPLOYEE_IMAGE_HEIGHT", 160);

/**
 * EmployeeProfileField
 */
class EmployeeProfileField
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $employee_id;

    /**
     * @var string
     */
    private $fieldname;

    /**
     * @var string
     */
    private $fieldvalue;

    /**
     * @var \Elearning\CompaniesBundle\Entity\Employee
     */
    private $employee;


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
     * Set employee_id
     *
     * @param integer $employeeId
     * @return EmployeeProfileField
     */
    public function setEmployeeId($employeeId)
    {
        $this->employee_id = $employeeId;

        return $this;
    }

    /**
     * Get employee_id
     *
     * @return integer 
     */
    public function getEmployeeId()
    {
        return $this->employee_id;
    }

    /**
     * Set fieldname
     *
     * @param string $fieldname
     * @return EmployeeProfileField
     */
    public function setFieldname($fieldname)
    {
        $this->fieldname = $fieldname;

        return $this;
    }

    /**
     * Get fieldname
     *
     * @return string 
     */
    public function getFieldname()
    {
        return $this->fieldname;
    }

    /**
     * Set fieldvalue
     *
     * @param string $fieldvalue
     * @return EmployeeProfileField
     */
    public function setFieldvalue($fieldvalue)
    {
        $this->fieldvalue = $fieldvalue;

        return $this;
    }

    /**
     * Get fieldvalue
     *
     * @return string 
     */
    public function getFieldvalue()
    {
        return $this->fieldvalue;
    }

    /**
     * Set employee
     *
     * @param \Elearning\CompaniesBundle\Entity\Employee $employee
     * @return EmployeeProfileField
     */
    public function setEmployee(\Elearning\CompaniesBundle\Entity\Employee $employee = null)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get employee
     *
     * @return \Elearning\CompaniesBundle\Entity\Employee 
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    public function preUpload(UploadedFile $imagefile)
    {
        $image = null;
        if (null !== $imagefile) {
            $mimetype = $imagefile->getMimeType();
            if (strpos($mimetype, "image/") === FALSE) {
                return false;
            }
            $extension = $imagefile->guessClientExtension();
            if (!in_array($extension, array("png", "jpg", "jpeg", "gif", "bmp"))) {
                return false;
            }

            if (!empty($this->employee->getFieldValue('image'))) {
                $this->remove($this->employee->getFieldValue('image'));
            }

            $filename = sha1(uniqid(mt_rand(), true));
            $image = $filename . '.' . $imagefile->guessExtension();
        }
        return $image;
    }


    public function remove($image)
    {
        $path = $this->getAbsolutePath($image);
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

    public function upload(UploadedFile $imagefile, $image)
    {
        if (null === $imagefile) {
            return;
        }

        $imagefile->move(
            $this->getUploadRootDir(),
            $image
        );

        $imageResize = new ImageResize($this->getAbsolutePath($image));
        $imageResize->resizeToBestFit(EMPLOYEE_IMAGE_WIDTH, EMPLOYEE_IMAGE_HEIGHT);
        $imageResize->save($this->getAbsolutePath($image));
    }

    public function getAbsolutePath($image)
    {
        return null === $image
            ? null
            : $this->getUploadRootDir() . '/' . $image;
    }

    public function getWebPath($image)
    {
        return null === $image
            ? null
            : "/" . $this->getUploadDir() . '/' . $image;
    }

    protected function getUploadRootDir()
    {
        $path = __DIR__ . '/../../../../web/' . $this->getUploadDir();
        if (!file_exists($path)) {
            mkdir($path, 755);
        }
        return $path;
    }

    protected function getUploadDir()
    {
        return 'uploads/userimages';
    }
}
