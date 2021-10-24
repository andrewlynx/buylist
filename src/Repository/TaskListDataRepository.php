<?php

namespace App\Repository;

use App\Entity\TaskListData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TaskListData|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskListData|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskListData[]    findAll()
 * @method TaskListData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskListDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskListData::class);
    }

    // /**
    //  * @return TaskListData[] Returns an array of TaskListData objects
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
    public function findOneBySomeField($value): ?TaskListData
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
