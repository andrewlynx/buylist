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
}
