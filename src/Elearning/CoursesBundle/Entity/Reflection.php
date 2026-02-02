<?php
// Reflection Entity v1.0

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Elearning\UserBundle\Entity\User;
use Elearning\CoursesBundle\Entity\Course;

/**
 * Reflection
 *
 * @ORM\Table(name="reflection")
 * @ORM\Entity(repositoryClass="Elearning\CoursesBundle\Entity\ReflectionRepository")
 */
class Reflection
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Elearning\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="student_id", referencedColumnName="id", nullable=false)
     */
    private $student;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Elearning\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="teacher_id", referencedColumnName="id", nullable=true)
     */
    private $teacher;

    /**
     * @var Course
     *
     * @ORM\ManyToOne(targetEntity="Elearning\CoursesBundle\Entity\Course")
     * @ORM\JoinColumn(name="course_id", referencedColumnName="id", nullable=false)
     */
    private $course;

    /**
     * @var string
     *
     * @ORM\Column(name="student_text", type="text")
     */
    private $studentText;

    /**
     * @var string
     *
     * @ORM\Column(name="teacher_response", type="text", nullable=true)
     */
    private $teacherResponse;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="student_created_at", type="datetime")
     */
    private $studentCreatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="teacher_responded_at", type="datetime", nullable=true)
     */
    private $teacherRespondedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_read_by_teacher", type="boolean")
     */
    private $isReadByTeacher = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_read_by_student", type="boolean")
     */
    private $isReadByStudent = false;


    public function __construct()
    {
        $this->studentCreatedAt = new \DateTime();
    }


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
     * Set studentText
     *
     * @param string $studentText
     *
     * @return Reflection
     */
    public function setStudentText($studentText)
    {
        $this->studentText = $studentText;

        return $this;
    }

    /**
     * Get studentText
     *
     * @return string
     */
    public function getStudentText()
    {
        return $this->studentText;
    }

    /**
     * Set teacherResponse
     *
     * @param string $teacherResponse
     *
     * @return Reflection
     */
    public function setTeacherResponse($teacherResponse)
    {
        $this->teacherResponse = $teacherResponse;

        return $this;
    }

    /**
     * Get teacherResponse
     *
     * @return string
     */
    public function getTeacherResponse()
    {
        return $this->teacherResponse;
    }

    /**
     * Set studentCreatedAt
     *
     * @param \DateTime $studentCreatedAt
     *
     * @return Reflection
     */
    public function setStudentCreatedAt($studentCreatedAt)
    {
        $this->studentCreatedAt = $studentCreatedAt;

        return $this;
    }

    /**
     * Get studentCreatedAt
     *
     * @return \DateTime
     */
    public function getStudentCreatedAt()
    {
        return $this->studentCreatedAt;
    }

    /**
     * Set teacherRespondedAt
     *
     * @param \DateTime $teacherRespondedAt
     *
     * @return Reflection
     */
    public function setTeacherRespondedAt($teacherRespondedAt)
    {
        $this->teacherRespondedAt = $teacherRespondedAt;

        return $this;
    }

    /**
     * Get teacherRespondedAt
     *
     * @return \DateTime
     */
    public function getTeacherRespondedAt()
    {
        return $this->teacherRespondedAt;
    }

    /**
     * Set isReadByTeacher
     *
     * @param boolean $isReadByTeacher
     *
     * @return Reflection
     */
    public function setIsReadByTeacher($isReadByTeacher)
    {
        $this->isReadByTeacher = $isReadByTeacher;

        return $this;
    }

    /**
     * Get isReadByTeacher
     *
     * @return boolean
     */
    public function getIsReadByTeacher()
    {
        return $this->isReadByTeacher;
    }

    /**
     * Set isReadByStudent
     *
     * @param boolean $isReadByStudent
     *
     * @return Reflection
     */
    public function setIsReadByStudent($isReadByStudent)
    {
        $this->isReadByStudent = $isReadByStudent;

        return $this;
    }

    /**
     * Get isReadByStudent
     *
     * @return boolean
     */
    public function getIsReadByStudent()
    {
        return $this->isReadByStudent;
    }

    /**
     * Set student
     *
     * @param \Elearning\UserBundle\Entity\User $student
     *
     * @return Reflection
     */
    public function setStudent(\Elearning\UserBundle\Entity\User $student)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student
     *
     * @return \Elearning\UserBundle\Entity\User
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set teacher
     *
     * @param \Elearning\UserBundle\Entity\User $teacher
     *
     * @return Reflection
     */
    public function setTeacher(\Elearning\UserBundle\Entity\User $teacher = null)
    {
        $this->teacher = $teacher;

        return $this;
    }

    /**
     * Get teacher
     *
     * @return \Elearning\UserBundle\Entity\User
     */
    public function getTeacher()
    {
        return $this->teacher;
    }

    /**
     * Set course
     *
     * @param \Elearning\CoursesBundle\Entity\Course $course
     *
     * @return Reflection
     */
    public function setCourse(\Elearning\CoursesBundle\Entity\Course $course)
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
}
