<?php

namespace App\Tests\DTO\TaskItem;

use App\DTO\TaskItem\TaskItemCreate;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemCreateTest extends WebTestCase
{
    public function testConstructor()
    {
        $dto = new TaskItemCreate($this->getValidData());
        $this->assertEquals('some_name', $dto->name);
        $this->assertEquals('some_qty', $dto->qty);
        $this->assertEquals(5, $dto->listId);
        $this->assertEquals('some_token', $dto->token);

        $dto = new TaskItemCreate($this->getInvalidData());
        $this->assertNotEquals('some_name', $dto->name);
        $this->assertNotEquals('some_qty', $dto->qty);
        $this->assertNotEquals(5, $dto->listId);
        $this->assertNotEquals('some_token', $dto->token);
    }

    protected function setUp(): void
    {
        if (null === static::$kernel) {
            self::bootKernel();
        }
    }

    private function getValidData()
    {
        return [
            'task_item_create[name]' => 'some_name',
            'task_item_create[qty]' => 'some_qty',
            'task_item_create[list_id]' => 5,
            'task_item_create[_token]' => 'some_token',
        ];
    }

    private function getInvalidData()
    {
        return [
            'name' => 2,
            'qty' => 1,
            'list_id' => 1,
            '_token' => 'some_token',
        ];
    }
}
