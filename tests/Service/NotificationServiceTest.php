<?php

namespace App\Tests\Service;

use App\Entity\Notification;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use App\Service\Notification\NotificationService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class NotificationServiceTest extends WebTestCase
{
    /**
     * @var User
     */
    private $user;

    public function testGetUnread()
    {
        $notificationService = $this->getNS();
        $this->assertEmpty($notificationService->getUnread());
    }

    public function testCountUnread()
    {
        $notificationService = $this->getNS();
        $this->assertEquals(0, $notificationService->countUnread());
    }

    public function testAdminNotification()
    {
        $notificationService = $this->getNS();
        $this->assertEmpty($notificationService->getAdminNotifications());
    }

    public function testCountUnreadAdmin()
    {
        $notificationService = $this->getNS();
        $this->assertEquals(0, $notificationService->countUnreadAdminNotifications());
    }

    public function testCreateOrUpdate()
    {
        $notificationService = $this->getNS();
        $taskListRepository = static::$container->get(TaskListRepository::class);
        $taskList = $taskListRepository->find(1);
        $userRepository = static::$container->get(UserRepository::class);
        $user2 = $userRepository->find(2);
        $notification = $notificationService->createOrUpdate(2, $this->user, $taskList, $user2);

        $this->assertEquals(1, $notification->getId());
        $this->assertEquals($this->user, $notification->getUser());
        $this->assertEquals($user2, $notification->getUserInvolved());
    }

    public function testGetDescription()
    {
        $notification = (new Notification())->setEvent(1);
        $this->assertEquals('notification.welcome', NotificationService::getDescription($notification));

        $notification = (new Notification())->setEvent(2);
        $this->assertEquals('notification.invitation', NotificationService::getDescription($notification));

        $notification = (new Notification())->setEvent(3);
        $this->assertEquals('notification.list_changed', NotificationService::getDescription($notification));

        $notification = (new Notification())->setEvent(4);
        $this->assertEquals('notification.list_archived', NotificationService::getDescription($notification));

        $notification = (new Notification())->setEvent(5);
        $this->assertEquals('notification.list_removed', NotificationService::getDescription($notification));

        $notification = (new Notification())->setEvent(6);
        $this->assertEquals('notification.unsubscribed', NotificationService::getDescription($notification));

        $notification = (new Notification())->setEvent(-20);
        $this->assertEquals('notification.not_found', NotificationService::getDescription($notification));
    }

    public function testGetUrlParams()
    {
        $taskList = new TaskList();
        $notification = (new Notification())->setEvent(4)->setTaskList($taskList);
        $this->assertEquals(0, NotificationService::getUrlParams($notification)['id']);

        $notification = (new Notification())->setEvent(1);
        $this->assertNull(NotificationService::getUrlParams($notification));
    }

    private function getNS(): NotificationService
    {
        $client = static::createClient();
        $session = static::$container->get('session');

        $userRepository = static::$container->get(UserRepository::class);
        $this->user = $userRepository->find(1);

        $firewallName = 'secure_area';
        $firewallContext = 'secured_area';

        $token = new UsernamePasswordToken($this->user, null, $firewallName, $this->user->getRoles());
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        /** @var UsageTrackingTokenStorage $tokenStorage */
        $tokenStorage = static::$container->get('security.token_storage');
        $tokenStorage->setToken($token);

        $em = static::$container->get('doctrine.orm.entity_manager');
        return new NotificationService($em, $tokenStorage);
    }
}