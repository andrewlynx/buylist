<?php

namespace App\Repository;

use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\Mutators\ListType;
use App\Repository\Mutators\Pagination;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
    /**
     * @var ListType
     */
    private $listTypeFilter;

    /**
     * @param ManagerRegistry $registry
     * @param ListType        $listTypeFilter
     */
    public function __construct(ManagerRegistry $registry, ListType $listTypeFilter)
    {
        parent::__construct($registry, TaskList::class);
        $this->listTypeFilter = $listTypeFilter;
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
        ;

        $query = (new Pagination())->paginate($qb, $page);
        $query = $this->listTypeFilter->filter($query);

        return $query->getQuery()->getResult();
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
     * @param User $user
     * @param int|null $page
     *
     * @return array
     */
    public function getFavourites(User $user, ?int $page = null): array
    {
        try {
            $conn = $this->getEntityManager()->getConnection();

            $sql = '
                SELECT DISTINCT t.id FROM task_list t
                LEFT JOIN favourite_lists fl ON fl.task_list_id = t.id
                LEFT JOIN `user` u ON fl.user_id = u.id
                WHERE u.id = :user
            ';
            $stmt = $conn->prepare($sql);
            $stmt->execute(['user' => $user->getId()]);

            $ids = array_column($stmt->fetchAllAssociative(), 'id');
        } catch (\Throwable $e) {
            //@todo log exception
            $ids = [];
        }
        $qb = $this->createQueryBuilder('t')
            ->where('t.id IN (:ids)')
            ->addSelect("(CASE WHEN t.creator = :user THEN 1 ELSE 0 END) AS HIDDEN creator")
            ->setParameter('user', $user)
            ->orderBy('t.creator')
            ->addOrderBy('t.createdAt', 'DESC')
            ->setParameter('ids', $ids)
        ;

        $query = (new Pagination())->paginate($qb, $page);
        $query = $this->listTypeFilter->filter($query);

        return $query->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return ArrayCollection
     */
    public function getByDates(User $user, DateTime $start, DateTime $end): ArrayCollection
    {
        $qb = $this->createQueryBuilder('t')
            ->Where('t.creator = :user')
            ->andWhere('t.date >= :start')
            ->andWhere('t.date <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start->setTime(00, 00))
            ->setParameter('end', $end->setTime(23, 59, 59));

        $qbShared = $this->createQueryBuilder('t')
            ->addSelect('CASE WHEN t.creator in (:favourites) THEN 1 ELSE 0 END AS HIDDEN favourites')
            ->innerJoin('t.shared', 'u', 'WITH', 'u.email = :email')
            ->andWhere('t.archived = 0')
            ->andWhere('t.date >= :start')
            ->andWhere('t.date <= :end')
            ->setParameter('email', $user->getEmail())
            ->setParameter('favourites', $user->getFavouriteUsers())
            ->setParameter('start', $start->setTime(00, 00))
            ->setParameter('end', $end->setTime(23, 59, 59))
        ;

        return new ArrayCollection(
            array_merge(
                $qb->getQuery()->getResult(),
                $qbShared->getQuery()->getResult()
            )
        );
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
        ;

        $query = (new Pagination())->paginate($qb, $page);
        $query = $this->listTypeFilter->filter($query);

        return $query->getQuery()->getResult();
    }
}
