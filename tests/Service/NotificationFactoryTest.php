<?php

namespace App\Tests\Service;

use App\Entity\TaskList;
use App\Entity\User;
use App\Service\Notification\NotificationFactory;
use App\Service\Notification\NotificationService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationFactoryTest extends WebTestCase
{
    public function testInvalid()
    {
        $user = new User();
        $this->expectExceptionMessage('event.invalid_event');
        $notification = NotificationFactory::make(-67, $user);
    }

    public function testWelcome()
    {
        $user = new User();
        $notification = NotificationFactory::make(NotificationService::EVENT_WELCOME, $user);

        $this->assertSame($user, $notification->getUser());
        $this->assertSame(NotificationService::EVENT_WELCOME, $notification->getEvent());
    }

    public function testValidationFail()
    {
        $user = new User();
        $taskList = new TaskList();
        $this->expectException(\InvalidArgumentException::class);
        $notification = NotificationFactory::make(NotificationService::EVENT_INVITED, $user, $taskList);
    }

    public function testInvited()
    {
        $user = new User();
        $user2 = new User();
        $taskList = new TaskList();
        $notification = NotificationFactory::make(NotificationService::EVENT_INVITED, $user, $taskList, $user2);

        $this->assertSame($user, $notification->getUser());
        $this->assertSame(NotificationService::EVENT_INVITED, $notification->getEvent());
        $this->assertSame($taskList, $notification->getTaskList());
        $this->assertSame($user2, $notification->getUserInvolved());
    }

    public function testRemoved()
    {
        $user = new User();
        $user2 = new User();
        $taskList = new TaskList();
        $notification = NotificationFactory::make(NotificationService::EVENT_LIST_REMOVED, $user, $taskList, $user2, 'text');

        $this->assertSame($user, $notification->getUser());
        $this->assertSame(NotificationService::EVENT_LIST_REMOVED, $notification->getEvent());
        $this->assertSame(null, $notification->getTaskList());
        $this->assertSame('text', $notification->getText());
        $this->assertSame($user2, $notification->getUserInvolved());
    }

    public function testRemovedValidationFail()
    {
        $user = new User();
        $this->expectException(\InvalidArgumentException::class);
        $notification = NotificationFactory::make(NotificationService::EVENT_INVITED, $user);
    }
}