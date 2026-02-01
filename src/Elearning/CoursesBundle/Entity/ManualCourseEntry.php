<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ManualCourseEntry
 */
class ManualCourseEntry
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
     * @var integer
     */
    private $hours;

    /**
     * @var \DateTime
     */
    private $updatetime;

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
     * @return ManualCourseEntry
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
     * Set hours
     *
     * @param integer $hours
     * @return ManualCourseEntry
     */
    public function setHours($hours)
    {
        $this->hours = $hours;

        return $this;
    }

    /**
     * Get hours
     *
     * @return integer 
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * Set updatetime
     *
     * @param \DateTime $updatetime
     * @return ManualCourseEntry
     */
    public function setUpdatetime($updatetime)
    {
        $this->updatetime = $updatetime;

        return $this;
    }

    /**
     * Get updatetime
     *
     * @return \DateTime 
     */
    public function getUpdatetime()
    {
        return $this->updatetime;
    }

    /**
     * Set employee
     *
     * @param \Elearning\CompaniesBundle\Entity\Employee $employee
     * @return ManualCourseEntry
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
}
