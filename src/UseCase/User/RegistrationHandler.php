<?php

namespace App\UseCase\User;

use App\DTO\User\Registration;
use App\Entity\EmailInvitation;
use App\Entity\User;
use App\Repository\EmailInvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @param EntityManagerInterface       $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param User         $user
     * @param Registration $dto
     *
     * @return User
     */
    public function register(User $user, Registration $dto): User
    {
        $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $dto->plainPassword
                )
            )
            ->setEmail($dto->email);

        $this->em->persist($user);

        // Check for pending invitations to lists
        /** @var EmailInvitationRepository $emailInvitationRepo */
        $emailInvitationRepo = $this->em->getRepository(EmailInvitation::class);
        $pending = $emailInvitationRepo->getPendingInvitations($user->getEmail());
        /** @var EmailInvitation $invitation */
        foreach ($pending as $invitation) {
            $taskList = $invitation->getTaskList()->addShared($user);
            $this->em->persist($taskList);
            $this->em->remove($invitation);
            //dd($taskList);
        }

        $this->em->flush();

        return $user;
    }
}