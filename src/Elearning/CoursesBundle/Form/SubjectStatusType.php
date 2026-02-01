<?php

namespace Elearning\CoursesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class SubjectStatusType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('code', 'text', array(
                'label'=>'reports.attendance.code'
            ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Elearning\CoursesBundle\Entity\SubjectStatus'
        ));
    }

    public function getName() {
        return "subject";
    }
}
