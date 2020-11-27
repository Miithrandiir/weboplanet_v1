<?php

namespace App\Repository;

use App\Entity\UserGgroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserGgroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserGgroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserGgroup[]    findAll()
 * @method UserGgroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserGgroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserGgroup::class);
    }

    // /**
    //  * @return UserGgroup[] Returns an array of UserGgroup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserGgroup
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
