<?php

namespace AppBundle\Repository;

/**
 * PostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostRepository extends \Doctrine\ORM\EntityRepository
{
    public function countAll()
    {
        return $this->_em->createQueryBuilder()
            ->select('count(p.id)')
            ->from('AppBundle:Post', 'p')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function findThumbByTheme($idTheme)
    {
        return $this->_em->createQueryBuilder()
            ->select('p.title', 't')
            ->from('AppBundle:Post', 'p')
            ->leftJoin('p.theme', 't')
            ->where('t.id = :idTheme')
            ->setParameter('idTheme', $idTheme)
            ->orderBy('p.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}