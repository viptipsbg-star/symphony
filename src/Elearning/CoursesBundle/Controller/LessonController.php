<?php

namespace Elearning\CoursesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elearning\CoursesBundle\Entity\Lesson;
use Elearning\CoursesBundle\Form\LessonType;

class LessonController extends Controller
{

    public function getAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter_id = $request->query->get('chapter_id');
        $em = $this->getDoctrine()->getManager();
        $lesson = $em->getRepository('ElearningCoursesBundle:Lesson')
                    ->findOneBy(array('chapter_id'=>$chapter_id));
        if ($lesson) {
            return new JsonResponse(array('success' => true, 'lesson' => array(
                'id'=>$lesson->getId(),
                'content'=>$lesson->getContent()
            )));
        }
        return new JsonResponse(array('success'=>false));
    }

    public function saveAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');

        $lesson = ($id)
            ? $em->getRepository('ElearningCoursesBundle:Lesson')->find($id)
            : new Lesson();
        $form = $this->createForm(new LessonType(), $lesson);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $lesson->setChapter($em->getReference('ElearningCoursesBundle:Chapter', $lesson->getChapterId()));
            $em->persist($lesson);
            $em->flush();
            return new JsonResponse(array('success'=>true, 'id'=>$lesson->getId()));
        }
        return new JsonResponse(array('success'=>false));

    }

}
