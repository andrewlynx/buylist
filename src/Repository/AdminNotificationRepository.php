<?php

namespace App\Repository;

use App\Entity\AdminNotification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AdminNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdminNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdminNotification[]    findAll()
 * @method AdminNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdminNotificationRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminNotification::class);
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getUnread(User $user): array
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->andWhere('n.seen = 0')
            ->setParameter('user', $user)
            ->orderBy('n.id', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
