<?php

namespace App\Tests\UseCase\Notification;

use App\Entity\Notification;
use App\Tests\TestTrait;
use App\UseCase\Notification\NotificationHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemHandlerTest extends WebTestCase
{
    use TestTrait;

    public function testRead(): void
    {
        /** @var NotificationHandler $notificationHandler */
        $notificationHandler = static::$container->get(NotificationHandler::class);

        $notification = (new Notification())
            ->setUser($this->getUser(1));
        $this->assertEquals(false, $notification->isSeen());

        $notificationHandler->read($notification);
        $this->assertEquals(true, $notification->isSeen());
    }

    protected function setUp(): void
    {
        if (null === static::$kernel) {
            self::bootKernel();
        }
    }
}
