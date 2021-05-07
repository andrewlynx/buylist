<?php

namespace App\Tests\DTO\TaskItem;

use App\DTO\TaskItem\TaskItemEdit;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemEditTest extends WebTestCase
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
            'task_item_edit[name]' => 'some_name',
            'task_item_edit[qty]' => 'some_qty',
            'task_item_edit[_token]' => 'some_token',
        ];

        $dto = new TaskItemEdit($dataArray);
        $this->assertEquals('some_name', $dto->name);
        $this->assertEquals('some_qty', $dto->qty);
        $this->assertEquals('some_token', $dto->token);

        $incorrectDataArray = [
            'name' => 2,
            'qty' => 1,
            '_token' => 'some_token',
        ];
        $dto = new TaskItemEdit($incorrectDataArray);
        $this->assertNotEquals('some_name', $dto->name);
        $this->assertNotEquals('some_qty', $dto->qty);
        $this->assertNotEquals('some_token', $dto->token);
    }
}
