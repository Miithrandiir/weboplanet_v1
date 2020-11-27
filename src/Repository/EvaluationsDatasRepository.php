<?php

namespace App\Repository;

use App\Entity\EvaluationsDatas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EvaluationsDatas|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvaluationsDatas|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvaluationsDatas[]    findAll()
 * @method EvaluationsDatas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluationsDatasRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EvaluationsDatas::class);
    }

    // /**
    //  * @return EvaluationsDatas[] Returns an array of EvaluationsDatas objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EvaluationsDatas
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
