<?php

namespace App\UseCase\User;

use App\Constant\AppConstant;
use App\DTO\User\Registration;
use App\DTO\User\Settings;
use App\Entity\Object\Email;
use App\Entity\User;
use App\Repository\UserRepository;
use App\UseCase\InvitationHandler\InvitationHandler;
use App\Validator\Locale;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var InvitationHandler
     */
    private $invitationHandler;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @param EntityManagerInterface       $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param InvitationHandler            $invitationHandler
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        InvitationHandler $invitationHandler
    ) {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->invitationHandler = $invitationHandler;
    }

    /**
     * @param Registration $dto
     *
     * @return User
     *
     * @throws Exception
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
        $this->invitationHandler->sendPendingInvitations($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @param GoogleUser $googleUser
     *
     * @return User
     *
     * @throws Exception
     */
    public function registerFromGoogle(GoogleUser $googleUser): User
    {
        $user = new User();
        $email = new Email($googleUser->getEmail());
        $locale = Locale::validateLocale($googleUser->getLocale())
            ? $googleUser->getLocale()
            : AppConstant::DEFAULT_LOCALE;

        $user->setEmail($email->getValue())
            ->setNickName($googleUser->getName())
            ->setLocale($locale);

        $this->em->persist($user);
        $this->invitationHandler->sendPendingInvitations($user);
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
