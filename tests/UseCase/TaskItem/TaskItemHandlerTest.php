<?php

namespace App\Tests\UseCase\TaskItem;

use App\DTO\TaskItem\TaskItemComplete;
use App\DTO\TaskItem\TaskItemCreate;
use App\Entity\User;
use App\Repository\UserRepository;
use App\UseCase\TaskItem\TaskItemHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemHandlerTest extends WebTestCase
{
    protected function setUp(): void
    {
        if (null === static::$kernel) {
            self::bootKernel();
        }
    }

    public function testCreateWrongUser()
    {
        $dto = $this->getTaskItemDto();
        $testUser = $this->getUser(2);

        /** @var TaskItemHandler $taskItemHandler */
        $taskItemHandler = static::$container->get(TaskItemHandler::class);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error assigning new Item to List');
        $taskItemHandler->create($dto, $testUser);
    }

    public function testCreate()
    {
        $dto = $this->getTaskItemDto();
        $testUser = $this->getUser(1);

            /** @var TaskItemHandler $taskItemHandler */
        $taskItemHandler = static::$container->get(TaskItemHandler::class);
        $taskItem = $taskItemHandler->create($dto, $testUser);

        $this->assertEquals(1, $taskItem->getId());
        $this->assertEquals('some_name', $taskItem->getName());
        $this->assertEquals('some_qty', $taskItem->getQty());
        $this->assertEquals($testUser, $taskItem->getTaskList()->getCreator());
    }

    public function testComplete()
    {
        $dto = $this->getTaskItemDto();
        $testUser = $this->getUser(1);

        /** @var TaskItemHandler $taskItemHandler */
        $taskItemHandler = static::$container->get(TaskItemHandler::class);
        $taskItem = $taskItemHandler->create($dto, $testUser);

        $this->assertFalse($taskItem->isCompleted());

        $dataArray = [
            'task_item_complete[completed]' => false,
            'task_item_complete[id]' => $taskItem->getId(),
            'task_item_complete[_token]' => 'some_token',
        ];

        $dto = new TaskItemComplete($dataArray);
        $taskItemHandler->complete($dto);
        $this->assertTrue($taskItem->isCompleted());
    }

    private function getTaskItemDto(): TaskItemCreate
    {
        $dataArray = [
            'task_item_create[name]' => 'some_name',
            'task_item_create[qty]' => 'some_qty',
            'task_item_create[list_id]' => 1,
            'task_item_create[_token]' => 'some_token',
        ];

        return new TaskItemCreate($dataArray);
    }

    private function getUser(int $id): User
    {
        $userRepository = static::$container->get(UserRepository::class);

        return $userRepository->find($id);
    }
}