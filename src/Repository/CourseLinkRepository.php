<?php

namespace App\Repository;

use App\Entity\CourseLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CourseLink|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseLink|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseLink[]    findAll()
 * @method CourseLink[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseLinkRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CourseLink::class);
    }

    // /**
    //  * @return CourseLink[] Returns an array of CourseLink objects
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
    public function findOneBySomeField($value): ?CourseLink
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
