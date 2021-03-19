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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskList::class);
    }

    /**
     * @param UserInterface $user
     *
     * @return mixed
     */
    public function getUsersTasks(UserInterface $user): array
    {
        return $this->getTasks($user, false);
    }

    /**
     * @param UserInterface $user
     *
     * @return mixed
     */
    public function getArchivedUsersTasks(UserInterface $user): array
    {
        return $this->getTasks($user, true);
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getSharedTasks(User $user): array
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.shared', 'u', 'WITH', 'u.email = :email')
            ->andWhere('t.archived = 0')
            ->setParameter('email', $user->getEmail())
            ->orderBy('t.creator')
            ->addOrderBy('t.createdAt', 'DESC')
        ;

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
     *
     * @return array
     */
    private function getTasks(UserInterface $user, bool $archived): array
    {
        $qb = $this->createQueryBuilder('t')
            ->Where('t.creator = :user')
            ->andWhere('t.archived = :archived')
            ->setParameter('user', $user)
            ->setParameter('archived', intval($archived))
            ->orderBy('t.id', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }
}
