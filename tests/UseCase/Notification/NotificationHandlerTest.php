<?php

use App\Entity\Notification;
use App\Repository\UserRepository;
use App\UseCase\Notification\NotificationHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemHandlerTest extends WebTestCase
{
    protected function setUp(): void
    {
        if (null === static::$kernel) {
            self::bootKernel();
        }
    }

    public function testRead()
    {
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->find(1);

        /** @var NotificationHandler $notificationHandler */
        $notificationHandler = static::$container->get(NotificationHandler::class);

        $notification = (new Notification())
            ->setUser($user);
        $this->assertEquals(false, $notification->isSeen());

        $notificationHandler->read($notification);
        $this->assertEquals(true, $notification->isSeen());
    }
}