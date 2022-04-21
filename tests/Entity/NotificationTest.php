<?php

namespace App\Tests\Entity;

use App\Entity\NotificationInvited;
use App\Entity\NotificationListArchived;
use App\Entity\NotificationListChanged;
use App\Entity\NotificationListRemoved;
use App\Entity\NotificationUnsubscribed;
use App\Entity\NotificationWelcome;
use App\Entity\TaskList;
use App\Entity\User;
use App\Service\Notification\NotificationService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationTest extends WebTestCase
{
    public function testEntity()
    {
        $taskList = new TaskList();
        $user = new User();
        $user2 = new User();
        $date = new \DateTime();
        $notification = new NotificationListChanged();
        $notification
            ->setTaskList($taskList)
            ->setText('text')
            ->setSeen(false)
            ->setUser($user)
            ->setUserInvolved($user2)
            ->setDate($date);

        $this->assertNull($notification->getId());
        $this->assertSame($taskList, $notification->getTaskList());
        $this->assertSame('text', $notification->getText());
        $this->assertFalse($notification->isSeen());
        $this->assertSame($user, $notification->getUser());
        $this->assertSame($user2, $notification->getUserInvolved());
        $this->assertSame($date, $notification->getDate());
        $this->assertSame(3, $notification->getEvent());
        $this->assertSame('notification.list_changed', $notification->getDescription());
        $this->assertNotNull($notification->getUrlParams());
    }

    public function testExtendedEntities()
    {
        $notification = new NotificationWelcome();
        $this->assertSame(NotificationService::EVENT_WELCOME, $notification->getEvent());
        $this->assertSame('notification.welcome', $notification->getDescription());

        $notification = new NotificationInvited();
        $this->assertSame(NotificationService::EVENT_INVITED, $notification->getEvent());
        $this->assertSame('notification.invitation', $notification->getDescription());

        $notification = new NotificationListChanged();
        $this->assertSame(NotificationService::EVENT_LIST_CHANGED, $notification->getEvent());
        $this->assertSame('notification.list_changed', $notification->getDescription());

        $notification = new NotificationListArchived();
        $this->assertSame(NotificationService::EVENT_LIST_ARCHIVED, $notification->getEvent());
        $this->assertSame('notification.list_archived', $notification->getDescription());

        $notification = new NotificationListRemoved();
        $this->assertSame(NotificationService::EVENT_LIST_REMOVED, $notification->getEvent());
        $this->assertSame('notification.list_removed', $notification->getDescription());

        $notification = new NotificationUnsubscribed();
        $this->assertSame(NotificationService::EVENT_UNSUBSCRIBED, $notification->getEvent());
        $this->assertSame('notification.unsubscribed', $notification->getDescription());
    }
}
