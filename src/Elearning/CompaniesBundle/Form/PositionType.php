<?php

namespace Elearning\CompaniesBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionType extends AbstractType
{

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $positionsData = $this->em->createQuery(
            'SELECT DISTINCT ef.fieldvalue
            FROM ElearningCompaniesBundle:EmployeeProfileField ef
            JOIN ef.employee e
            WHERE ef.fieldname = :fieldname'
        )->setParameter('fieldname', 'position')
        ->getResult();
        $positions = array();
        foreach ($positionsData as $field) {
            $positions[$field['fieldvalue']] = $field['fieldvalue'];
        }
        $resolver->setDefaults(array(
            'choices' => $positions,
            'attr' => array(
                'class' => 'position-select'
            )
        ));
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'elearning_companies_position';
    }
}
