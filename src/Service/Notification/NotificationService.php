<?php

namespace App\Service\Notification;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
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

    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->em = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public function getUnread(): array
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return $this->em->getRepository(Notification::class)->getUnread($user);
    }

    /**
     * @return int
     */
    public function countUnread(): int
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return $this->em->getRepository(Notification::class)->countUnread($user);
    }

    /**
     * @param Notification $notification
     *
     * @return string
     */
    public static function getDescription(Notification $notification): string
    {
        switch ($notification->getEvent()) {
            case self::EVENT_WELCOME:
                return 'notification.welcome';
                break;
            case self::EVENT_INVITED:
                return 'notification.invitation';

            default:
                return 'notification.not_found';
        }
    }
}