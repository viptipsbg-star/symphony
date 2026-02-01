<?php

namespace Elearning\CoursesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseQuestionType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('question', "textarea", array(
                'label'=>'question.question'
            ))
            ->add('answer', "textarea", array(
                'label'=>'question.answer'
            ))
            ->add('listening_id', 'hidden')
            ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Elearning\CoursesBundle\Entity\CourseQuestion'
        ));
    }

    public function getName() {
        return "coursequestion";
    }
}
