<?php
// Reflections Feature - Student Controller v1.0

namespace Elearning\CoursesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Elearning\CoursesBundle\Entity\Reflection;
use Elearning\CoursesBundle\Form\ReflectionType;

class StudentReflectionController extends Controller
{
    public function listAction()
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You must be logged in to view reflections.');
        }

        $repo = $this->getDoctrine()->getRepository('ElearningCoursesBundle:Reflection');
        $reflections = $repo->findByStudent($user->getId());

        // Find unread responses to mark them as read if needed? 
        // Or show them as unread. Let's just list them.

        return $this->render('ElearningCoursesBundle:Reflection:student_list.html.twig', array(
            'reflections' => $reflections
        ));
    }

    public function createAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You must be logged in to create a reflection.');
        }

        $reflection = new Reflection();
        $reflection->setStudent($user);

        $form = $this->createForm(ReflectionType::class, $reflection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($reflection);
            $em->flush();

            $this->addFlash('success', 'reflection.flash.created');
            return $this->redirectToRoute('student_reflection_list');
        }

        return $this->render('ElearningCoursesBundle:Reflection:student_create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function viewAction($id)
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You must be logged in.');
        }

        $repo = $this->getDoctrine()->getRepository('ElearningCoursesBundle:Reflection');
        $reflection = $repo->find($id);

        if (!$reflection) {
            throw $this->createNotFoundException('Reflection not found.');
        }

        if ($reflection->getStudent()->getId() !== $user->getId()) {
            throw new AccessDeniedException('You do not have permission to view this reflection.');
        }

        // Mark as read by student if it has a teacher response and wasn't read
        if ($reflection->getTeacherResponse() && !$reflection->getIsReadByStudent()) {
            $reflection->setIsReadByStudent(true);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->render('ElearningCoursesBundle:Reflection:student_view.html.twig', array(
            'reflection' => $reflection
        ));
    }
}
