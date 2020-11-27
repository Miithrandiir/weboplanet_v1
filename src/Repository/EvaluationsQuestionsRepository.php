<?php

namespace App\Repository;

use App\Entity\EvaluationsQuestions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EvaluationsQuestions|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvaluationsQuestions|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvaluationsQuestions[]    findAll()
 * @method EvaluationsQuestions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluationsQuestionsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EvaluationsQuestions::class);
    }

    // /**
    //  * @return EvaluationsQuestions[] Returns an array of EvaluationsQuestions objects
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
    public function findOneBySomeField($value): ?EvaluationsQuestions
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
