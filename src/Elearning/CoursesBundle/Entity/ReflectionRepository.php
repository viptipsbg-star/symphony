<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ReflectionRepository extends EntityRepository
{
    public function findByStudent($studentId)
    {
        return $this->createQueryBuilder('r')
            ->where('r.student = :studentId')
            ->setParameter('studentId', $studentId)
            ->orderBy('r.studentCreatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByTeacher($teacherId)
    {
        return $this->createQueryBuilder('r')
            ->where('r.teacher = :teacherId')
            ->setParameter('teacherId', $teacherId)
            ->orderBy('r.studentCreatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
