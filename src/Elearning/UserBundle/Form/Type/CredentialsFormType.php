<?php

namespace Elearning\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CredentialsFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('email', 'email', array(
                'translation_domain' => 'FOSUserBundle',
                'label' => "credentials.email"
            ))
            ->add('new', 'repeated', array(
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'credentials.new_password'),
                'second_options' => array('label' => 'credentials.new_password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Elearning\UserBundle\Form\Model\Credentials',
            'intention' => 'credentials',
        ));
    }

    public function getName()
    {
        return "elearning_user_credentials";
    }

}
