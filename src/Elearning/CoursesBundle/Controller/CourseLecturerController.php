<?php

namespace Elearning\CoursesBundle\Controller;

use Elearning\CompaniesBundle\Entity\Administrator;
use Elearning\CoursesBundle\Entity\ExamAttempt;
use Elearning\CoursesBundle\Form\CategoryType;
use Proxies\__CG__\Elearning\CoursesBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elearning\CoursesBundle\Entity\File;
use Elearning\CoursesBundle\Entity\Course;
use Elearning\CoursesBundle\Entity\Chapter;
use Elearning\CoursesBundle\Entity\Video;
use Elearning\CoursesBundle\Entity\CourseFile;
use Elearning\CoursesBundle\Entity\CourseFaq;
use Elearning\CoursesBundle\Entity\Lesson;
use Elearning\CoursesBundle\Entity\Exam;
use Elearning\CoursesBundle\Form\CourseType;
use Elearning\CoursesBundle\Form\CourseQuestionType;
use Elearning\CoursesBundle\Form\CourseFaqType;
use Elearning\CoursesBundle\Form\ChapterType;
use \GetId3\GetId3Core as GetId3;


class CourseLecturerController extends Controller
{

    /* Shows list of courses */
    /* GET route */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $filter = $request->query->get('filter');

        $params = array();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        //$queryParams = array('creator_user_id' => $user->getId());
        $queryParams = array();
        if (!isset($filter['status'])) {
            $filter = array('status' => 'published');
        }


        if (!empty($filter['status'])) {
            $queryParams['status'] = $filter['status'];
        }

        if ($filter['status'] == "initiated") {
            $queryParams['status'] = array('initiated', 'unpublished');
        }


        $courses = $em->getRepository('ElearningCoursesBundle:Course')
            ->findBy($queryParams, array('ordering' => 'ASC'));

        if (empty($courses)) {
            $filter['status'] = "";
            unset($queryParams['status']);
            $courses = $em->getRepository('ElearningCoursesBundle:Course')
                ->findBy($queryParams);
        }

        $params['filter'] = $filter;

        $params['courses'] = $courses;

        $questions_counts = array();
        foreach ($courses as $course) {
            $query = $em->createQuery(
                'SELECT q
                FROM ElearningCoursesBundle:CourseQuestion q
                JOIN q.courseListening l
                WHERE l.course_id = :course_id
                AND q.answer IS NULL
                ORDER BY q.createdtime ASC'
            )->setParameter('course_id', $course->getId());
            $questions = $query->getResult();
            $questions_counts[$course->getId()] = count($questions);
        }
        $params['questionscounts'] = $questions_counts;

        return $this->render(sprintf("ElearningCoursesBundle:CourseLecturer:course_list.html.twig"), $params);
    }


    /* Shows form for course creation */
    /* GET route */
    public function newAction($step = 1, $id = null)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $params = array();
        $em = $this->getDoctrine()->getManager();
        $app_config = $this->container->getParameter('app_config');
        if ($step == 1) {
            $groups = array();
            if (empty($id)) {
                $course = new Course();
            } else {
                $course = $em->getRepository('ElearningCoursesBundle:Course')
                    ->find($id);
                $params['course_id'] = $course->getId();
                $groupsQB = $em->createQueryBuilder()
                    ->select('DISTINCT g')
                    ->from("ElearningCoursesBundle:GroupCourse", 'gc')
                    ->join("ElearningCompaniesBundle:Group", 'g', 'WITH', 'gc.group_id = g.id')
                    ->where("gc.active = 1")
                    ->andWhere("gc.course = :course")
                    ->andWhere("g.state = :state")
                    ->setParameter('state', 'published')
                    ->setParameter("course", $course);
                $groups = $groupsQB->getQuery()->getResult();
            }
            $form = $this->createForm(new CourseType(), $course, array(
                'action' => $this->generateUrl('elearning_courses_create', array('id' => $id, 'step' => 1)),
                'method' => 'POST'
            ));
            $params['course'] = $course;
            $params['groups'] = $groups;
            $params['flexibleEnabled'] = isset($app_config['flexible_courses_enabled']) ? $app_config['flexible_courses_enabled'] : false;
            $params['form'] = $form->createView();
        } else if ($step == 2) {
            $chapter = $this->getFirstByOrderChapter($id);
            $chapter = !empty($chapter) ? $chapter[0] : new Chapter();
            
            $chapterform = $this->createForm(new ChapterType(), new Chapter(), array(
                'action' => $this->generateUrl('elearning_courses_create_chapter'),
                'method' => 'POST',
                'disabled_chapter_types' => isset($app_config['disabled_chapter_types']) ? $app_config['disabled_chapter_types'] : null
            ));
            $chapterform->remove('ordering');

            $params['chapter'] = $chapter;
            $params['chapterform'] = $chapterform->createView();
            $params['chapter_id'] = $chapter->getId();
            $params['course_id'] = $id;


            $rep = $this->getDoctrine()->getManager()->getRepository("ElearningCoursesBundle:Chapter");
            $chapters = $rep->findBy(array('course_id' => $id, 'state' => array('edit')), array('ordering' => 'ASC'));

            $params['chapters'] = $chapters;
        } else if ($step == 3) {
            $em = $this->getDoctrine()->getManager();
            $course = $em->find("ElearningCoursesBundle:Course", $id);
            $params['course'] = $course;

            $params['course_id'] = $id;

            $chapters = $em->getRepository('ElearningCoursesBundle:Chapter')
                ->findBy(array('course_id' => $id, 'state' => array('edit')), array('ordering' => 'ASC'));
            $params['chapters'] = $chapters;

        }
        $params['step'] = $step;
        return $this->render(sprintf("ElearningCoursesBundle:CourseLecturer:new_course_step%d.html.twig", $step), $params);
    }

    private function getFirstByOrderChapter($course_id)
    {
        $em = $this->getDoctrine()->getManager();
        $chapter = $em->getRepository('ElearningCoursesBundle:Chapter')
            ->findBy(array('course_id' => $course_id, 'state' => 'edit'), array('ordering' => 'ASC'), 1);
        return $chapter;
    }

    /* Stores new course */
    /* POST route */
    public function createAction(Request $request, $step = null, $id = null)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        /* TODO return error when form is not valid */
        $em = $this->getDoctrine()->getManager();
        $course = ($id)
            ? $em->getRepository('ElearningCoursesBundle:Course')->find($id)
            : new Course();
        $form = $this->createForm(new CourseType(), $course);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if (!$course->preUpload()) {
                $translator = $this->get('translator');
                $message = $translator->trans("new_course.form.error_uploading_image_file");
                $this->addFlash('error', $message);
                return $this->redirectToRoute('elearning_courses_new', array('step' => 1, 'id' => $course->getId()));
            }
            $user = $this->getUser();
            $status = $course->getStatus();
            if (empty($status)) {
                $course->setStatus("initiated");
            }
            $qb = $em->createQueryBuilder()
                ->select('MAX(c.ordering)')
                ->from("ElearningCoursesBundle:Course", 'c');
            $ordering = $qb->getQuery()
                ->getSingleScalarResult();

            $course->setOrdering($ordering + 1);
            $course->setCreator($em->getReference('ElearningUserBundle:User', $user->getId()));
            $em->persist($course);
            $em->flush();
            $course->upload();
            return $this->redirectToRoute('elearning_courses_new', array('step' => 2, 'id' => $course->getId()));
        }

        $params = array();
        $params['form'] = $form->createView();
        $params['course'] = $course;
        $params['flexibleEnabled'] = isset($app_config['flexible_courses_enabled']) ? $app_config['flexible_courses_enabled'] : false;
        $params['step'] = 1;

        return $this->render("ElearningCoursesBundle:CourseLecturer:new_course_step1.html.twig", $params);
    }

    public function createChapterAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter = new Chapter();
        $course_id = $request->get('chapter')['course_id'];
        $ordering = $this->getLastChapterOrdering($course_id);

        $form = $this->createForm(new ChapterType(), $chapter);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /* TODO Those 2 lines are some kind of hack, need to fix them */
            $chapter->setCourse($em->getReference('ElearningCoursesBundle:Course', $course_id));
            $chapter->setOrdering(++$ordering);
            $chapter->setState('edit');
            $chapter->setDirty(true);


            if ($chapter->getType() == "lesson") {
                $lesson = new Lesson();
                $lesson->setChapter($chapter);
                $lesson->setContent("");
                $lesson->setVersion("edit");
                $em->persist($lesson);
            } else if ($chapter->getType() == "exam") {
                $exam = new Exam();
                $exam->setChapter($chapter);
                $exam->setOptions("");
                $exam->setVersion("edit");
                $em->persist($exam);
            }


            $em->persist($chapter);
            $em->flush();
            return new JsonResponse(array('success' => true, 'chapter' => array(
                'id' => $chapter->getId(),
                'name' => $chapter->getName(),
                'type' => $chapter->getType()
            )));
        }
        return new JsonResponse(array('success' => false));
    }

    private function getLastChapterOrdering($course_id)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            "SELECT c.ordering
             FROM ElearningCoursesBundle:Chapter c
             WHERE c.course_id = :course_id
             AND c.state = 'edit'
             ORDER BY c.ordering DESC")
            ->setParameter('course_id', $course_id);
        $query->setMaxResults(1);
        $result = $query->getScalarResult();
        $ordering = empty($result) ? 0 : $result[0]['ordering'];
        return (int)$ordering;
    }

    public function changeChapterOrderAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $orderedIds = $request->request->get('chapter');
        $course_id = $request->request->get('course_id');
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            "SELECT c
             FROM ElearningCoursesBundle:Chapter c
             INDEX BY c.id
             WHERE c.course_id = :course_id")
            ->setParameter('course_id', $course_id);
        $chapters = $query->getResult();

        foreach ($orderedIds as $ordering => $chapter_id) {
            $chapters[$chapter_id]->setOrdering($ordering);
        }
        $em->flush();
        return new JsonResponse(array('success' => true));
    }


    public function saveVideoAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $times = $request->request->get('times');
        if (!is_array($times)) {
            $times = array();
        }
        if (!isset($times['mainvideo'])) {
            $times['mainvideo'] = array();
        }
        if (!isset($times['slidesvideo'])) {
            $times['slidesvideo'] = array();
        }
        $times = array_replace($times['mainvideo'], $times['slidesvideo']);
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('ElearningCoursesBundle:Video');
        $index = 0;
        foreach ($times as $file_id => $time) {
            $video = $rep->findOneBy(array('file_id' => $file_id));
            if (empty($video)) {
                $video = new Video();
                $video->setFile($em->getReference('ElearningCoursesBundle:File', $file_id));
            }
            $video->setEndTime($time);
            $video->getFile()->setOrdering($index);
            $index++;
            $em->persist($video);
        }
        $em->flush();
        return new JsonResponse(array('success' => true));
    }

    public function deleteAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');


        $em = $this->getDoctrine()->getManager();
        $course = $em->find("ElearningCoursesBundle:Course", $id);

        $course->setStatus("deleted");
        $em->flush();
        /* TODO mark course as deleted */
    }

    public function changeStateAction($id, $action)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $course = $em->find('ElearningCoursesBundle:Course', $id);
        if (!$course) {
            throw $this->createNotFoundException(
                'No course found for id: ' . $id
            );
        }
        switch ($action) {
            case 'publish':
                $course->setStatus('published');
                break;
            case 'unpublish':
                $course->setStatus('unpublished');
                break;
            case 'trash':
                $course->setStatus('trash');
                break;
        }
        $em->persist($course);
        $em->flush();
        return $this->redirectToRoute('elearning_courses_list_lecturer');
    }

    public function processingNotifyDiagnosticsAction(Request $request)
    {
        $code = "j8JNHJn8Uhj7Hjhg8Hjvd6Fhb7Gnn7G";
        $receivedCode = $request->request->get('code');
        if ($receivedCode !== $code) {
            return new JsonResponse(array('success' => false));
        }
        $post = $_POST;
        $data = json_encode($post);
        $data = "[" . date("Y-m-d H:i:s") . "] " . $data . "\n\n";
        file_put_contents(__DIR__ . "/../../../../uploads/diagnostics.log", $data, FILE_APPEND);

        return new JsonResponse(array('succes' => true));
    }

    public function processingDiagnosticsAction(Request $request)
    {
        $videomergerurl = $this->container->getParameter('videomerger.url');
        $notifylink = $this->generateUrl(
            'elearning_course_notify_diagnostics',
            array(),
            true
        );
        $data_to_send = array(
            'data' => array(
                'notifylink' => $notifylink,
                'notifyprogresslink' => $notifylink,
                'chapterid' => -1,
                'diagnostics' => true
            )
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $videomergerurl); # URL to post to
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); # return into a variable
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data_to_send));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch); # run!
        if (!$response) {
            var_dump(curl_error($ch));
        }
        curl_close($ch);

        var_dump($response);


        return new JsonResponse(array('success' => true));
    }

    public function startProcessingAction(Request $request, $course_id)
    {
        $videomergerurl = $this->container->getParameter('videomerger.url');
        $filetoken = $this->container->getParameter('videomerger.token');
        $notifylink = $this->generateUrl(
            'elearning_course_notify_processing',
            array(),
            true
        );
        $notifyprogresslink = $this->generateUrl(
            'elearning_course_notify_processing_progress',
            array(),
            true
        );

        $em = $this->getDoctrine()->getManager();


        /* Finding all deleted chapters (which previously been in state 'edit')
         * All these chapters public versions should be deleted too */
        $deletedchapters = $em->getRepository("ElearningCoursesBundle:Chapter")
            ->findBy(array('course_id' => $course_id, 'state' => 'deleted'));

        foreach ($deletedchapters as $chapter) {
            $oldchapter = $chapter->getOldChapter();
            if (!empty($oldchapter) && $oldchapter->getState() == "published") {
                $oldchapter->setState('publicdeleted');
            }
        }


        $toProcessData = array();


        $selectedchapters = $request->request->get('selectedchapters');


        $chapters = $em->getRepository('ElearningCoursesBundle:Chapter')
            ->findBy(array('course_id' => $course_id, 'state' => array('edit')));

        foreach ($chapters as $chapter) {
            if (!empty($selectedchapters) && !in_array($chapter->getId(), $selectedchapters)) {
                $chapter->setProgress(100);
                $em->flush();
                continue;
            }

            /* This new chapter is created for later editing, old chapter will
             * be published and later (after next processing) marked as deleted
             */

            $oldchapter = $chapter->getOldChapter();
            if (!empty($oldchapter)) {
                $oldchapter->setState('publicdeleted');
            }

            $newchapter = clone $chapter;
            $newchapter->setState('edit');
            $newchapter->setProcessId(null);
            $newchapter->setProgress(null);
            $newchapter->setOldChapter($chapter);
            $newchapter->setDirty(false);
            $em->persist($newchapter);

            $chapter->setProgress(0);
            $chapter->setState('published');

            if ($chapter->getType() == "video") {
                $rep = $em->getRepository('ElearningCoursesBundle:File');
                $instructor_files = $rep->findBy(array('chapter_id' => $chapter->getId(), 'type' => 'instructor'),
                    array('ordering' => 'ASC'));
                $slides_files = $rep->findBy(array('chapter_id' => $chapter->getId(), 'type' => 'slide'),
                    array('ordering' => 'ASC'));

                $leftvideo = array();
                $prevEndTime = 0;
                foreach ($instructor_files as $file) {
                    if (empty($file)) {
                        error_log("ERROR in processing - empty instructor file");
                        /* TODO fix */
                        return new JsonResponse(array(
                            'success' => false,
                            'message' => "ERROR: empty instructor file: ". $chapter->getName()
                        ));
                    }

                    $newfile = clone $file;
                    $newfile->setChapter($newchapter);
                    $em->persist($newfile);

                    $video = $file->getVideo();
                    if (empty($video)) {
                        $video = new Video();
                        $video->setEndtime($prevEndTime + 10 * 1000);
                    }
                    if ($video->getEndtime() < $prevEndTime) {
                        return new JsonResponse(array(
                            'success' => false,
                            'message' => "Invalid chapter files endtimes: ". $chapter->getName()
                        ));
                    }
                    $prevEndTime = $video->getEndtime();

                    $newvideo = clone $video;
                    $newvideo->setFile($newfile);
                    $em->persist($newvideo);


                    $filepath = $this->generateUrl('elearning_courses_show_file', array(
                            'id' => $file->getId()
                        ), true) . "?token=" . $filetoken;
                    /*
                    $video = $file->getVideo();
                    if (empty($video) || ($video->getEndtime() == 0 && count($instructor_files) > 1)) {
                        error_log("ERROR in processing - empty instructor video - " . $file->getId());
                        return new JsonResponse(array(
                            'success' => false,
                            'message' => "ERROR: empty instructor video: ".$file->getId(). " " . $chapter->getName()
                        ));
                        continue;
                    }
                    */
                    $leftvideo[] = array(
                        'filepath' => $filepath,
                        'type' => $file->getFileType(),
                        'endtime' => $video->getEndtime()
                    );
                }
                $rightvideo = array();
                $prevEndTime = 0;
                foreach ($slides_files as $file) {
                    if (empty($file)) {
                        error_log("ERROR in processing - empty slide file");
                        /* TODO fix */
                        return new JsonResponse(array(
                            'success' => false,
                            'message' => "ERROR: empty slide file: ". $chapter->getName()
                        ));
                    }
                    $newfile = clone $file;
                    $newfile->setChapter($newchapter);
                    $em->persist($newfile);

                    $video = $file->getVideo();
                    if (empty($video)) {
                        $video = new Video();
                        $video->setEndtime($prevEndTime + 10 * 1000);
                    }
                    if ($video->getEndtime() < $prevEndTime) {
                        return new JsonResponse(array(
                            'success' => false,
                            'message' => "Invalid chapter files endtimes: ". $chapter->getName()
                        ));
                    }
                    $prevEndTime = $video->getEndtime();
                    $newvideo = clone $video;
                    $newvideo->setFile($newfile);
                    $em->persist($newvideo);

                    $filepath = $this->generateUrl('elearning_courses_show_file', array(
                            'id' => $file->getId()
                        ), true) . '?token=' . $filetoken;
                    /*
                    $video = $file->getVideo();
                    if (empty($video) || ($video->getEndtime() == 0 && count($slides_files) > 1)) {
                        error_log("ERROR in processing - empty slide video - " . $file->getId());
                        return new JsonResponse(array(
                            'success' => false,
                            'message' => "ERROR: empty slide video: ".$file->getId(). " " . $chapter->getName()
                        ));
                    }
                    */
                    $rightvideo[] = array(
                        'filepath' => $filepath,
                        'type' => $file->getFileType(),
                        'endtime' => $video->getEndtime()
                    );
                }
                if (empty($leftvideo) && empty($rightvideo)) {
                    $chapter->setProgress(100);
                    continue;
                }

                $data_to_send = array(
                    'data' => array(
                        'notifylink' => $notifylink,
                        'notifyprogresslink' => $notifyprogresslink,
                        'leftvideo' => $leftvideo,
                        'rightvideo' => $rightvideo,
                        'chapterid' => $chapter->getId()
                    )
                );

                $toProcessData[$chapter->getId()] = $data_to_send;

            } else if ($chapter->getType() == 'video_files') {

                $rep = $em->getRepository('ElearningCoursesBundle:File');

                /* mp4 file */
                $mp4_file = $rep->findOneBy(array('chapter_id' => $chapter->getId(), 'type' => 'instructor'));

                if (empty($mp4_file)) {
                    error_log("ERROR in processing - empty full mp4 file");
                    return new JsonResponse(array(
                        'success' => false,
                        'message' => "ERROR: empty full mp4 file: ". $chapter->getName()
                    ));
                }

                $mp4_filename = 'chapter' . $chapter->getId() . '.mp4';
                $this->saveVideoFile($mp4_file, $chapter, $newchapter, $mp4_filename, 'mp4_full');

                /* mobile file */
                $mobile_file = $rep->findOneBy(array('chapter_id' => $chapter->getId(), 'type' => 'mobile'));
                if ($mobile_file) {
                    $mobile_filename = 'chapter' . $chapter->getId() . '-mobile.mp4';
                    $this->saveVideoFile($mobile_file, $chapter, $newchapter, $mobile_filename, 'mp4_mobile');
                }

                /* webm file */
                $webm_file = $rep->findOneBy(array('chapter_id' => $chapter->getId(), 'type' => 'webm'));
                if ($webm_file) {
                    $webm_filename = 'chapter' . $chapter->getId() . '.webm';
                    $this->saveVideoFile($webm_file, $chapter, $newchapter, $webm_filename, 'webm_full');
                }

                $chapter->setProgress(100);
                $chapter->setState('published');

            } else if ($chapter->getType() == 'quiz') {
                $rep = $em->getRepository('ElearningCoursesBundle:Quiz');
                $oldpublicquiz = $rep->findOneBy(array('chapter_id' => $chapter->getId(), 'version' => 'public'));
                if (!empty($oldpublicquiz)) {
                    $em->remove($oldpublicquiz);
                }
                $quiz = $rep->findOneBy(array('chapter_id' => $chapter->getId(), 'version' => 'edit'));

                if (empty($quiz)) {
                    /* No saved quiz. Moving on */
                    continue;
                }

                $newquiz = clone $quiz;
                $newquiz->setVersion('edit');
                $newquiz->setChapter($newchapter);
                $em->persist($newquiz);
                $quiz->setVersion('public');
                $chapter->setProgress(100);
                $chapter->setState('published');
            } else if ($chapter->getType() == 'lesson') {
                $rep = $em->getRepository('ElearningCoursesBundle:Lesson');
                $oldpubliclesson = $rep->findOneBy(array('chapter_id' => $chapter->getId(), 'version' => 'public'));
                if (!empty($oldpubliclesson)) {
                    $em->remove($oldpubliclesson);
                }
                $lesson = $rep->findOneBy(array('chapter_id' => $chapter->getId(), 'version' => 'edit'));

                if (empty($lesson)) {
                    /* No saved lesson. Moving on */
                    continue;
                }

                $newlesson = clone $lesson;
                $newlesson->setVersion('edit');
                $newlesson->setChapter($newchapter);
                $em->persist($newlesson);
                $lesson->setVersion('public');
                $chapter->setProgress(100);
                $chapter->setState('published');
            } else if ($chapter->getType() == 'exam') {
                $rep = $em->getRepository('ElearningCoursesBundle:Exam');
                $oldpublicexam = $rep->findOneBy(array('chapter_id' => $chapter->getId(), 'version' => 'public'));
                if (!empty($oldpublicexam)) {
                    $oldpublicexam->setVersion('deleted');
                }
                $exam = $rep->findOneBy(array('chapter_id' => $chapter->getId(), 'version' => 'edit'));
                if (empty($exam)) {
                    /* No saved exam. Moving on */
                    continue;
                }

                if ($chapter->getOldId()) {
                    $oldexam = $rep->findOneBy(array('chapter_id' => $chapter->getOldId()));
                    $oldattempts = $oldexam->getAttempts();
                    /** @var ExamAttempt $oldattempt */
                    foreach ($oldattempts as $oldattempt) {
                        if (is_null($oldattempt->getStartTime()) &&
                            is_null($oldattempt->getEndTime()) &&
                            is_null($oldattempt->getPassed()) &&
                            is_null($oldattempt->getResult()) &&
                            is_null($oldattempt->getSpentTime())
                        ) {
                            $em->remove($oldattempt);
                        } else {
                            $oldattempt->setExam($exam);
                            $em->persist($oldattempt);
                        }
                    }

                    $completions = $em->getRepository('ElearningCoursesBundle:CourseCompletion')->findBy(array(
                        'chapter_id' => $chapter->getOldId()
                    ));
                    foreach ($completions as $completion) {
                        $completion->setChapter($chapter);
                        $em->persist($completion);
                    }
                }

                $newexam = clone $exam;
                $newexam->setVersion('edit');
                $newexam->setChapter($newchapter);
                $em->persist($newexam);


                foreach ($exam->getQuestions() as $question) {
                    $newQuestion = clone $question;
                    $newQuestion->setExam($newexam);
                    $em->persist($newQuestion);
                    foreach ($newQuestion->getAnswers() as $answer) {
                        $newAnswer = clone $answer;
                        $newAnswer->setQuestion($newQuestion);
                        $em->persist($newAnswer);
                    }
                }


                $exam->setVersion('public');

                $chapter->setProgress(100);
                $chapter->setState('published');
            } else if ($chapter->getType() == "feedback") {
                $rep = $em->getRepository('ElearningCoursesBundle:Feedback');
                $oldpublicfeedback = $rep->findOneBy(array('chapter_id' => $chapter->getId(), 'version' => 'public'));
                if (!empty($oldpublicfeedback)) {
                    $em->remove($oldpublicfeedback);
                }
                $feedback = $rep->findOneBy(array('chapter_id' => $chapter->getId(), 'version' => 'edit'));
                if (empty($feedback)) {
                    /* No saved feedback. Moving on */
                    continue;
                }

                $newfeedback = clone $feedback;
                $newfeedback->setVersion('edit');
                $newfeedback->setChapter($newchapter);
                $em->persist($newfeedback);
                $feedback->setVersion('public');
                $chapter->setProgress(100);
                $chapter->setState('published');
            } else if ($chapter->getType() == 'material') {
                $rep = $em->getRepository('ElearningCoursesBundle:Material');
                $materials = $rep->findBy(array('chapter_id' => $chapter->getId()));

                foreach ($materials as $material) {
                    /* This one will be edit version */
                    $newmaterial = clone $material;
                    $newmaterial->setChapter($newchapter);
                    $em->persist($newmaterial);

                    $material->copyFileToPublic();
                }

                $chapter->setProgress(100);
                $chapter->setState('published');
            } else if ($chapter->getType() == 'slides') {
                $rep = $em->getRepository('ElearningCoursesBundle:Slide');
                $slides = $rep->findBy(array('chapter_id' => $chapter->getId()));


                if (!empty($slides)) {
                    $em->flush();
                    $directory = pathinfo($slides[count($slides)-1]->getAbsolutePath(), PATHINFO_DIRNAME);
                    $sourcePath = realpath($directory);
                    $basepath = realpath($directory."/..");
                    $destPath = $basepath."/".$newchapter->getId();
                    mkdir($destPath);
                    $files = scandir($sourcePath);
                    foreach ($files as $file) {
                        if (in_array($file, array(".","..", "", "public"))) continue;
                        copy($sourcePath."/".$file, $destPath."/".$file);
                    }
                }

                $directory = null;
                foreach ($slides as $slide) {
                    /* This one will be edit version */
                    $newslide = clone $slide;
                    $newslide->setChapter($newchapter);
                    $em->persist($newslide);

                    $slide->copyFileToPublic();
                }



                $chapter->setProgress(100);
                $chapter->setState('published');
            }

        }


        if (!empty($toProcessData)) {
            foreach ($toProcessData as $chapter_id=>$dataToSend) {

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $videomergerurl); # URL to post to
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); # return into a variable
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($dataToSend));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $response = curl_exec($ch); # run!
                curl_close($ch);

                $responsedata = json_decode($response);
                if (!empty($responsedata) && $responsedata->success) {
                    $chapter = $em->find("ElearningCoursesBundle:Chapter", $chapter_id);
                    $process_id = $responsedata->process_id;
                    $chapter->setProcessId($process_id);
                    $em->persist($chapter);
                }
            }
        }

        if ($this->checkAllChaptersProcessed($course_id)) {
            $this->publishCourse($course_id);
        }

        $em->flush();

        return new JsonResponse(array(
            'success' => true,
        ));
    }

    public function notifyProcessingAction(Request $request)
    {
        $chapterid = $request->request->get('chapterid');
        $processid = $request->request->get('processid');
        $duration = $request->request->get('duration');
        $downloadlink_mp4 = $request->request->get('downloadlink_mp4');
        $downloadlink_webm = $request->request->get('downloadlink_webm');
        $downloadlink_mp4_mobile = $request->request->get('downloadlink_mp4_mobile');
        error_log(json_encode(array("RECEIVED NOTIFICATION:", $downloadlink_mp4, $downloadlink_webm, $downloadlink_mp4_mobile, $duration, $processid, $chapterid)));

        $em = $this->getDoctrine()->getManager();
        $chapter = $em->find("ElearningCoursesBundle:Chapter", $chapterid);
        $chapter->setProgress(100);
        $chapter->setState('published');

        /* Delete'ing old course files */
        foreach ($chapter->getCoursefiles() as $coursefile) {
            $em->remove($coursefile);
        }

        $mp4_location = $this->downloadFile($downloadlink_mp4, $chapter->getCourseId(), $chapter->getId(), "mp4");
        $mp4coursefile = new CourseFile();
        $mp4coursefile->setLocation($mp4_location);
        $mp4coursefile->setType("mp4_full");
        $mp4coursefile->setChapter($chapter);
        $mp4coursefile->setDuration($duration); /* TODO need to fill in this field */
        $em->persist($mp4coursefile);

        $webm_location = $this->downloadFile($downloadlink_webm, $chapter->getCourseId(), $chapter->getId(), "webm");
        $webmcoursefile = new CourseFile();
        $webmcoursefile->setLocation($webm_location);
        $webmcoursefile->setType("webm_full");
        $webmcoursefile->setChapter($chapter);
        $webmcoursefile->setDuration($duration); /* TODO need to fill in this field */
        $em->persist($webmcoursefile);


        $mp4_mobile_location = $this->downloadFile($downloadlink_mp4_mobile, $chapter->getCourseId(), $chapter->getId(), "mp4", "mobile");
        $mp4coursefile = new CourseFile();
        $mp4coursefile->setLocation($mp4_mobile_location);
        $mp4coursefile->setType("mp4_mobile");
        $mp4coursefile->setChapter($chapter);
        $mp4coursefile->setDuration($duration); /* TODO need to fill in this field */
        $em->persist($mp4coursefile);

        if ($this->checkAllChaptersProcessed($chapter->getCourseId())) {
            $this->publishCourse($chapter->getCourseId());
        }

        $em->flush();

        return new JsonResponse(array(
            'success' => true,
        ));
    }

    private function checkAllChaptersProcessed($course_id)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            "SELECT c
             FROM ElearningCoursesBundle:Chapter c
             WHERE c.course_id = :course_id
             AND c.progress != 100
             AND c.state = 'published'")
            ->setParameter('course_id', $course_id);
        $chapters = $query->getResult();
        return empty($chapters);
    }

    private function publishCourse($course_id)
    {
        $em = $this->getDoctrine()->getManager();
        $course = $em->find('ElearningCoursesBundle:Course', $course_id);
        $course->setStatus('published');
        $em->flush();
    }


    private function downloadFile($fileurl, $course_id, $chapter_id, $format, $version = "full")
    {
        set_time_limit(0);
        $savepath = "../uploads/coursevideos/" . $course_id . "/";
        error_log("DOWNLOAD FILE: SAVEPATH: " . $savepath);
        if (!file_exists($savepath) && !mkdir($savepath)) {
            error_log(json_encode(array("file_exists:", file_exists($savepath), " OR !mkdir")));
            return false;
        }
        $fileurl = urldecode($fileurl);
        $versiontext = ($version == "mobile") ? "-mobile" : "";
        $filename = "chapter" . $chapter_id . $versiontext . "." . $format;
        $fp = fopen($savepath . $filename, 'w+');
        error_log("START DOWNLOADING FILE: " . str_replace(" ", "%20", $fileurl));
        $ch = curl_init(str_replace(" ", "%20", $fileurl));//Here is the file we are downloading, replace spaces with %20
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch); // get curl response
        $error = curl_error($ch);
        error_log(json_encode(array("CURL ERROR:", $error)));
        curl_close($ch);
        fclose($fp);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $savepath . $filename);
        finfo_close($finfo);
        /* if text/* - error page downloaded, return false */
        if (strpos($mimetype, "text/") !== FALSE) {
            error_log(json_encode(array("GOT error page", $savepath . $filename)));
            return false;
        }

        return $savepath . $filename;
    }

    public function notifyProcessingProgressAction(Request $request)
    {
        $chapterid = $request->request->get('chapterid');
        $processid = $request->request->get('processid');
        $progress = $request->request->get('progress');
        $em = $this->getDoctrine()->getManager();
        $chapter = $em->find("ElearningCoursesBundle:Chapter", $chapterid);
        $chapter->setProgress($progress);
        $em->persist($chapter);
        $em->flush();

        return new JsonResponse(array(
            'success' => true,
        ));
    }


    public function processingProgressAction($course_id)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            "SELECT c
             FROM ElearningCoursesBundle:Chapter c
             WHERE c.course_id = :course_id
             AND c.state = 'published'")
            ->setParameter('course_id', $course_id);
        $chapters = $query->getResult();
        $totalprogress = 0;
        foreach ($chapters as $chapter) {
            $progress = $chapter->getProgress();
            $totalprogress += !empty($progress) ? $chapter->getProgress() : 0;
        }

        $progress = count($chapters) ? $totalprogress / count($chapters) : 0;

        $end = $this->checkAllChaptersProcessed($course_id);
        if ($end) {
            $this->publishCourse($course_id);
        }

        return new JsonResponse(array(
            'success' => true,
            'progress' => $progress,
            'end' => $end
        ));
    }


    public function deleteChapterAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter_id = $request->query->get('chapter_id');
        $em = $this->getDoctrine()->getManager();
        $chapter = $em->find('ElearningCoursesBundle:Chapter', $chapter_id);
        if (!empty($chapter)) {
            $chapter->setState('deleted');
            $old_chapter = $chapter->getOldChapter();
            if ($old_chapter) {
                $old_chapter->setState('publicdeleted');
            }
            //$em->remove($chapter);
            $em->flush();
            $response = array('success' => true);
        } else {
            $response = array('success' => false);
        }
        return new JsonResponse($response);
    }

    public function renameChapterAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter_id = $request->request->get('chapter_id');
        $name = $request->request->get('name');
        $em = $this->getDoctrine()->getManager();
        $chapter = $em->find('ElearningCoursesBundle:Chapter', $chapter_id);
        if (!empty($chapter)) {
            $chapter->setName($name);
            $em->flush();
            $response = array('success' => true);
        } else {
            $response = array('success' => false);
        }
        return new JsonResponse($response);
    }


    public function markChapterDirtyAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter_id = $request->request->get('chapter_id');

        $em = $this->getDoctrine()->getManager();
        $chapter = $em->find('ElearningCoursesBundle:Chapter', $chapter_id);
        $chapter->setDirty(true);
        $em->flush();

        $response = array('success' => true);

        return new JsonResponse($response);
    }


    public function categoriesAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $params = array();

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository("ElearningCoursesBundle:Category")->findBy(array('parent_id' => null, 'state' => 'published'), array('ordering' => 'ASC'));
        $params['categories'] = $categories;


        $category = new \Elearning\CoursesBundle\Entity\Category();
        $form = $this->createForm(new CategoryType(), $category);
        $params['category_fields_form'] = $form->createView();

        return $this->render(sprintf("ElearningCoursesBundle:CourseLecturer:categories.html.twig"), $params);
    }

    public function categoriesReorderAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $pks = $request->request->get('cid');
        $order = $request->request->get('order');

        foreach ($pks as $i => $pk) {
            if (isset($order[$i])) {
                $category = $em->find('ElearningCoursesBundle:Category', $pk);
                $category->setOrdering($order[$i]);
            }
        }
        $em->flush();

        $response = array('success' => true);
        return new JsonResponse($response);
    }

    public function coursesReorderAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $pks = $request->request->get('cid');
        $order = $request->request->get('order');

        foreach ($pks as $i => $pk) {
            if (isset($order[$i])) {
                $course = $em->find('ElearningCoursesBundle:Course', $pk);
                $course->setOrdering($order[$i]);
            }
        }
        $em->flush();

        $response = array('success' => true);
        return new JsonResponse($response);
    }

    public function categoriesStoreAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();

        $category_id = $request->request->get('category_id');

        $category = empty($category_id)
            ? new \Elearning\CoursesBundle\Entity\Category()
            : $em->find("ElearningCoursesBundle:Category", $category_id);

        if (empty($category_id)) {
            $reqcategory = $request->request->get('category');
            $parent_id = $reqcategory['parent'];
            $qb = $em->createQueryBuilder()
                ->select('MAX(c.ordering)')
                ->from("ElearningCoursesBundle:Category", 'c');
            if ($parent_id) {
                $qb->where('c.parent_id = :parent_id')
                    ->setParameter('parent_id', $parent_id ? $parent_id : null);
            } else {
                $qb->where('c.parent_id IS NULL');
            }
            $ordering = $qb->getQuery()
                ->getSingleScalarResult();

            $category->setOrdering($ordering + 1);
            $category->setState('published');
        }

        $form = $this->createForm(new CategoryType(), $category);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($category);
            $em->flush();
            return new JsonResponse(array('success' => true, 'id' => $category->getId()));
        }
        $errormessage = $form->getErrors()->current()->getMessage();
        return new JsonResponse(array('success' => false, 'message' => $errormessage));
    }

    public function categoriesDeleteAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $category_id = $request->request->get('category_id');

        $category = $em->find('ElearningCoursesBundle:Category', $category_id);
        $category->setState('deleted');

        $em->flush();

        return new JsonResponse(array('success' => true));
    }

    protected function saveVideoFile ($file, $chapter, $newchapter, $filename, $type)
    {
        $coursefile_location = '../uploads/coursevideos/' . $chapter->getCourseId() . '/';
        if (!file_exists($coursefile_location ) && !mkdir($coursefile_location )) {
            error_log(json_encode(array("file_exists:", file_exists($coursefile_location ), " OR !mkdir")));
            return false;
        }
        $em = $this->getDoctrine()->getManager();

        $getID3 = new GetId3;
        $file_info = $getID3->analyze($file->getAbsolutePath());

        copy($file->getAbsolutePath(), $coursefile_location . $filename);

        $newfile = clone $file;
        $newfile->setChapter($newchapter);
        $em->persist($newfile);

        $video = $file->getVideo();
        if (empty($video)) {
            $video = new Video();
            $video->setEndtime(round($file_info['playtime_seconds']));
        }
        
        $newvideo = clone $video;
        $newvideo->setFile($newfile);
        $em->persist($newvideo);

        $coursefile = new CourseFile();
        $coursefile->setLocation($coursefile_location . $filename);
        $coursefile->setType($type);
        $coursefile->setChapter($chapter);
        $coursefile->setDuration(round($file_info['playtime_seconds']));
        $em->persist($coursefile);
    }

}
