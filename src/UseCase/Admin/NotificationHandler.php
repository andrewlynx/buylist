<?php

namespace App\UseCase\Admin;

use App\Entity\AdminNotification;
use App\Entity\User;
use App\Exceptions\UserException;
use App\Repository\AdminNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class NotificationHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /**
     * @param array $users
     * @param string $text
     */
    public function create(array $users, string $text): void
    {
        foreach ($users as $user) {
            $note = (new AdminNotification())
                ->setText($text)
                ->setUser($user);
            $this->em->persist($note);
        }

        $this->em->flush();
    }

    /**
     * @param AdminNotification|null $notification
     * @param User                   $user
     *
     * @throws UserException
     */
    public function markSeen(?AdminNotification $notification, User $user): void
    {
        if ($notification === null || $notification->getUser() !== $user) {
            throw new UserException('notification.not_found');
        }

        $notification->setSeen(true);
        $this->em->flush();
    }

    /**
     *
     */
    public function clear(): void
    {
        /** @var AdminNotificationRepository $adminNotificationRepo */
        $adminNotificationRepo = $this->em->getRepository(AdminNotification::class);
        $adminNotificationRepo->clearRead();
    }
}
