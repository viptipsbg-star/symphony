<?php

namespace Elearning\UserBundle\Controller;

use Sonata\UserBundle\Controller\ProfileFOSUser1Controller as BaseController;

class ProfileFOSUser1Controller extends BaseController

{

    /**
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function editProfileAction()
    {
        $response = parent::editProfileAction();
        $form = $this->container->get('sonata.user.profile.form');
        return $this->render('SonataUserBundle:Profile:edit_profile.html.twig', array(
            'form'               => $form->createView(),
            'breadcrumb_context' => 'user_profile',
        ));
    }

}
