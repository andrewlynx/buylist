<?php

namespace App\Tests\Controller\JsonApi;

use App\DTO\TaskList\TaskListUsersRaw;
use App\Entity\TaskList;
use App\Tests\Controller\ControllerTestHelper;
use App\Tests\TestTrait;
use App\UseCase\TaskList\TaskListHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationControllerTest extends WebTestCase
{
    use TestTrait;

    public function testCheckUpdates()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request('POST', ControllerTestHelper::generateRoute('notification_check_updates'));
        $this->assertResponseIsSuccessful();
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $responseArray);
    }

    public function testNotificationRead()
    {
        $client = static::createClient();
        $taskList = $this->getTaskList(1);
        $this->createNotification($taskList);

        $client = ControllerTestHelper::logInUser($client, 'user2@example.com');
        $crawler = $client->request('GET', ControllerTestHelper::generateRoute('task_list_index'));
        $this->assertContains(
            'invited you to list',
            $client->getResponse()->getContent()
        );
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
}
