<?php

namespace Elearning\CoursesBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class CategoryAdmin extends Admin
{

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
            ->add('name', 'text', array('label' => 'Category name'))
            ->add('parent_id', 'entity', array('class' => 'Elearning\CoursesBundle\Entity\Category', 'required' => false))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
            ->add('name')
            ->add('parent_id')
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
            ->addIdentifier('name')
            ->add('parent_id')
        ;
    }

}
