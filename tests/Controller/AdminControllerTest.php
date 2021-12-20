<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\AdminNotificationRepository;
use App\Tests\TestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    use TestTrait;

    public function testIndexNotLogged()
    {
        $client = static::createClient();

        $client->request('GET', ControllerTestHelper::generateRoute('admin_panel'));

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/login', $client->getResponse()->headers->get('Location'));
    }

    public function testIndexNotAdmin()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->catchExceptions(false);

        $this->expectExceptionMessage('Access Denied.');
        $client->request('GET', ControllerTestHelper::generateRoute('admin_panel'));
    }

    public function testIndex()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client, 'admin@example.com');

        $client->request('GET', ControllerTestHelper::generateRoute('admin_panel'));
        $this->assertResponseIsSuccessful();
    }

    public function testCreateAdminNotification()
    {
        $client = static::createClient();

        $user = $this->getUser(1);
        $this->assertCount(0, $user->getAdminNotifications());

        $this->createAdminNotification($client);

        $user = $this->getUser(1);
        $adminNotification = $user->getAdminNotifications()->first();
        $this->assertEquals('Test Notification', $adminNotification->getText());
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testReadAdminNotification()
    {
        $client = static::createClient();
        $this->createAdminNotification($client);

        $admin = $this->findUser('admin@example.com');
        $this->assertFalse($admin->getAdminNotifications()->first()->isSeen());

        $client->request(
            'GET',
            ControllerTestHelper::generateRoute('admin_panel')
        );
        $this->assertContains(
            'Test Notification',
            $client->getResponse()->getContent()
        );

        $client->request(
            'GET',
            ControllerTestHelper::generateRoute(
                'notification_read_admin',
                $admin->getAdminNotifications()->first()->getId()
            )
        );

        /** @var User $admin */
        $admin = $this->findUser('admin@example.com');
        $this->assertTrue($admin->getAdminNotifications()->first()->isSeen());

        $client->request(
            'GET',
            ControllerTestHelper::generateRoute('admin_panel')
        );
        $this->assertNotContains(
            'Test Notification',
            $client->getResponse()->getContent()
        );

        $client->request(
            'GET',
            ControllerTestHelper::generateRoute('admin_remove_read_notifications')
        );
        /** @var AdminNotificationRepository $adminNotificationRepository */
        $adminNotificationRepository = static::$container->get(AdminNotificationRepository::class);
        $count = $adminNotificationRepository->findBy(['seen' => 1]);
        $this->assertEquals(count($count), 0);
    }

    public function testReadAdminNotificationError()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client, 'admin@example.com');

        $client->request(
            'GET',
            ControllerTestHelper::generateRoute('notification_read_admin', 100)
        );

        $this->assertContains(
            '"status":"error"',
            $client->getResponse()->getContent()
        );
    }

    private function createAdminNotification(KernelBrowser $client): KernelBrowser
    {
        $client = ControllerTestHelper::logInUser($client, 'admin@example.com');
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('admin_create_notification', 0)
        );
        $form = $crawler->filter('form[name="create_admin_notification"]')->form();
        $form->setValues([
            'create_admin_notification[text]' => 'Test Notification',
        ]);
        $client->submit($form);
        $client->followRedirect();
        $this->assertContains(
            'Message(s) created',
            $client->getResponse()->getContent()
        );

        return $client;
    }
}
