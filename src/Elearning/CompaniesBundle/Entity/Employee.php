<?php

namespace Elearning\CompaniesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * Employees
 */
class Employee
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
     * @var integer
     */
    private $company_id;

    /**
     * @var integer
     */
    private $user_id;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var \Elearning\CompaniesBundle\Entity\Company
     */
    private $company;

    /**
     * @var \Elearning\UserBundle\Entity\User
     */
    private $user;


    /**
     * Set company_id
     *
     * @param integer $companyId
     * @return Employee
     */
    public function setCompanyId($companyId)
    {
        $this->company_id = $companyId;

        return $this;
    }

    /**
     * Get company_id
     *
     * @return integer 
     */
    public function getCompanyId()
    {
        return $this->company_id;
    }

    /**
     * Set user_id
     *
     * @param integer $userId
     * @return Employee
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set company
     *
     * @param \Elearning\CompaniesBundle\Entity\Company $company
     * @return Employee
     */
    public function setCompany(\Elearning\CompaniesBundle\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \Elearning\CompaniesBundle\Entity\Company 
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set user
     *
     * @param \Elearning\UserBundle\Entity\User $user
     * @return Employee
     */
    public function setUser(\Elearning\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Elearning\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $fields;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fields = new \Doctrine\Common\Collections\ArrayCollection();
        $this->active = true;
    }

    /**
     * Add fields
     *
     * @param \Elearning\CompaniesBundle\Entity\EmployeeProfileField $fields
     * @return Employee
     */
    public function addField(\Elearning\CompaniesBundle\Entity\EmployeeProfileField $fields)
    {
        $this->fields[] = $fields;

        return $this;
    }

    /**
     * Remove fields
     *
     * @param \Elearning\CompaniesBundle\Entity\EmployeeProfileField $fields
     */
    public function removeField(\Elearning\CompaniesBundle\Entity\EmployeeProfileField $fields)
    {
        $this->fields->removeElement($fields);
    }

    /**
     * Get fields
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFields()
    {
        return $this->fields;
    }
    /**
     * @var string
     */
    private $type;


    /**
     * Set type
     *
     * @param string $type
     * @return Employee
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
    
    
    public function getFieldValue($fieldname) {
        $selectedfield = null;
        
        foreach ($this->fields as $field) {
            if($field->getFieldname() == $fieldname) {
                $selectedfield = $field;
                break;
            }
        }
        if ($selectedfield) {
            return $selectedfield->getFieldvalue();
        }
        return null;
    }

    public function getField($fieldname) {
        foreach ($this->fields as $field) {
            if($field->getFieldname() == $fieldname) {
                return $field;
            }
        }
        
        return null;
    }
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groups;


    /**
     * Add groups
     *
     * @param \Elearning\CompaniesBundle\Entity\Group $groups
     * @return Employee
     */
    public function addGroup(\Elearning\CompaniesBundle\Entity\Group $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Elearning\CompaniesBundle\Entity\Group $groups
     */
    public function removeGroup(\Elearning\CompaniesBundle\Entity\Group $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }
    /**
     * @var string
     */
    private $state;


    /**
     * Set state
     *
     * @param string $state
     * @return Employee
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $manualCourseHours;


    /**
     * Add manualCourseHours
     *
     * @param \Elearning\CoursesBundle\Entity\ManualCourseEntry $manualCourseHours
     * @return Employee
     */
    public function addManualCourseHour(\Elearning\CoursesBundle\Entity\ManualCourseEntry $manualCourseHours)
    {
        $this->manualCourseHours[] = $manualCourseHours;

        return $this;
    }

    /**
     * Remove manualCourseHours
     *
     * @param \Elearning\CoursesBundle\Entity\ManualCourseEntry $manualCourseHours
     */
    public function removeManualCourseHour(\Elearning\CoursesBundle\Entity\ManualCourseEntry $manualCourseHours)
    {
        $this->manualCourseHours->removeElement($manualCourseHours);
    }

    /**
     * Get manualCourseHours
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getManualCourseHours()
    {
        return $this->manualCourseHours;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Employee
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $employeeSubject;


    /**
     * Add employeeSubject
     *
     * @param \Elearning\CoursesBundle\Entity\EmployeeSubject $employeeSubject
     * @return Employee
     */
    public function addEmployeeSubject(\Elearning\CoursesBundle\Entity\EmployeeSubject $employeeSubject)
    {
        $this->employeeSubject[] = $employeeSubject;

        return $this;
    }

    /**
     * Remove employeeSubject
     *
     * @param \Elearning\CoursesBundle\Entity\EmployeeSubject $employeeSubject
     */
    public function removeEmployeeSubject(\Elearning\CoursesBundle\Entity\EmployeeSubject $employeeSubject)
    {
        $this->employeeSubject->removeElement($employeeSubject);
    }

    /**
     * Get employeeSubject
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEmployeeSubject()
    {
        return $this->employeeSubject;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $diaryTopics;


    /**
     * Add diaryTopics
     *
     * @param \Elearning\CoursesBundle\Entity\DiaryTopic $diaryTopics
     * @return Employee
     */
    public function addDiaryTopic(\Elearning\CoursesBundle\Entity\DiaryTopic $diaryTopics)
    {
        $this->diaryTopics[] = $diaryTopics;

        return $this;
    }

    /**
     * Remove diaryTopics
     *
     * @param \Elearning\CoursesBundle\Entity\DiaryTopic $diaryTopics
     */
    public function removeDiaryTopic(\Elearning\CoursesBundle\Entity\DiaryTopic $diaryTopics)
    {
        $this->diaryTopics->removeElement($diaryTopics);
    }

    /**
     * Get diaryTopics
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDiaryTopics()
    {
        return $this->diaryTopics;
    }

    /**
     * Get active and sort diaryTopics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActiveDiaryTopics()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("active", 1))
            ->orderBy(array("issued" => Criteria::ASC));

        return $this->diaryTopics->matching($criteria);
    }

}
