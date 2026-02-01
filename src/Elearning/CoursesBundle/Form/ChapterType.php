<?php

namespace Elearning\CoursesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChapterType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options) {
        $types = array(
            'video'=>'new_course.editor.types.video',
            'video_files'=>'new_course.editor.types.video_files',
            'quiz'=>'new_course.editor.types.quiz',
            'lesson'=>'new_course.editor.types.lesson',
            'exam'=>'new_course.editor.types.exam',
            'feedback'=>'new_course.editor.types.feedback',
            'material'=>'new_course.editor.types.material',
            'slides'=>'new_course.editor.types.slides'
            );

        if ($options['disabled_chapter_types']) {
            foreach ($options['disabled_chapter_types'] as $type) {
                unset($types[$type]);
            }
        }

        $builder
            ->add('name', 'text', array(
                'label'=>"new_course.editor.chapters.new.name"
            ))
            ->add('type', 'choice', array(
                'choices' => $types,
                'label'=>"new_course.editor.chapters.new.type"
            ))
            ->add('course_id', 'hidden')
            ->add('ordering', 'hidden');
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Elearning\CoursesBundle\Entity\Chapter',
            'disabled_chapter_types' => null,
        ));
    }

    public function getName() {
        return "chapter";
    }
}
