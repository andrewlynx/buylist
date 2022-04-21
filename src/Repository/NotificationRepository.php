<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getUnread(User $user): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->andWhere('n.seen = :seen')
            ->setParameter('user', $user)
            ->setParameter('seen', false)
            ->orderBy('n.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param User $user
     *
     * @return int
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countUnread(User $user): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n)')
            ->andWhere('n.user = :user')
            ->andWhere('n.seen = :seen')
            ->setParameter('user', $user)
            ->setParameter('seen', false)
            ->setMaxResults(5)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getUsersNotifications(User $user): array
    {
        $qb = $this->createQueryBuilder('n')
            ->Where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.id', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param User $user
     */
    public function readAll(User $user): void
    {
        $qb = $this->createQueryBuilder('n')
            ->update()
            ->set('n.seen', true)
            ->where('n.user = :user')
            ->setParameter('user', $user);

        $qb->getQuery()->execute();
    }

    /**
     * @param User $user
     */
    public function clearAll(User $user): void
    {
        $qb = $this->createQueryBuilder('n')
            ->delete()
            ->where('n.user = :user')
            ->setParameter('user', $user);

        $qb->getQuery()->execute();
    }

    /**
     * @param User $user
     *
     * @return bool
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function checkUpdates(User $user): bool
    {
        $qb = $this->createQueryBuilder('n')
            ->select('COUNT(n)')
            ->where('n.user = :user')
            ->andWhere('n.date > :date')
            ->setParameter('user', $user)
            ->setParameter('date', $user->getPreviousVisitTime());

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
