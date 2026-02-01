<?php

namespace Elearning\CompaniesBundle\Services;

use Elearning\UserBundle\Entity\User;

class AdminService
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function getChildrenIds(User $user)
    {
        $userIds = [];
        $admin = $this->em->getRepository('ElearningCompaniesBundle:Administrator')
            ->findOneBy(array('user_id' => $user->getId()));

        $qb = $this->em->createQueryBuilder()
            ->select(['a.user_id'])
            ->from('ElearningCompaniesBundle:Administrator', 'a')
            ->where('a.root = :root')
            ->andWhere('a.lft > :lft')
            ->andWhere('a.rgt < :rgt')
            ->setParameters(array(
                'root' => $admin->getRoot(),
                'lft' => $admin->getLft(),
                'rgt' => $admin->getRgt()
            ));

        $children = $qb->getQuery()->getResult();
        foreach ($children as $child) {
            $userIds[] = $child['user_id'];
        }
        
        return $userIds;
    }
    
    public function getGroups(User $user, $company, $idsOnly = false)
    {
        $groupIds = [];
        $userIds = array($user->getId());
        $adminIds = $this->getChildrenIds($user);
        $userIds = array_merge($userIds, $adminIds);

        $groupsQb = $this->em->createQueryBuilder()
            ->select('g')
            ->from('ElearningCompaniesBundle:Group', 'g')
            ->join('g.employees', 'e')
            ->where('g.company_id = :company_id')
            ->andWhere('g.state = :state')
            ->andWhere('e.user_id IN (:user_ids)')
            ->setParameters(array(
                    'company_id' => $company->getId(),
                    'state' => 'published',
                    'user_ids' => $userIds
                )
            );
        
        $groups = $groupsQb->getQuery()->getResult();
        if (!$idsOnly) {
            return $groups;
        }
        
        foreach ($groups as $group) {
            $groupIds[] = $group->getId();
        }
        return $groupIds;
    }

    public function getGroupAdmins()
    {
        $groupAdmins = $this->em->createQueryBuilder()
            ->select(array('g.id as gid', 'u.username', 'e.id as eid', 'a.lvl as lvl'))
            ->from('ElearningCompaniesBundle:Group', 'g')
            ->join('g.employees', 'e')
            ->leftJoin('e.fields', 'f')
            ->join('e.user', 'u')
            ->join('ElearningCompaniesBundle:Administrator', 'a', 'WITH', 'u.id = a.user_id')
            ->where('u.enabled = 1')
            ->orderBy('g.id', 'ASC')
            ->addOrderBy('a.lvl', 'DESC')
            ->getQuery()->getResult();

        $result = array();
        $curGroup = null;
        $maxLvl = null;
        foreach ($groupAdmins as $admin) {
            if ($admin['gid'] != $curGroup) {
                $curGroup = $admin['gid'];
                $maxLvl = $admin['lvl'];
            }
            if ($admin['lvl'] == $maxLvl) {
                $employee['eid'] = $admin['eid'];
                $employee['username'] = $admin['username'];
                $employeeRef = $this->em->getReference('ElearningCompaniesBundle:Employee', $admin['eid']);
                $employee['name'] = $employeeRef->getFieldValue('name');
                $employee['surname'] = $employeeRef->getFieldValue('surname');
                $result[$admin['gid']][] = $employee;
            }
        }

        return $result;
    }
}
