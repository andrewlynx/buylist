<?php

namespace App\Service\Notification;

use App\Entity\Notification;
use App\Entity\TaskList;
use App\Entity\User;
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
     * @param int $event
     * @param User $user
     * @param TaskList|null $taskList
     * @param User|null $userInvolved
     *
     * @return Notification
     *
     * @throws Exception
     */
    public function createOrUpdate(
        int $event,
        User $user,
        ?TaskList $taskList = null,
        ?User $userInvolved = null
    ): Notification {
        $notification = $this->getOrCreate(
            $event,
            $user,
            $taskList,
            $userInvolved
        );

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }

    /**
     * @param int $event
     * @param array<User> $users
     * @param TaskList|null $taskList
     * @param User|null $userInvolved
     *
     * @throws Exception
     */
    public function createForManyUsers(
        int $event,
        array $users,
        ?TaskList $taskList = null,
        ?User $userInvolved = null
    ): void {
        foreach ($users as $user) {
            // do not add notification for current user
            if ($user === $this->getUser()) {
                continue;
            }
            $notification = $this->getOrCreate(
                $event,
                $user,
                $taskList,
                $userInvolved
            );
            $this->em->persist($notification);
        }
        $this->em->flush();
    }

    /**
     * Returns translation string to display on a page
     *
     * @param Notification $notification
     *
     * @return string
     */
    public static function getDescription(Notification $notification): string
    {
        switch ($notification->getEvent()) {
            case self::EVENT_WELCOME:
                return 'notification.welcome';
            case self::EVENT_INVITED:
                return 'notification.invitation';
            case self::EVENT_LIST_CHANGED:
                return 'notification.list_changed';
            case self::EVENT_LIST_ARCHIVED:
                return 'notification.list_archived';
            case self::EVENT_LIST_REMOVED:
                return 'notification.list_removed';
            case self::EVENT_UNSUBSCRIBED:
                return 'notification.unsubscribed';

            default:
                return 'notification.not_found';
        }
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
     * @param int           $event
     * @param User          $user
     * @param TaskList|null $taskList
     * @param User|null     $userInvolved
     *
     * @return Notification
     *
     * @throws \Exception
     */
    private function getOrCreate(
        int $event,
        User $user,
        ?TaskList $taskList = null,
        ?User $userInvolved = null
    ): Notification {
        $notification = $this->em->getRepository(Notification::class)->findOneBy([
            'event' => $event,
            'user' => $user->getId(),
            'taskList' => $taskList->getId(),
            'userInvolved' => $userInvolved,
            'seen' => false
        ]);
        if (!$notification) {
            $notification = NotificationFactory::make(
                $event,
                $user,
                $taskList,
                $userInvolved
            );
        }

        return $notification;
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
