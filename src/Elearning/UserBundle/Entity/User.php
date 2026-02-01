<?php

namespace Elearning\UserBundle\Entity;

use Elearning\CompaniesBundle\Entity\EmployeeProfileField;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * User
 */
class User extends BaseUser
{
    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $groups;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $courseListenings;

    private $employee = null;


    /**
     * Set employee
     *
     * @param boolean $employee
     * @return Employee
     */
    public function setEmployee($employee)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get employee
     *
     * @return Employee 
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * Get employee field value
     *
     * @param string $fieldName
     * @return mixed $value
     */
    public function __get($fieldName)
    {
        if (!$this->employee) {
            return null;
        }
        $value = $this->employee->getFieldValue($fieldName);
        if (preg_match('/[\d]{4}\.[\d]{2}\.[\d]{2}/', $value)) {
            $value = new \DateTime(str_replace('.', '-', $value));
        }
        return $value;
    }

    /**
     * Set employee field value
     *
     * @param string $fieldName
     * @param string $value
     * @return string
     */
    public function __set($fieldName, $value)
    {
        $targetField = null;
        $fields = $this->employee->getFields();
        foreach ($fields as $field) {
            if ($field->getFieldname() == $fieldName) {
                $targetField = $field;
            }
        }

        if (!$targetField) {
            $targetField = new EmployeeProfileField();
            $targetField->setEmployee($this->employee);
            $targetField->setFieldname($fieldName);
            $this->employee->addField($targetField);
        }

        if ($value instanceof \DateTime) {
            $value = $value->format('Y.m.d');
        }

        $targetField->setFieldvalue($value);

        return $this;
    }

    /**
     * Add courseListenings
     *
     * @param \Elearning\CoursesBundle\Entity\CourseListening $courseListenings
     * @return User
     */
    public function addCourseListening(\Elearning\CoursesBundle\Entity\CourseListening $courseListenings)
    {
        $this->courseListenings[] = $courseListenings;

        return $this;
    }

    /**
     * Remove courseListenings
     *
     * @param \Elearning\CoursesBundle\Entity\CourseListening $courseListenings
     */
    public function removeCourseListening(\Elearning\CoursesBundle\Entity\CourseListening $courseListenings)
    {
        $this->courseListenings->removeElement($courseListenings);
    }

    /**
     * Get courseListenings
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCourseListenings()
    {
        return $this->courseListenings;
    }
}
