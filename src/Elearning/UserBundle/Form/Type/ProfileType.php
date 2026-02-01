<?php

namespace Elearning\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\UserBundle\Form\Type\ProfileType as BaseType;

class ProfileType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('locale')
            ->remove('timezone')
            ->remove('website')
            ->remove('biography')
            ->remove('phone')
            ->remove('dateOfBirth')
            ->remove('gender')
            ->remove('firstname')
            ->remove('lastname')
            ->add('email', 'email', array(
                'label' => 'form.label_email',
                'required' => false
            ))
            ->add('birthday', 'date', array(
                "label" => "form.label_date_of_birth",
                "widget" => "single_text",
                "format" => "yyyy-MM-dd",
                "attr" => array("class" => "form-control"),
            ));
    }

    public function getName()
    {
        return "elearning_user_profile";
    }

}
