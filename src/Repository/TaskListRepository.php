<?php

namespace App\Repository;

use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use function Symfony\Component\Translation\t;

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
            ->orderBy('t.creator' )
            ->addOrderBy('t.createdAt')
        ;

        return $qb->getQuery()->getResult();
    }

    private function getTasks(UserInterface $user, bool $archived): array
    {
        $qb = $this->createQueryBuilder('t')
            ->Where('t.creator = :user')
            ->andWhere('t.archived = :archived')
            ->setParameter('user', $user)
            ->setParameter('archived', intval($archived));

        return $qb->getQuery()->getResult();
    }
}
