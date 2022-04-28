<?php

namespace App\Tests\Controller\JsonApi;

use App\DTO\TaskList\TaskListUsersRaw;
use App\Entity\Notification;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Tests\Controller\ControllerTestHelper;
use App\Tests\TestTrait;
use App\UseCase\TaskList\TaskListHandler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationControllerTest extends WebTestCase
{
    use TestTrait;

    public function testNotificationRead()
    {
        $client = static::createClient();
        $taskList = $this->getTaskList(1);
        $this->createNotification($taskList);

        $client = ControllerTestHelper::logInUser($client, 'user2@example.com');
        $client->request('GET', ControllerTestHelper::generateRoute('task_list_index'));
        $this->assertContains(
            'invited you to list',
            $client->getResponse()->getContent()
        );
        $client = $this->readNotification($client, 1);
        $this->assertResponseIsSuccessful();
        $this->assertContains('read', $client->getResponse()->getContent());
    }

    public function testNotificationReadWrongUser()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $taskList = $this->getTaskList(1);
        $this->createNotification($taskList);

        $client = $this->readNotification($client, 2);
        $this->assertContains('Invalid form data', $client->getResponse()->getContent());
    }

    public function testNotificationReadFail()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client, 'user2@example.com');
        $taskList = $this->getTaskList(1);
        $this->createNotification($taskList);

        $client = $this->readNotificationNotJson($client, 3);
        $this->assertContains('Incorrect data format', $client->getResponse()->getContent());
    }

    public function testCheckUpdatesWrongMethod()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            ControllerTestHelper::generateRoute('notification_check_updates')
        );
        $this->assertResponseStatusCodeSame(405);
    }

    public function testCheckUpdatesNoUpdates()
    {
        $client = static::createClient();
        $user = $this->getUser(2);
        $client->loginUser($user);

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('notification_check_updates')
        );
        $this->assertResponseIsSuccessful();
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $responseArray);
        $this->assertContains('error', $responseArray);
    }

    public function testCheckUpdates()
    {
        $client = static::createClient();
        $user = $this->getUser(2);
        $user = $this->setLastVisitTime($user);
        $client->loginUser($user);

        $taskList = $this->getTaskList(2);
        $this->createNotification($taskList);

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('notification_check_updates')
        );
        $this->assertResponseIsSuccessful();
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $responseArray);
        $this->assertContains('success', $responseArray);
    }

    private function createNotification(TaskList $taskList)
    {
        $taskListHandler = $this->getTaskListHandler();
        $taskListUsersRaw = new TaskListUsersRaw([
            [
                "email" => "user2@example.com",
                "active" => "1"
            ],
        ]);
        $taskListHandler->processSharedList($taskListUsersRaw, $taskList);
    }

    private function getTaskListHandler(): TaskListHandler
    {
        /** @var TaskListHandler */
        return static::$container->get(TaskListHandler::class);
    }

    private function setLastVisitTime(User $user)
    {
        $newTime = strtotime('-5 minutes');
        $user->setLastLogin(new \DateTime(date('Y-m-d H:i:s', $newTime)));
        $user->setPreviousVisitTime(new \DateTime(date('Y-m-d H:i:s', $newTime)));
        static::$container->get('doctrine.orm.entity_manager')->persist($user);
        return $user;
    }

    private function readNotification(KernelBrowser $client, int $id): KernelBrowser
    {
        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('notification_read', $id),
            [],
            [],
            [],
            json_encode([
                '_token' => ControllerTestHelper::getToken('read_notification'.$id),
            ])
        );

        return $client;
    }

    private function readNotificationNotJson(KernelBrowser $client, int $id): KernelBrowser
    {
        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('notification_read', $id),
            [],
            [],
            [],
            'some_data'
        );

        return $client;
    }
}
