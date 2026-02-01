<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmployeeSubject
 */
class EmployeeSubject
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
    private $subject_id;

    /**
     * @var integer
     */
    private $subject_status_id;

    /**
     * @var \Elearning\CoursesBundle\Entity\Subject
     */
    private $subject;

    /**
     * @var \Elearning\CompaniesBundle\Entity\Employee
     */
    private $employee;

    /**
     * @var \Elearning\CoursesBundle\Entity\SubjectStatus
     */
    private $subjectStatus;
    


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
     * @return EmployeeSubject
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
     * Set subject_id
     *
     * @param integer $subjectId
     * @return EmployeeSubject
     */
    public function setSubjectId($subjectId)
    {
        $this->subject_id = $subjectId;

        return $this;
    }

    /**
     * Get subject_id
     *
     * @return integer 
     */
    public function getSubjectId()
    {
        return $this->subject_id;
    }

    /**
     * Set subject_status_id
     *
     * @param \DateTime $subjectStatusId
     * @return EmployeeSubject
     */
    public function setSubjectStatusId($subjectStatusId)
    {
        $this->subject_status_id = $subjectStatusId;

        return $this;
    }

    /**
     * Get subject_status_id
     *
     * @return \DateTime 
     */
    public function getSubjectStatusId()
    {
        return $this->subject_status_id;
    }

    /**
     * Set subject
     *
     * @param \Elearning\CoursesBundle\Entity\Subject $subject
     * @return EmployeeSubject
     */
    public function setSubject(\Elearning\CoursesBundle\Entity\Subject $subject = null)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return \Elearning\CoursesBundle\Entity\Subject 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set employee
     *
     * @param \Elearning\CompaniesBundle\Entity\Employee $employee
     * @return EmployeeSubject
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

    /**
     * Set subjectStatus
     *
     * @param \Elearning\CoursesBundle\Entity\SubjectStatus $subjectStatus
     * @return EmployeeSubject
     */
    public function setSubjectStatus(\Elearning\CoursesBundle\Entity\SubjectStatus $subjectStatus = null)
    {
        $this->subjectStatus = $subjectStatus;

        return $this;
    }

    /**
     * Get subjectStatus
     *
     * @return \Elearning\CoursesBundle\Entity\SubjectStatus 
     */
    public function getSubjectStatus()
    {
        return $this->subjectStatus;
    }
}
