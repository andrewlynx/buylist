<?php

namespace App\Service\Notification;

use App\Entity\Notification;
use App\Entity\TaskList;
use App\Entity\User;
use DateTime;
use Exception;
use InvalidArgumentException;

class NotificationFactory
{
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
    public static function make(
        int $event,
        User $user,
        ?TaskList $taskList = null,
        ?User $userInvolved = null
    ): Notification {
        $notification = (new Notification())
            ->setEvent($event)
            ->setUser($user)
            ->setDate(new DateTime());

        switch ($event) {
            case NotificationService::EVENT_WELCOME:
                break;
            case NotificationService::EVENT_INVITED:
            case NotificationService::EVENT_LIST_CHANGED:
            case NotificationService::EVENT_LIST_ARCHIVED:
            case NotificationService::EVENT_LIST_REMOVED:
            case NotificationService::EVENT_UNSUBSCRIBED:
                self::validate($taskList, TaskList::class);
                self::validate($userInvolved, User::class);
                $notification
                    ->setTaskList($taskList)
                    ->setUserInvolved($userInvolved);
                break;
            default:
                throw new InvalidArgumentException('event.invalid_event');
        }

        return $notification;
    }

    /**
     * @param $argument
     * @param string $class
     */
    private static function validate($argument, string $class): void
    {
        if (!$argument instanceof $class) {
            throw new InvalidArgumentException(
                sprintf(
                    'Incorrect value passed to Notification factory: expected %s, got %s',
                    $class,
                    gettype($argument)
                )
            );
        }
    }
}
