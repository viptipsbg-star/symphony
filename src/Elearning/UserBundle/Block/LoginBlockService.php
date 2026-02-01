<?php

namespace Elearning\UserBundle\Block;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BaseBlockService;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LoginBlockService extends BaseBlockService
{

    protected $authorizationChecker;

    public function __construct($name, EngineInterface $templating, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($name, $templating);
        $this->authorizationChecker = $authorizationChecker;
    }

    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'template' => 'ElearningUserBundle:Block:block_login.html.twig',
        ));
    }

    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('content', 'text', array('required' => false))
            )
        ));
    }


    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        $errorElement
            ->with('settings.content')
            ->assertNotNull(array())
            ->assertNotBlank()
            ->assertMaxLength(array('limit' => 50))
            ->end();
    }


    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        // merge settings
        $settings = $blockContext->getSettings();

        $userIsLoggedin = ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'));

        return $this->renderResponse($blockContext->getTemplate(), array(
            'block' => $blockContext->getBlock(),
            'userIsLoggedin' => $userIsLoggedin,
            'settings' => $settings
        ), $response);
    }
}
