<?php

namespace App\UseCase\User;

use App\DTO\User\Registration;
use App\DTO\User\Settings;
use App\Entity\EmailInvitation;
use App\Entity\Object\Email;
use App\Entity\User;
use App\Repository\EmailInvitationRepository;
use App\Validator\Locale;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
     * @param Registration $dto
     *
     * @return User
     */
    public function register(Registration $dto): User
    {
        $user = new User();
        $email = new Email($dto->email);
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $dto->plainPassword
            )
        )
            ->setEmail($email->getValue())
            ->setLocale($dto->locale)
            ->setNickName($dto->nickName);

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

        return $user;
    }

    /**
     * @param User $user
     * @param Settings $dto
     *
     * @return User
     *
     * @throws Exception
     */
    public function updateSettings(User $user, Settings $dto): User
    {
        $locale = $dto->locale;
        if (Locale::validateLocale($locale, true)) {
            $user->setLocale($locale);
        }

        if ($dto->oldPassword !== null) {
            if ($this->passwordEncoder->isPasswordValid($user, $dto->oldPassword)) {
                $user->setPassword(
                    $this->passwordEncoder->encodePassword(
                        $user,
                        $dto->newPassword
                    )
                );
            } else {
                throw new Exception('user.incorrect_current_password');
            }
        }

        $this->em->flush();

        return $user;
    }

    /**
     * @param User $user
     * @return User
     *
     * @throws Exception
     */
    public function login(User $user): User
    {
        $user->setLastLogin(new DateTime());
        $this->em->flush();

        return $user;
    }
}
