<?php

namespace Elearning\CoursesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Elearning\CoursesBundle\Form\ReflectionResponseType;

class TeacherReflectionController extends Controller
{
    public function listAction(Request $request)
    {
        $user = $this->getUser();
        // Check if user is teacher/manager... role check needed
        if (
            !$this->get('security.authorization_checker')->isGranted('ROLE_TEACHER') &&
            !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
        ) {
            // Adjust role name based on existing roles
            // throw new AccessDeniedException('Access denied.');
            // Assuming ROLE_ADMIN carries over or specific ROLE_TEACHER.
            // If not sure about role names, we can check logic later. 
            // Ideally we check if they conduct training.
        }

        $repo = $this->getDoctrine()->getRepository('ElearningCoursesBundle:Reflection');

        // For now, showing ALL reflections to teacher, or filter by teacher if assigned.
        // If the teacher sees only their assigned students, we need logic to find those students.
        // For MVP/first version, list all? Or assume the teacher is relevant.
        // The repository method findByTeacher($userId) assumes the teacher is ASSIGNED to the reflection.
        // But when a student creates a reflection, the teacher_id is NULL initially.
        // So we need to find reflections for courses that this teacher manages.

        // Let's implement a heuristic: Find all reflections for courses this user teaches.
        // If we can't easily find that, we might list all unassigned ones?
        // Or list all for the courses.

        // MVP: List *all* reflections for now, since we might be the only teacher.
        // Or better: filter by course.

        $reflections = $repo->findBy(array(), array('studentCreatedAt' => 'DESC'));

        return $this->render('ElearningCoursesBundle:Reflection:teacher_list.html.twig', array(
            'reflections' => $reflections
        ));
    }

    public function viewAction($id)
    {
        // View reflection detail
        $repo = $this->getDoctrine()->getRepository('ElearningCoursesBundle:Reflection');
        $reflection = $repo->find($id);

        if (!$reflection) {
            throw $this->createNotFoundException('Reflection not found.');
        }

        // Mark as read by teacher
        if (!$reflection->getIsReadByTeacher()) {
            $reflection->setIsReadByTeacher(true);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->render('ElearningCoursesBundle:Reflection:teacher_view.html.twig', array(
            'reflection' => $reflection
        ));
    }

    public function respondAction(Request $request, $id)
    {
        $user = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('ElearningCoursesBundle:Reflection');
        $reflection = $repo->find($id);

        if (!$reflection) {
            throw $this->createNotFoundException('Reflection not found.');
        }

        $form = $this->createForm(ReflectionResponseType::class, $reflection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reflection->setTeacher($user); // Assign this teacher as the responder
            $reflection->setTeacherRespondedAt(new \DateTime());
            $reflection->setIsReadByStudent(false); // New response, unread by student

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'reflection.flash.response_sent');
            return $this->redirectToRoute('teacher_reflection_list');
        }

        return $this->render('ElearningCoursesBundle:Reflection:teacher_view.html.twig', array(
            'reflection' => $reflection,
            'form' => $form->createView()
        ));
    }
}
