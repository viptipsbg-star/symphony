<?php

namespace Elearning\CompaniesBundle\Model;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CompaniesManager {
    private $em;
    private $securityTS;

    public function __construct(EntityManager $em, TokenStorageInterface $token_storage) {
        $this->em = $em;
        $this->securityTS = $token_storage;
    }
    public function companyByUserId($user_id) {
        $rep = $this->em->getRepository ( "ElearningCompaniesBundle:Employee" );
        $employee = $rep->findOneBy ( array (
                'user_id' => $user_id 
        ) );
        return $employee->getCompany ();
    }
    public function currentUserCompany() {
        $current_user = $this->securityTS->getToken ()->getUser ();
        $rep = $this->em->getRepository ( "ElearningCompaniesBundle:Employee" );
        $employee = $rep->findOneBy ( array (
                'user_id' => $current_user->getId() 
        ) );
        return $employee->getCompany ();
    }
}
