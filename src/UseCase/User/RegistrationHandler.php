<?php

namespace App\UseCase\User;

use App\DTO\User\Registration;
use App\DTO\User\Settings;
use App\Entity\EmailInvitation;
use App\Entity\Object\Email;
use App\Entity\User;
use App\Repository\EmailInvitationRepository;
use App\Repository\UserRepository;
use App\Service\Notification\NotificationService;
use App\Validator\Locale;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @param EntityManagerInterface       $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param NotificationService          $notificationService
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        NotificationService $notificationService
    ) {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->notificationService = $notificationService;
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

            $this->notificationService->createOrUpdate(
                NotificationService::EVENT_INVITED,
                $user,
                $taskList,
                $taskList->getCreator()
            );
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

    /**
     * @param string $userName
     *
     * @return User
     *
     * @throws NonUniqueResultException
     */
    public function makeAdmin(string $userName): User
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->em->getRepository(User::class);
        $user = $userRepo->findUser($userName);
        if (!$user) {
            throw new Exception(sprintf('User %s not found', $userName));
        }
        $user->addRole(User::ROLE_ADMIN);
        $this->em->flush();

        return $user;
    }
}
