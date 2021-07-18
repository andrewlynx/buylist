<?php

namespace App\Tests\Entity;

use App\Entity\Notification;
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
        $notification = new Notification();
        $notification
            ->setTaskList($taskList)
            ->setText('text')
            ->setSeen(false)
            ->setUser($user)
            ->setUserInvolved($user2)
            ->setDate($date)
            ->setEvent(3);

        $this->assertNull($notification->getId());
        $this->assertSame($taskList, $notification->getTaskList());
        $this->assertSame('text', $notification->getText());
        $this->assertSame(false, $notification->isSeen());
        $this->assertSame($user, $notification->getUser());
        $this->assertSame($user2, $notification->getUserInvolved());
        $this->assertSame($date, $notification->getDate());
        $this->assertSame(3, $notification->getEvent());
        $this->assertSame(NotificationService::getDescription($notification), $notification->getDescription());
        $this->assertNotNull($notification->getUrlParams());
    }
}