<?php

namespace Elearning\CoursesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReflectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('course', EntityType::class, array(
                'class' => 'ElearningCoursesBundle:Course',
                'choice_label' => 'name',
                'label' => 'reflection.form.course',
                'placeholder' => 'reflection.form.choose_course',
            ))
            ->add('studentText', TextareaType::class, array(
                'label' => 'reflection.form.student_text',
                'attr' => array('rows' => 10, 'class' => 'form-control reflection-text'),
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'reflection.form.submit',
                'attr' => array('class' => 'btn btn-orange'),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Elearning\CoursesBundle\Entity\Reflection'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'elearning_coursesbundle_reflection';
    }
}
