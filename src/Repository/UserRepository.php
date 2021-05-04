<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     *
     * @param UserInterface $user
     * @param string $newEncodedPassword
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param string $name
     *
     * @return User|null
     *
     * @throws NonUniqueResultException
     */
    public function findUser(string $name): ?User
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.email = :name')
            ->orWhere('u.nickName = :name')
            ->setParameter('name', $name);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function findFriends(User $user): array
    {
        try {
            $conn = $this->getEntityManager()->getConnection();

            $sql = '
                SELECT DISTINCT u.id FROM user u
                LEFT JOIN task_list_user tu ON tu.user_id = u.id
                LEFT JOIN task_list t ON tu.task_list_id = t.id
                WHERE t.creator_id = :user
            ';
            $stmt = $conn->prepare($sql);
            $stmt->execute(['user' => $user->getId()]);

            $ids = array_column($stmt->fetchAllAssociative(), 'id');
        } catch (\Throwable $e) {
            //@todo log exception
            $ids = [];
        }
        $qb = $this->createQueryBuilder('u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }
}
