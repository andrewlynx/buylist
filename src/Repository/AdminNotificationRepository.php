<?php

namespace App\Repository;

use App\Entity\AdminNotification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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

    /**
     *
     */
    public function clearRead(): void
    {
        $qb = $this->createQueryBuilder('n')
            ->delete()
            ->where('n.seen = 1');

        $qb->getQuery()->execute();
    }

    /**
     * @return int
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countUnread(): int
    {
        $qb = $this->createQueryBuilder('n')
            ->select('COUNT(n)')
            ->where('n.seen = 1');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
