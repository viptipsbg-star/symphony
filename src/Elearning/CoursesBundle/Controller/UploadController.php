<?php

namespace Elearning\CoursesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use Elearning\CoursesBundle\Entity\Upload;

class UploadController extends Controller
{

    /* Handles course file uploads */
    /* POST route */
    public function uploadAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $file = new Upload();

        $uploaded_file = $request->files->get('file');
        $original_filename = $uploaded_file->getClientOriginalName();
        $course_id = $request->request->get('course_id');

        $ext = pathinfo($original_filename, PATHINFO_EXTENSION);
        $filename = str_replace(".", "", uniqid("", true)).".".$ext;
        $file->setLocation($filename);

        $file->setOriginalFilename($original_filename);
        $file->setFile($uploaded_file);
        $file->setCourse($em->getReference("ElearningCoursesBundle:Course", $course_id));

        if ( true ) { /* TODO make validation of files and data */
            $em = $this->getDoctrine()->getManager();
            $file->upload();

            $em->persist($file);
            $em->flush();
            $newid = $file->getId();
            $fileurl = $this->generateUrl(
                'elearning_courses_show_course_file',
                array('id' => $file->getId())
            );
            return new JsonResponse(array('success' => true, 'filename' => $file->getOriginalFilename(), 'filesize'=>$file->getFilesize(), 'url'=>$fileurl, 'id' => $newid));
        }
        return new JsonResponse(array('success' => false));
    }

    public function deleteAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $id = $request->request->get('id');

        $em = $this->getDoctrine()->getManager();

        $file = $em->find('ElearningCoursesBundle:Upload', $id);

        if (!$file) {
            throw $this->createNotFoundException(
                'No file found for id: '.$id
            );
        }
        $em->remove($file);
        $em->flush();
        return new JsonResponse(array('success' => true));
    }

    public function showAction(Request $request, $id) {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $file_entity = $this->getDoctrine()->getManager()
            ->find('ElearningCoursesBundle:Upload', $id);

        if (!$file_entity || !is_file($file_entity->getAbsolutePath())) {
            throw $this->createNotFoundException(
                'No file found for id: '.$id
            );
        }

        $path = $file_entity->getAbsolutePath();

        $stream = $this->get('file_stream');
        $stream->init($path)->start();
        exit();
    }

    /* TODO need to refactor. Not DRY */
    public function showStudentAction(Request $request, $id) {
        $this->denyAccessUnlessGranted('ROLE_STUDENT', null, 'Unable to access this page!');

        $file_entity = $this->getDoctrine()->getManager()
            ->find('ElearningCoursesBundle:Upload', $id);

        if (!$file_entity || !is_file($file_entity->getAbsolutePath())) {
            throw $this->createNotFoundException(
                'No file found for id: '.$id
            );
        }

        $path = $file_entity->getAbsolutePath();

        $stream = $this->get('file_stream');
        $stream->init($path)->start();
        exit();
    }


}
