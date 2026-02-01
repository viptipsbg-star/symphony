<?php

namespace Elearning\CompaniesBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class EmployeeAdmin extends Admin
{

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
            ->add('company', 'entity', array('class' => 'Elearning\CompaniesBundle\Entity\Company'))
            ->add('user', 'entity', array('class' => 'Elearning\UserBundle\Entity\User'))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
            ->add('company')
            ->add('user')
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
            ->addIdentifier('company')
            ->addIdentifier('user')
        ;
    }

}
