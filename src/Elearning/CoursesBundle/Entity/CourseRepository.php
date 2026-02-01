<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CourseRepository extends EntityRepository
{
    public function getFirstChapter($courseId)
    {
        $query = $this->getEntityManager()->createQuery("
                  SELECT ch
                  FROM ElearningCoursesBundle:Chapter ch
                  WHERE ch.state = 'published'
                  AND ch.type != 'exam'
                  AND ch.course_id = :courseId
                  ORDER BY ch.ordering
                ")
            ->setParameter('courseId', $courseId)
            ->setMaxResults(1);
        return $query->getSingleResult();
    }
}
