<?php

namespace Elearning\CoursesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => "categories.form.name"
            ))
            ->add('parent', 'entity', array(
                'class' => 'ElearningCoursesBundle:Category',
                'choice_label' => 'name',
                'placeholder' => '',
                'label' => "categories.form.parent",
                'required' => false
            ))
            ->add('main_page', 'checkbox', array(
                'label' => "categories.form.show_on_courses_page",
                'required' => false
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Elearning\CoursesBundle\Entity\Category'
        ));
    }

    public function getName()
    {
        return "category";
    }
}