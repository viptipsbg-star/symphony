<?php

namespace Elearning\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $system_id = $this->container->getParameter('system_id');
        $template = "frontpage_$system_id.html.twig";
        $default_template = "frontpage.html.twig";

        if (!$this->get('templating')->exists($template)) {
            $template = $default_template;
        }
        return $this->render('ElearningMainBundle:Default:index.html.twig', array('template' => $template));
    }
}
