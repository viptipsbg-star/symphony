<?php

namespace Elearning\CoursesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class DiaryTopicType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('issued', 'date', array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => false,
                'model_timezone' => 'Europe/Moscow',
                'view_timezone'  => 'UTC',
                'label'=>'reports.attendance.date'
            ))
            ->add('topic', 'entity', array(
                'class' => 'ElearningCoursesBundle:Topic',
                'choice_label' => 'text',
                'placeholder' => '',
                'label' => "diary.topic",
                'query_builder' => function ($repo) {
                    return $repo->createQueryBuilder('t')
                        ->join('t.diaryTopics', 'dt')
                        ->where('dt.active = 1');
                }
            ))
            ->add('employee_id', 'hidden')
            ->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'preSubmit'));
    }

    public function preSubmit(FormEvent $event) {
        $form = $event->getForm();
        $form->remove('topic');
        $form->add('topic', 'entity', array(
            'class' => 'ElearningCoursesBundle:Topic',
        ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Elearning\CoursesBundle\Entity\DiaryTopic'
        ));
    }

    public function getName() {
        return "diary_topic";
    }
}
