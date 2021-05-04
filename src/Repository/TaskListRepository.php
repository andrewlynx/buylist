<?php

namespace App\Repository;

use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Entity\User;
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
    public const PER_PAGE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskList::class);
    }

    /**
     * @param UserInterface $user
     * @param int|null $page
     *
     * @return array
     */
    public function getUsersTasks(UserInterface $user, ?int $page = null): array
    {
        return $this->getTasks($user, false, $page);
    }

    /**
     * @param UserInterface $user
     * @param int|null $page
     *
     * @return array
     */
    public function getArchivedUsersTasks(UserInterface $user, ?int $page = null): array
    {
        return $this->getTasks($user, true, $page);
    }

    /**
     * @param User $user
     * @param int|null $page
     *
     * @return array
     */
    public function getSharedTasks(User $user, ?int $page = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->addSelect('CASE WHEN t.creator in (:favourites) THEN 1 ELSE 0 END AS HIDDEN favourites')
            ->innerJoin('t.shared', 'u', 'WITH', 'u.email = :email')
            ->andWhere('t.archived = 0')
            ->setParameter('email', $user->getEmail())
            ->setParameter('favourites', $user->getFavouriteUsers())
            ->orderBy('t.creator')
            ->orderBy('favourites', 'DESC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->setMaxResults(self::PER_PAGE)
        ;

        if ($page !== null) {
            $qb->setFirstResult(intval($page * self::PER_PAGE));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @param string $value
     *
     * @return array
     */
    public function searchLists(User $user, string $value): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('MATCH_AGAINST(t.name, t.description) AGAINST(:searchterm boolean)>0')
            ->andWhere('t.creator = :user OR :user MEMBER OF t.shared')
            ->setParameter('searchterm', $value)
            ->setParameter('user', $user)
            ->orderBy('t.creator')
            ->addOrderBy('t.archived')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @param string $value
     *
     * @return array
     */
    public function searchListItems(User $user, string $value): array
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin(TaskItem::class, 'i', 'WITH', 'i.taskList = t.id')
            ->where('MATCH_AGAINST(i.name, i.qty) AGAINST(:searchterm boolean)>0')
            ->andWhere('t.creator = :user OR :user MEMBER OF t.shared')
            ->setParameter('searchterm', $value)
            ->setParameter('user', $user)
            ->orderBy('t.creator')
            ->addOrderBy('t.archived')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param UserInterface $user
     * @param bool $archived
     * @param int|null $page
     *
     * @return array
     */
    private function getTasks(UserInterface $user, bool $archived, ?int $page): array
    {
        $qb = $this->createQueryBuilder('t')
            ->Where('t.creator = :user')
            ->andWhere('t.archived = :archived')
            ->setParameter('user', $user)
            ->setParameter('archived', intval($archived))
            ->orderBy('t.id', 'DESC')
            ->setMaxResults(self::PER_PAGE)
        ;

        if ($page !== null) {
            $qb->setFirstResult(intval($page * self::PER_PAGE));
        }

        return $qb->getQuery()->getResult();
    }
}
