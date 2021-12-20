<?php

namespace App\Tests\UseCase\TaskItem;

use App\DTO\TaskItem\TaskItemComplete;
use App\DTO\TaskItem\TaskItemCreate;
use App\DTO\TaskItem\TaskItemEdit;
use App\Entity\TaskItem;
use App\Tests\TestTrait;
use App\UseCase\TaskItem\TaskItemHandler;
use App\UseCase\TaskList\TaskListHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemHandlerTest extends WebTestCase
{
    use TestTrait;

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

        $this->assertNotNull($taskItem->getId());
        $this->assertEquals('some_name', $taskItem->getName());
        $this->assertEquals('some_qty', $taskItem->getQty());
        $this->assertEquals($testUser, $taskItem->getTaskList()->getCreator());
    }

    public function testCreateArchived()
    {
        $taskList = $this->getTaskList(1);

        /** @var TaskListHandler $taskListHandler */
        $taskListHandler = static::$container->get(TaskListHandler::class);
        $taskListHandler->archive($taskList, !$taskList->isArchived());

        $dto = $this->getTaskItemDto();
        $testUser = $this->getUser(1);

        $taskItemHandler = $this->getTaskItemHandler();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('list.activate_list_to_edit');
        $taskItemHandler->create($dto, $testUser);
    }

    public function testComplete()
    {
        $dto = $this->getTaskItemDto();
        $testUser = $this->getUser(1);

        $taskItemHandler = $this->getTaskItemHandler();
        $taskItem = $taskItemHandler->create($dto, $testUser);

        $this->assertFalse($taskItem->isCompleted());

        $dto = new TaskItemComplete($this->getCompleteData($taskItem));
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

    protected function setUp(): void
    {
        if (null === static::$kernel) {
            self::bootKernel();
        }
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

    private function getTaskItemHandler(): TaskItemHandler
    {
        /** @var TaskItemHandler */
        return static::$container->get(TaskItemHandler::class);
    }

    private function getCompleteData(TaskItem $taskItem): array
    {
        return [
            'task_item_complete[completed]' => false,
            'task_item_complete[id]' => $taskItem->getId(),
            'task_item_complete[_token]' => 'some_token',
        ];
    }
}
