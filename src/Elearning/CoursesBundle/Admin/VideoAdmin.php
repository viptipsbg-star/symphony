<?php

namespace Elearning\CoursesBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class VideoAdmin extends Admin
{

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
            ->add('chapter', 'sonata_type_model')
            ->add('filename')
            ->add('duration')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
            ->add('chapter')
            ->add('filename')
            ->add('duration')
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
            ->addIdentifier('id')
            ->add('chapter')
            ->add('duration')
        ;
    }

}
