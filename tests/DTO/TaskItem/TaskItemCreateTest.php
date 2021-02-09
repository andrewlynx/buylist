<?php

namespace App\Tests\DTO\TaskItem;

use App\DTO\TaskItem\TaskItemCreate;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemCreateTest extends WebTestCase
{
    protected function setUp(): void
    {
        if (null === static::$kernel) {
            self::bootKernel();
        }
    }

    public function testConstructor()
    {
        $dataArray = [
            'task_item_create[name]' => 'some_name',
            'task_item_create[qty]' => 'some_qty',
            'task_item_create[list_id]' => 5,
            'task_item_create[_token]' => 'some_token',
        ];

        $dto = new TaskItemCreate($dataArray);
        $this->assertEquals('some_name', $dto->name);
        $this->assertEquals('some_qty', $dto->qty);
        $this->assertEquals(5, $dto->listId);
        $this->assertEquals('some_token', $dto->token);

        $incorrectDataArray = [
            'name' => 2,
            'qty' => 1,
            'list_id' => 1,
            '_token' => 'some_token',
        ];
        $dto = new TaskItemCreate($incorrectDataArray);
        $this->assertNotEquals('some_name', $dto->name);
        $this->assertNotEquals('some_qty', $dto->qty);
        $this->assertNotEquals(5, $dto->listId);
        $this->assertNotEquals('some_token', $dto->token);
    }
}
