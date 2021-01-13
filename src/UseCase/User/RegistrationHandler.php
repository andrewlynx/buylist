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
        }

        $this->em->flush();

        // generate a signed url and email it to the user
//            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
//                (new TemplatedEmail())
//                    ->from(new Address('mailer@buylist.com', 'BuyList'))
//                    ->to($user->getEmail())
//                    ->subject('Please Confirm your Email')
//                    ->htmlTemplate('registration/confirmation_email.html.twig')
//            );
        // do anything else you need here, like send an email

        return $user;
    }
}