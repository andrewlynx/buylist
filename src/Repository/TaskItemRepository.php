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
}
