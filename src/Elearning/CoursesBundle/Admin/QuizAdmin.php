<?php

namespace Elearning\CoursesBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class QuizAdmin extends Admin
{

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
            ->add('chapter', 'sonata_type_model')
            ->add('type')
            ->add('question')
            ->add('choices')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
            ->add('chapter')
            ->add('type')
            ->add('question')
            ->add('choices')
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
            ->addIdentifier('id')
            ->add('type')
            ->add('question')
        ;
    }

}
