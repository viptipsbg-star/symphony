<?php

namespace Elearning\CoursesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CourseType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id = $builder->getData()->getId();
        $builder
            ->add('name', null, array(
                'label'=>'new_course.form.name'
            ))
            ->add('category', 'entity', array(
                'class'=>'ElearningCoursesBundle:Category',
                'choice_label'=>'name',
                'label'=>'new_course.form.category'
            ))
            ->add('description', 'textarea', array(
                'label'=>'new_course.form.description'
            ))
            ->add('listenperiod', 'integer', array(
                'label'=>'new_course.form.listenperiod'
            ))
            ->add('certificate_needed', 'checkbox', array(
                'label'=>'new_course.form.certificate_needed',
                'required' => false,
            ))
            ->add('flexible_order', 'checkbox', array(
                'label'=>'new_course.form.flexible_order',
                'required' => false,
            ))
            ->add('can_pause_exams', 'checkbox', array(
                'label'=>'new_course.form.can_pause_exams',
                'required' => false,
            ))
            ->add('show_active_date_to', 'checkbox', array(
                'label'=>'new_course.form.show_active_date_to',
                'required' => false,
            ))
            ->add('show_category', 'checkbox', array(
                'label'=>'new_course.form.show_category',
                'required' => false,
            ))
            ->add('show_certificate_needed', 'checkbox', array(
                'label'=>'new_course.form.show_certificate_needed',
                'required' => false,
            ))
            ->add('show_exams_count', 'checkbox', array(
                'label'=>'new_course.form.show_exams_count',
                'required' => false,
            ))
            ->add('active_date_to', 'date', array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => false,
                'required' => false,
                'model_timezone' => 'Europe/Moscow',
                'view_timezone'  => 'UTC',
                'label'=>'new_course.form.active_date_to'
            ))
            ->add('main_page', 'checkbox', array(
                'label'=>'new_course.form.show_on_courses_page',
                'required' => false,
            ))
            ->add('imagefile', 'file', array(
                'label'=>'new_course.form.image',
                'required'=> !$id ? true : false,
                'constraints' => !$id ?
                    array(
                        new Assert\NotNull(),
                    ) : null
            ))
            ->add('duration', 'integer', array(
                'label' => 'new_course.form.duration',
                'required' => false,
            ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Elearning\CoursesBundle\Entity\Course'
        ));
    }

    public function getName() {
        return "course";
    }
}
