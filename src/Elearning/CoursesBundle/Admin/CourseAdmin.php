<?php

namespace Elearning\CoursesBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class CourseAdmin extends Admin
{

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
            ->add('name', 'text', array('label' => 'Course name'))
            ->add('category', 'sonata_type_model')
            ->add('description')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
            ->add('name')
            ->add('category')
            ->add('description')
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
            ->addIdentifier('name')
            ->add('category')
        ;
    }

}
