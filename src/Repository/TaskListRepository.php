<?php

namespace App\Repository;

use App\Entity\TaskList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method TaskList|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskList|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskList[]    findAll()
 * @method TaskList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskList::class);
    }

    /**
     * @param UserInterface $user
     *
     * @return mixed
     */
    public function getUsersTasks(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('t')
            ->Where('t.creator = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }
}
