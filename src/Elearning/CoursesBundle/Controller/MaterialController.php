<?php

namespace Elearning\CoursesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elearning\CoursesBundle\Entity\Material;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class MaterialController extends Controller
{

    public function getAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter_id = $request->query->get('chapter_id');
        $em = $this->getDoctrine()->getManager();
        $materials = $em->getRepository('ElearningCoursesBundle:Material')
            ->findBy(array('chapter_id' => $chapter_id));
        $files = array();
        if ($materials) {
            foreach ($materials as $material) {
                $link = $this->generateUrl(
                    'elearning_courses_material_file',
                    array('material_id' => $material->getId()),
                    true
                );
                $files[] = array('id' => $material->getId(), 'title' => $material->getTitle(), 'link' => $link);
            }
        }
        return new JsonResponse(array('success' => true, 'files' => $files));
    }

    public function saveAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $chapter_id = $request->request->get('chapter_id');
        $ids = $request->request->get('material_id');

        $em = $this->getDoctrine()->getManager();
        $allmaterials = $em->getRepository('ElearningCoursesBundle:Material')
            ->findBy(array('chapter_id' => $chapter_id));

        foreach ($allmaterials as $material) {
            if (!in_array($material->getId(), $ids)) {
                $em->remove($material);
            }
        }

        $titles = $request->request->get('title');
        $files = $request->files->get('file');

        $materials = array();

        foreach ($titles as $key => $title) {
            $isnew = true;
            if (isset($ids[$key]) && !empty($ids[$key])) {
                $material = $em->find("ElearningCoursesBundle:Material", $ids[$key]);
                $isnew = false;
            }
            else {
                $material = new Material();
            }
            $material->setChapter($em->getReference('ElearningCoursesBundle:Chapter', $chapter_id));
            $material->setTitle($title);
            $material->setFile($files[$key]);
            $material->setOrdering($key);
            if ($isnew) {
                $result = $material->upload();
                if (!$result) {
                    $translator = $this->get('translator');
                    $message = $translator->trans('new_course.editor.material.error_saving_files');
                    return new JsonResponse(array('success' => false, 'message' => $message));
                }
            }
            $em->persist($material);
            $materials[] = $material;
        }

        $em->flush();

        $allmaterials = $em->getRepository('ElearningCoursesBundle:Material')
            ->findBy(array('chapter_id' => $chapter_id));
        $materialIds = array();
        foreach ($allmaterials as $material) {
            $materialIds[] = $material->getId();
        }
        return new JsonResponse(array('success' => true, 'ids' => $materialIds));
    }

    /**
     * @param Request $request
     * @param $material_id
     * @param null $filename
     * @Security("has_role('ROLE_STUDENT') or has_role('ROLE_LECTURER')")
     */
    public function showFileAction(Request $request, $material_id, $filename = null)
    {
        $em = $this->getDoctrine()->getManager();



        $file = $em
            ->getRepository('ElearningCoursesBundle:Material')
            ->find($material_id);

        if (!$file || !is_file($file->getAbsolutePath())) {
            throw $this->createNotFoundException(
                'No file found for id: ' . $material_id
            );
        }

        if (empty($filename)) {
            $filename = $file->getFilename();
            return $this->redirectToRoute("elearning_courses_material_file", array('material_id' => $material_id, 'filename' => $filename));
        }


        $path = $file->getAbsolutePath();

        $stream = $this->get('file_stream');
        $stream->init($path)->start();
        exit();
    }


    public function deleteAction(Request $request, $material_id)
    {
        $this->denyAccessUnlessGranted('ROLE_LECTURER', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $material = $em->find("ElearningCoursesBundle:Material", $material_id);

        if (!$material) {
            throw $this->createNotFoundException(
                'No file found for id: ' . $material_id
            );
        }

        $em->remove($material);
        $em->flush();
        return new JsonResponse(array('success'=>true));
    }

}
