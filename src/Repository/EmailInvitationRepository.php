<?php

namespace App\Repository;

use App\Entity\EmailInvitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmailInvitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailInvitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailInvitation[]    findAll()
 * @method EmailInvitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailInvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailInvitation::class);
    }

    /**
     * @param string $email
     * @param int|null $taskListId
     *
     * @return array|null
     */
    public function getPendingInvitations(string $email, ?int $taskListId = null): ?array
    {
        $qb = $this->createQueryBuilder('i')
            ->andWhere('i.email = :email')
            ->setParameter('email', $email);
        if ($taskListId !== null) {
            $qb->andWhere('i.taskList = :id')
                ->setParameter('id', $taskListId);
        }

        return $qb->getQuery()->getResult();
    }
}
