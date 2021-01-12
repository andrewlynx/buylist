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
     *
     * @return array|null
     */
    public function getPendingInvitations(string $email): ?array
    {
        $qb = $this->createQueryBuilder('i')
            ->andWhere('i.email = :email')
            ->setParameter('email', $email);

        return $qb->getQuery()->getResult();
    }
}
