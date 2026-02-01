<?php

namespace Elearning\CoursesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class SubjectType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('lesson_date', 'date', array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => false,
                'model_timezone' => 'Europe/Moscow',
                'view_timezone'  => 'UTC',
                'label'=>'reports.attendance.date'
            ))
            ->add('description', 'text', array(
                'label'=>'reports.attendance.subject_title'
            ))
            ->add('group_id', 'hidden');
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Elearning\CoursesBundle\Entity\Subject'
        ));
    }

    public function getName() {
        return "subject";
    }
}
