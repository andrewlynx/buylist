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

    // /**
    //  * @return EmailInvitation[] Returns an array of EmailInvitation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EmailInvitation
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
