<?php

namespace App\Repository;

use App\Entity\GroupSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GroupSection|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupSection|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupSection[]    findAll()
 * @method GroupSection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupSectionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GroupSection::class);
    }

    // /**
    //  * @return GroupSection[] Returns an array of GroupSection objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GroupSection
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
