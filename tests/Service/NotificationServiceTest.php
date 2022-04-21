<?php

namespace App\Tests\Service;

use App\Entity\Notification;
use App\Entity\NotificationInvited;
use App\Entity\NotificationListArchived;
use App\Entity\NotificationListRemoved;
use App\Entity\TaskList;
use App\Entity\User;
use App\Service\Notification\NotificationService;
use App\Tests\TestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class NotificationServiceTest extends WebTestCase
{
    use TestTrait;

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

    public function testGetUrlParams()
    {
        $taskList = new TaskList();
        $notification = (new NotificationListArchived())->setTaskList($taskList);
        $this->assertEquals(0, NotificationService::getUrlParams($notification)['id']);

        $notification = (new NotificationListRemoved());
        $this->assertNull(NotificationService::getUrlParams($notification));
    }

    private function getNS(): NotificationService
    {
        $client = static::createClient();
        $session = static::$container->get('session');

        $this->user = $this->getUser(1);

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
