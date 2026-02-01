<?php

namespace Elearning\CoursesBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ExamRepository extends EntityRepository
{
    /**
     * @param $courseId
     * @param bool $actual
     * @return array
     */
    public function getCourseExams($courseId, $actual = false)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('e')
            ->from('ElearningCoursesBundle:Exam', 'e', 'e.id')
            ->join('e.chapter', 'c')
            ->where('c.course = :courseId')
            ->andWhere('c.type = :type')
            ->orderBy('c.ordering')
            ->setParameters(array(
                'courseId' => $courseId,
                'type' => Chapter::TYPE_EXAM
            ));

        if ($actual) {
            $qb
                ->andWhere('c.state = :state')
                ->setParameter('state', Chapter::STATE_PUBLISHED);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $listenings
     * @return array
     */
    public function getExamsAttemptsCntByListenings(array $listenings)
    {
        $examsInfo = $this->getEntityManager()->createQueryBuilder()
            ->select(array(
                'ea.listening_id as listening_id',
                'ea.exam_id as exam_id',
                'COUNT(ea.id) as cnt',
                'SUM(ea.passed) as completed_cnt'
            ))
            ->from('ElearningCoursesBundle:ExamAttempt', 'ea')
            ->where('ea.listening_id IN (:listenings)')
            ->groupBy('ea.listening_id')
            ->addGroupBy('ea.exam_id')
            ->setParameter('listenings', $listenings)
            ->getQuery()->getResult();

        $result = array();

        foreach ($examsInfo as $info) {
            $result[$info['listening_id']][$info['exam_id']] = $info;
        }

        return $result;
    }

    /**
     * @param Course $course
     * @param bool $reverse
     * @return array
     */
    public function getActualExamsWithOldVersionsByCourse(Course $course, $reverse = false)
    {
        //Cache
        $chapters = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from('ElearningCoursesBundle:Chapter', 'c')
            ->where('c.course = :course')
            ->setParameter('course', $course)
            ->getQuery()->getResult();

        /** @var Exam[] $exams */
        $exams = $this->getEntityManager()->createQueryBuilder()
            ->select('e')
            ->from('ElearningCoursesBundle:Exam', 'e', 'e.chapter_id')
            ->join('ElearningCoursesBundle:Chapter', 'c', 'WITH', 'c.id = e.chapter_id')
            ->where('c.course = :course')
            ->setParameter('course', $course)
            ->getQuery()->getResult();

        $examsWithOldVersions = array();

        /** @var Exam $exam */
        foreach ($exams as $exam) {
            if ($exam->getChapter()->getState() !== Chapter::STATE_PUBLISHED) {
                continue;
            }

            $examsWithOldVersions[$exam->getId()][] = $exam->getId();
            $currentChapter = $exam->getChapter();

            while ($currentChapter && $currentChapter->getOldChapter()) {
                $examsWithOldVersions[$exam->getId()][] = $exams[$currentChapter->getOldChapter()->getId()]->getId();
                $currentChapter = $currentChapter->getOldChapter();
            }
        }

        if ($reverse) {
            $oldVersionsLinks = array();

            foreach ($examsWithOldVersions as $actualExam => $oldVersions) {
                foreach ($oldVersions as $oldVersion) {
                    $oldVersionsLinks[$oldVersion] = $actualExam;
                }
            }

            return $oldVersionsLinks;
        }

        return $examsWithOldVersions;
    }

}
