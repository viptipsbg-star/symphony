<?php

namespace Elearning\CoursesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elearning\CoursesBundle\Entity\Quiz;

class QuizController extends Controller
{

    public function getAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter_id = $request->query->get('chapter_id');
        $em = $this->getDoctrine()->getManager();
        $quiz = $em->getRepository('ElearningCoursesBundle:Quiz')
                    ->findOneBy(array('chapter_id'=>$chapter_id));
        if ($quiz) {
            if ($quiz->getType() == "connect") {
                $data = $quiz->getData();
                $root_path = __DIR__."/../../../../";
                foreach ($data['choices'] as &$choice) {
                    if ($choice['first']['type'] == "image") {
                        $path = $root_path.$choice['first']['value'];
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $imgdata = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($imgdata);
                        $choice['first']['value'] = $base64;
                    }
                    if ($choice['second']['type'] == "image") {
                        $path = $root_path.$choice['second']['value'];
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $imgdata = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($imgdata);
                        $choice['second']['value'] = $base64;
                    }
                }
                $quiz->setData($data);
            }
            return new JsonResponse(array('success' => true, 'quiz' => array(
                'id'=>$quiz->getId(),
                'type'=>$quiz->getType(),
                'data'=>$quiz->getData()
            )));
        }
        return new JsonResponse(array('success'=>false));
    }

    public function saveAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');
        $chapter_id = $request->request->get('chapter_id');
        $type = $request->request->get('type');
        $data = $request->request->get('data');

        $images_path = "uploads/quizfiles/";
        $root_path = __DIR__."/../../../../";

        $quiz = ($id)
            ? $em->getRepository('ElearningCoursesBundle:Quiz')->find($id)
            : new Quiz();

        if ($quiz->getType() == "connect") {
            $old_data = $quiz->getData();
            if (!empty($old_data)) {
                foreach ($old_data['choices'] as $choice) {
                    if ($choice['first']['type'] == "image") {
                        $file = $root_path.$choice['first']['value'];
                        unlink($file);
                    }
                    if ($choice['second']['type'] == "image") {
                        $file = $root_path.$choice['second']['value'];
                        unlink($file);
                    }
                }
            }
        }

        if ($type == "connect") {
            foreach ($data['choices'] as &$choice) {
                if ($choice['first']['type'] == "image") {
                    $base64img = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $choice['first']['value']));
                    $filename = str_replace(".", "", uniqid("connectimage_", true)).".png";
                    imagepng(imagecreatefromstring($base64img), $root_path.$images_path.$filename);
                    $choice['first']['value'] = $images_path.$filename;
                }
                if ($choice['second']['type'] == "image") {
                    $base64img = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $choice['second']['value']));
                    $filename = str_replace(".", "", uniqid("connectimage_", true)).".png";
                    imagepng(imagecreatefromstring($base64img), $root_path.$images_path.$filename);
                    $choice['second']['value'] = $images_path.$filename;
                }
            }
        }

        $quiz->setChapter($em->getReference('ElearningCoursesBundle:Chapter', $chapter_id));
        $quiz->setType($type);
        $quiz->setData($data);
        $em->persist($quiz);
        $em->flush();
        return new JsonResponse(array('success'=>true, 'id'=>$quiz->getId()));
    }

}
