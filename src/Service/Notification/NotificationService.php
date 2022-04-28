<?php

namespace App\Service\Notification;

use App\Entity\AdminNotification;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotificationService
{
    public const EVENT_WELCOME = 1;
    public const EVENT_INVITED = 2;
    public const EVENT_LIST_CHANGED = 3;
    public const EVENT_LIST_ARCHIVED = 4;
    public const EVENT_LIST_REMOVED = 5;
    public const EVENT_UNSUBSCRIBED = 6;

    public const EVENTS = [
        self::EVENT_WELCOME,
        self::EVENT_INVITED,
        self::EVENT_LIST_CHANGED,
        self::EVENT_LIST_ARCHIVED,
        self::EVENT_LIST_REMOVED,
        self::EVENT_UNSUBSCRIBED,
    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->em = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array<Notification>
     */
    public function getUnread(): array
    {
        return $this->em->getRepository(Notification::class)->getUnread($this->getUser());
    }

    /**
     * @return int
     */
    public function countUnread(): int
    {
        return $this->em->getRepository(Notification::class)->countUnread($this->getUser());
    }

    /**
     * @return array
     */
    public function getAdminNotifications(): array
    {
        return $this->em->getRepository(AdminNotification::class)->getUnread($this->getUser());
    }

    /**
     * @return int
     */
    public function countUnreadAdminNotifications(): int
    {
        return $this->em->getRepository(AdminNotification::class)->countUnread();
    }

    /**
     * @param Collection<Notification> $notifications
     */
    public function save(Collection $notifications)
    {
        foreach ($notifications->getIterator() as $notification) {
            $this->em->persist($notification);
        }
        $this->em->flush();
    }

    /**
     * @param Notification $notification
     *
     * @return Notification|null
     *
     * @throws Exception
     */
    public function checkExistence(Notification $notification): ?Notification {
        /** @var Notification $notification */
        $notification = $this->em->getRepository(get_class($notification))->findOneBy([
            'user' => $notification->getUser()->getId(),
            'taskList' => $notification->getTaskList() ? $notification->getTaskList()->getId() : null,
            'userInvolved' => $notification->getUserInvolved(),
            'text' => $notification->getText(),
            'seen' => false
        ]);

        return $notification;
    }

    /**
     * Returns url to wrap the notification on a page o null of it's not required
     *
     * @param Notification $notification
     *
     * @return array<string, int|string>|null
     */
    public static function getUrlParams(Notification $notification): ?array
    {
        switch ($notification->getEvent()) {
            case self::EVENT_INVITED:
            case self::EVENT_LIST_CHANGED:
            case self::EVENT_LIST_ARCHIVED:
                return [
                    'page' => 'task_list_view',
                    'id' => $notification->getTaskList()->getId() ?? 0,
                ];

            default:
                return null;
        }
    }

    /**
     * @return User|null
     */
    private function getUser(): ?User
    {
        if ($this->tokenStorage->getToken()) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            return $user;
        }

        return null;
    }
}
