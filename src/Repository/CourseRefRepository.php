<?php

namespace App\Repository;

use App\Entity\CourseRef;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CourseRef|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseRef|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseRef[]    findAll()
 * @method CourseRef[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseRefRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CourseRef::class);
    }

    // /**
    //  * @return CourseRef[] Returns an array of CourseRef objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CourseRef
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
