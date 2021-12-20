<?php

namespace App\Tests\Controller;

use App\Constant\AppConstant;
use App\DTO\TaskItem\TaskItemComplete;
use App\DTO\TaskItem\TaskItemCreate;
use App\Entity\TaskItem;
use App\Tests\TestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemControllerTest extends WebTestCase
{
    use TestTrait;

    public function testTaskItemCreateJsonFail()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('task_item_create', 1),
            [],
            [],
            [],
            'not-json'
        );
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $responseArray);
        $this->assertArrayHasKey('data', $responseArray);
        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_ERROR);
    }

    public function testTaskItemCreateTokenFail()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('task_item_create', 1),
            [],
            [],
            [],
            json_encode([
                'task_item_create[_token]' => 'wrong_token',
                'task_item_create[name]' => 'some item'
            ])
        );
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_ERROR);
        $this->assertEquals($responseArray['data'], 'Invalid CSRF token');
    }

    public function testTaskItemCreateAndComplete()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client = $this->createTaskItem($client);

        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_SUCCESS);

        $taskList = $this->getTaskList(1);
        /** @var TaskItem $taskItem */
        $taskItem = $taskList->getTaskItems()->first();

        $this->assertEquals(1, $taskItem->getId());
        $this->assertEquals('some item', $taskItem->getName());
        $this->assertEquals('2', $taskItem->getQty());
        $this->assertEquals(false, $taskItem->isCompleted());

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('task_item_complete', 1),
            [],
            [],
            [],
            json_encode([
                'task_item_complete[_token]' => ControllerTestHelper::getToken(TaskItemComplete::FORM_NAME),
                'task_item_complete[completed]' => false,
                'task_item_complete[id]' => 1,
            ])
        );
        $responseArray = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_SUCCESS);

        $taskList = $this->getTaskList(1);
        /** @var TaskItem $taskItem */
        $taskItem = $taskList->getTaskItems()->first();
        $this->assertEquals(true, $taskItem->isCompleted());
    }

    public function testTaskItemCompleteJsonFail()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('task_item_complete', 1),
            [],
            [],
            [],
            'not-json'
        );
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $responseArray);
        $this->assertArrayHasKey('data', $responseArray);
        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_ERROR);
    }

    public function testTaskItemCompleteTokenFail()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('task_item_complete', 1),
            [],
            [],
            [],
            json_encode([
                'task_item_create[_token]' => 'wrong_token',
                'task_item_complete[id]' => 1,
            ])
        );
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_ERROR);
        $this->assertEquals($responseArray['data'], 'Invalid CSRF token');
    }

    private function createTaskItem(KernelBrowser $client): KernelBrowser
    {
        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('task_item_create', 1),
            [],
            [],
            [],
            json_encode([
                'task_item_create[_token]' => ControllerTestHelper::getToken(TaskItemCreate::FORM_NAME),
                'task_item_create[name]' => 'some item',
                'task_item_create[qty]' => '2',
                'task_item_create[list_id]' => 1,
            ])
        );

        return $client;
    }
}
