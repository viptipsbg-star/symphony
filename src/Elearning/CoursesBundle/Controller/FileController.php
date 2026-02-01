<?php

namespace Elearning\CoursesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use Elearning\CoursesBundle\Entity\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{

    /* Handles course file uploads */
    /* POST route */
    public function uploadAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $uploaded_file = $request->files->get('file');
        if (empty($uploaded_file)) {
            $response = array('success'=>false, 'message'=>"Error");
            return new JsonResponse($response);
        }

        if ($uploaded_file->getError() != UPLOAD_ERR_OK) {
            switch($uploaded_file->getError()) {
                case UPLOAD_ERR_INI_SIZE:
                    $message = "Uploaded file exceeds upload_max_filesize in php.ini";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = "The uploaded file was only partially uploaded.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = "No file was uploaded.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = "Missing a temporary folder.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = "Failed to write file to disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $message = "A PHP extension stopped the file upload.";
                    break;
                default:
                    $message = "Unknown error: " . $uploaded_file->getError();
            }
            $response = array('success'=>false, 'message'=>$message);
            return new JsonResponse($response);
        }

        $type = $request->request->get('type');
        $chapter_id = $request->request->get('chapter_id');
        $ordering = $request->request->get('ordering');

        $extension = $uploaded_file->guessExtension();
        $mimetype = $uploaded_file->getMimeType();

        $translator = $this->get('translator');
        if (strpos($mimetype, "video") !== FALSE && !in_array($extension, array("mp4", "webm"))) {
            $message = $translator->trans("new_course.editor.upload.video_format_error");
            $response = array('success'=>false, 'message'=>$message);
            return new JsonResponse($response);
        }
        else if (strpos($mimetype, "audio") !== FALSE && !in_array($extension, array('mp3'))) {
            $message = $translator->trans("new_course.editor.upload.audio_format_error");
            $response = array('success'=>false, 'message'=>$message);
            return new JsonResponse($response);
        }



        if ($extension == "pdf") {
            $files = $this->storePdfFile($uploaded_file, $chapter_id, $type);
        } else {
            $result = $this->storeFile($uploaded_file, $chapter_id, $type, $ordering);

            if (!$result) {
                return new JsonResponse(array('success' => false));
            }
            list($location, $thumbnailUrl, $newid, $name, $size) = $result;
            $files = array(
                array(
                    'location' => $location,
                    'thumbnail' => $thumbnailUrl,
                    'id' => $newid,
                    'name' => $name,
                    'size' => $size
                )
            );
        }
        return new JsonResponse(array('success' => true, 'type' => $type, 'files' => $files));
    }

    private function storePdfFile($uploadedFile, $chapter_id, $type)
    {
        $abspath = __DIR__ . "/../../../../";
        $dirpath = "uploads/coursefiles/".$chapter_id."/";
        if (!file_exists($abspath.$dirpath)) {
            mkdir($abspath.$dirpath);
        }
        $em = $this->getDoctrine()->getManager();

        $original_filename = $uploadedFile->getClientOriginalName();
        $filebase = str_replace(".", "", uniqid($type, true));

        $realpath = $uploadedFile->getRealPath();
        $im = new \Imagick($realpath);

        $numberOfImages = $im->getNumberImages();

        $images = array();

        for ($i = 0; $i < $numberOfImages; $i++) {
            $filepath = $abspath . $dirpath;
            $filename = $filebase . "_" . $i . "." . "png";
            $im->setResolution(150, 150);
            $im->readImage($realpath . '[' . $i . ']');
            $im->resetIterator();
            $im->setImageFormat('png');
            $im->setImageMatte(true);
            $im->setImageMatteColor('white');
            $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_OPAQUE);
            $im->writeImage($filepath . $filename);

            /* TODO move to config */
            $THUMBNAIL_WIDTH = 120;
            $THUMBNAIL_HEIGHT = 120;

            $im->cropThumbnailImage($THUMBNAIL_WIDTH, $THUMBNAIL_HEIGHT);
            $im->writeImage($abspath . $dirpath . $filebase . "_" . $i . "_thumb." . 'png');
            $im->clear();

            $ordering = $this->getLastOrdering($chapter_id, $type) + 1;
            $file = new File();
            $file->setLocation($dirpath . $filename);
            $file->setOriginalFilename($original_filename);
            $file->setType($type);
            $chapter = $em->find("ElearningCoursesBundle:Chapter", $chapter_id);
            $file->setChapter($chapter);
            $file->setOrdering($ordering);
            $em->persist($file);
            $em->flush();
            $newid = $file->getId();
            $thumbailUrl = $this->generateUrl(
                'elearning_courses_show_file_thumbnail',
                array('id' => $file->getId())
            );
            $images[] = array(
                'location' => $dirpath . $filename,
                'thumbnail' => $thumbailUrl,
                'id' => $newid,
                'name' => $original_filename,
                'size' => $file->getFileSize()
            );
        }
        return $images;
    }

    private function storeFile($uploadedFile, $chapter_id, $type, $ordering)
    {
        if (empty($ordering)) {
            $ordering = $this->getLastOrdering($chapter_id, $type) + 1;
        }

        $original_filename = $uploadedFile->getClientOriginalName();
        $ext = pathinfo($original_filename, PATHINFO_EXTENSION);
        $filename = str_replace(".", "", uniqid($type, true)) . "." . $ext;

        $file = new File();
        $file->setLocation($filename);
        $file->setOriginalFilename($original_filename);
        $file->setType($type);
        $file->setFile($uploadedFile);
        $file->setChapterId($chapter_id);
        $file->setOrdering($ordering);

        $chapter = $this->getDoctrine()
            ->getRepository('ElearningCoursesBundle:Chapter')
            ->find($chapter_id);
        $file->setChapter($chapter);


        if (true) { /* TODO make validation of files and data */
            $em = $this->getDoctrine()->getManager();
            $file->upload();

            $em->persist($file);
            $em->flush();
            $newid = $file->getId();
            $thumbailUrl = $this->generateUrl(
                'elearning_courses_show_file_thumbnail',
                array('id' => $file->getId())
            );
            $size = $file->getFileSize();
            return array($file->getLocation(), $thumbailUrl, $newid, $original_filename, $size);
        }
        return false;
    }

    public function deleteAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $id = $request->request->get('id');

        $em = $this->getDoctrine()->getManager();

        $file = $em->getRepository('ElearningCoursesBundle:File')
            ->find($id);

        if (!$file) {
            throw $this->createNotFoundException(
                'No file found for id: ' . $id
            );
        }
        $type = $file->getType();
        $chapter_id = $file->getChapterId();
        $em->remove($file);
        $em->flush();
        $this->reorderFiles($chapter_id, $type);
        return new JsonResponse(array('success' => true));
    }

    private function reorderFiles($chapter_id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $files = $em->getRepository('ElearningCoursesBundle:File')
            ->findBy(array('chapter_id' => $chapter_id, 'type' => $type),
                array('ordering' => 'ASC'));
        $ordering = 1;
        foreach ($files as $file) {
            $file->setOrdering($ordering++);
        }
        $em->flush();
    }

    public function changeOrderAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $file_id = $request->request->get('file_id');
        $direction = $request->request->get('direction');
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('ElearningCoursesBundle:File');
        $file = $rep->find($file_id);
        $old_ordering = $file->getOrdering();
        $chapter_id = $file->getChapterId();
        $type = $file->getType();
        if ($direction == 'left') {
            $new_ordering = $old_ordering - 1;
        } else {
            $new_ordering = $old_ordering + 1;
        }
        $file->setOrdering($new_ordering);

        $res = $rep->findBy(array('chapter_id' => $chapter_id, 'type' => $type, 'ordering' => $new_ordering));
        if (!empty($res)) {
            $next_file = $res[0];
            $next_file->setOrdering($old_ordering);
        }
        $em->flush();
        $this->reorderFiles($chapter_id, $type);
        return new JsonResponse(array('success' => true));
    }

    public function showAction(Request $request, $id)
    {
        $realtoken = "RhnO8jbf5Jmmk5bnvNHy73Mkixx0Ohkr"; /* TODO: MOVE TO CONFIG */
        $secrettoken = $request->query->get('token');
        if (empty($secrettoken) || $secrettoken != $realtoken) {
            $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');
        }

        /* TODO check if allowed to show image */
        $file_entity = $this->getDoctrine()
            ->getRepository('ElearningCoursesBundle:File')
            ->find($id);

        if (!$file_entity || !is_file($file_entity->getAbsolutePath())) {
            throw $this->createNotFoundException(
                'No file found for id: ' . $id
            );
        }


        $path = $file_entity->getAbsolutePath();

        $stream = $this->get('file_stream');
        $stream->init($path)->start();
        exit();
    }

    public function showThumbnailAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        /* TODO check if allowed to show image */
        $file = $this->getDoctrine()
            ->getRepository('ElearningCoursesBundle:File')
            ->find($id);

        if (!$file) {
            throw $this->createNotFoundException(
                'No file found for id: ' . $id
            );
        }

        $mimetype = $file->getMimeType();

        $path = $file->getAbsoluteThumbnailPath();
        if (strpos($mimetype, "video") !== false) {
            $path = $file->getAbsoluteVideoThumbnailPath();
            $mimetype = "image/png";
        } elseif (strpos($mimetype, "audio") !== false) {
            $path = $file->getAbsoluteAudioThumbnailPath();
            $mimetype = "image/png";
        }

        $response = new Response();
        $response->headers->set('Content-Type', $mimetype);
        $response->headers->set('Content-Disposition', 'inline; filename="' . $file->getOriginalFilename().'"');
        $response->sendHeaders();
        readfile($path);
        exit();
    }


    protected function getLastOrdering($chapter_id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            "SELECT f.ordering
             FROM ElearningCoursesBundle:File f
             WHERE f.chapter_id = :chapter_id
             AND f.type = :type
             ORDER BY f.ordering DESC")
            ->setParameter('chapter_id', $chapter_id)
            ->setParameter('type', $type);
        $query->setMaxResults(1);
        $result = $query->getScalarResult();
        $ordering = empty($result) ? 0 : $result[0]['ordering'];
        return (int)$ordering;
    }


    public function getChapterFilesAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter_id = $request->query->get('chapter_id');
        $type = $request->query->get('type');
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            "SELECT f
             FROM ElearningCoursesBundle:File f
             WHERE f.chapter_id = :chapter_id
             AND f.type = :type
             ORDER BY f.ordering ASC")
            ->setParameter('chapter_id', $chapter_id)
            ->setParameter('type', $type);
        $files = $query->getResult();
        $resp = array();
        foreach ($files as $file) {
            $url = $this->generateUrl(
                'elearning_courses_show_file_thumbnail',
                array('id' => $file->getId())
            );
            $resp[] = array(
                'name' => $file->getOriginalFilename(),
                'size' => $file->getFileSize(),
                'path' => $url,
                'id' => $file->getId()
            );
        }

        return new JsonResponse(array('success' => true, 'files' => $resp));
    }

    public function getAllChapterFilesAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter_id = $request->query->get('chapter_id');

        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('ElearningCoursesBundle:File');
        $instructor_files = $rep->findBy(array('chapter_id' => $chapter_id, 'type' => 'instructor'),
            array('ordering' => 'ASC'));
        $slides_files = $rep->findBy(array('chapter_id' => $chapter_id, 'type' => 'slide'),
            array('ordering' => 'ASC'));


        $instructor = $this->prepareFiles($instructor_files);
        $slides = $this->prepareFiles($slides_files);

        $query = $em->createQuery(
            "SELECT v.file_id, v.endtime, f.type
             FROM ElearningCoursesBundle:Video v
             JOIN v.file f
             WHERE f.chapter_id = :chapter_id
             ORDER BY f.ordering ASC")
            ->setParameter('chapter_id', $chapter_id);
        $times_items = $query->getResult();

        $times = array('mainvideo' => array(), 'slidesvideo' => array());
        $maxtime = 0;
        foreach ($times_items as $item) {
            $type = ($item['type'] == 'instructor') ? 'mainvideo' : 'slidesvideo';
            $times[$type][$item['file_id']] = $item['endtime'];
            if ($item['endtime'] > $maxtime) {
                $maxtime = $item['endtime'];
            }
        }
        $times['videoduration'] = $maxtime;

        return new JsonResponse(array('success' => true,
            'instructor' => $instructor,
            'slides' => $slides,
            'times' => $times));
    }


    private function prepareFiles($entities)
    {
        $files = array();
        foreach ($entities as $file) {
            $mimetype = $file->getMimeType();
            $type = "";
            if (strpos($mimetype, "video") !== false) {
                $type = "video";
            } else if (strpos($mimetype, 'image') !== false) {
                $type = "image";
            } else if (strpos($mimetype, 'audio') !== false) {
                $type = "audio";
            }
            $files[] = array(
                'filename' => $file->getOriginalFilename(),
                'id' => $file->getId(),
                'type' => $type,
                'path' => $this->get('router')
                    ->generate('elearning_courses_show_file', array(
                        'id' => $file->getId()
                    ))
            );
        }
        return $files;
    }


    public function showCourseFileAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        /* TODO check if allowed to show course file (if user is enrolled) */
        $em = $this->getDoctrine()->getManager();
        $file = $em->find("ElearningCoursesBundle:CourseFile", $id);

        if (!$file || !is_file($file->getAbsolutePath())) {
            throw $this->createNotFoundException(
                'No file found for id: ' . $id
            );
        }
        
        $path = $file->getAbsolutePath();
        return new BinaryFileResponse($path);
    }


    public function moveCourseFilesToNewLocationAction()
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        $files = $em->getRepository("ElearningCoursesBundle:File")->findAll();
        foreach ($files as $file) {
            $file->moveFileToNewLocation();
        }
        $em->flush();
        return new JsonResponse(array('success'=>true));
    }

}
