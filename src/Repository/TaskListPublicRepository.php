<?php

namespace App\Repository;

use App\Entity\TaskListPublic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TaskListPublic|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskListPublic|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskListPublic[]    findAll()
 * @method TaskListPublic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskListPublicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskListPublic::class);
    }

    // /**
    //  * @return TaskListPublic[] Returns an array of TaskListPublic objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TaskListPublic
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
