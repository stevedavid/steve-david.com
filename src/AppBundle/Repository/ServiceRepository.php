<?php

namespace AppBundle\Repository;

/**
 * ServiceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ServiceRepository extends \Doctrine\ORM\EntityRepository
{
    public function countAll()
    {
        return $this->_em->createQueryBuilder()
            ->select('count(s.id)')
            ->from('AppBundle:Service', 's')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function findRandom($limit)
    {
        $results = $this->_em->createQueryBuilder()
            ->select('s')
            ->addSelect('RAND() as HIDDEN rand')
            ->from($this->_entityName, 's')
            ->orderBy('rand')
            ->getQuery()
            ->getResult()
        ;

        return $results;
    }
}