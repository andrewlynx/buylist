<?php

namespace App\Repository;

use App\Entity\TaskItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TaskItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskItem[]    findAll()
 * @method TaskItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskItem::class);
    }

    // /**
    //  * @return TaskItem[] Returns an array of TaskItem objects
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
    public function findOneBySomeField($value): ?TaskItem
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
