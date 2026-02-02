<?php

namespace Elearning\CoursesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReflectionResponseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('teacherResponse', TextareaType::class, array(
                'label' => 'reflection.form.teacher_response',
                'attr' => array('rows' => 10, 'class' => 'form-control reflection-response'),
                'required' => true,
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'reflection.form.submit_response',
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
        return 'elearning_coursesbundle_reflection_response';
    }
}
