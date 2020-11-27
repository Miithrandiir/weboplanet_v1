<?php

namespace App\Repository;

use App\Entity\SectionRef;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SectionRef|null find($id, $lockMode = null, $lockVersion = null)
 * @method SectionRef|null findOneBy(array $criteria, array $orderBy = null)
 * @method SectionRef[]    findAll()
 * @method SectionRef[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SectionRefRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SectionRef::class);
    }

    // /**
    //  * @return SectionRef[] Returns an array of SectionRef objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SectionRef
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
