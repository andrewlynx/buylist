<?php

namespace App\Tests\UseCase\TaskItem;

use App\DTO\TaskItem\TaskItemComplete;
use App\DTO\TaskItem\TaskItemCreate;
use App\DTO\TaskItem\TaskItemEdit;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskItemRepository;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use App\UseCase\TaskItem\TaskItemHandler;
use App\UseCase\TaskList\TaskListHandler;
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

        $taskItemHandler = $this->getTaskItemHandler();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('error_assign_to_list');
        $taskItemHandler->create($dto, $testUser);
    }

    public function testCreate()
    {
        $dto = $this->getTaskItemDto();
        $testUser = $this->getUser(1);

        $taskItemHandler = $this->getTaskItemHandler();
        $taskItem = $taskItemHandler->create($dto, $testUser);

        $this->assertEquals(2, $taskItem->getId());
        $this->assertEquals('some_name', $taskItem->getName());
        $this->assertEquals('some_qty', $taskItem->getQty());
        $this->assertEquals($testUser, $taskItem->getTaskList()->getCreator());
    }

    public function testCreateArchived()
    {
        $taskListRepository = static::$container->get(TaskListRepository::class);
        $taskList = $taskListRepository->find(1);

        /** @var TaskListHandler $taskListHandler */
        $taskListHandler = static::$container->get(TaskListHandler::class);
        $taskListHandler->archive($taskList, !$taskList->isArchived());

        $dto = $this->getTaskItemDto();
        $testUser = $this->getUser(1);

        $taskItemHandler = $this->getTaskItemHandler();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('list.activate_list_to_edit');
        $taskItem = $taskItemHandler->create($dto, $testUser);
    }

    public function testComplete()
    {
        $dto = $this->getTaskItemDto();
        $testUser = $this->getUser(1);

        $taskItemHandler = $this->getTaskItemHandler();
        $taskItem = $taskItemHandler->create($dto, $testUser);

        $this->assertFalse($taskItem->isCompleted());

        $dataArray = [
            'task_item_complete[completed]' => false,
            'task_item_complete[id]' => $taskItem->getId(),
            'task_item_complete[_token]' => 'some_token',
        ];

        $dto = new TaskItemComplete($dataArray);
        $taskItemHandler->complete($dto, $testUser);
        $this->assertTrue($taskItem->isCompleted());
    }

    public function testEdit()
    {
        $dto = $this->getTaskItemDto();
        $testUser = $this->getUser(1);

        $taskItemHandler = $this->getTaskItemHandler();
        $taskItem = $taskItemHandler->create($dto, $testUser);

        $dto = new TaskItemEdit();
        $dto->taskItem = $taskItem;
        $dto->name = 'new name';
        $dto->qty = 'new qty';

        $taskItemHandler = $this->getTaskItemHandler();
        $editedTaskItem = $taskItemHandler->edit($dto, $this->getUser(1));

        $this->assertEquals($taskItem->getId(), $editedTaskItem->getId());
        $this->assertEquals('new name', $editedTaskItem->getName());
        $this->assertEquals('new qty', $editedTaskItem->getQty());
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

    private function getTaskItemHandler(): TaskItemHandler
    {
        /** @var TaskItemHandler */
        return static::$container->get(TaskItemHandler::class);
    }
}
