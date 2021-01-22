<?php

namespace App\Tests\DTO\TaskItem;

use App\DTO\TaskItem\TaskItemComplete;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemCompleteTest extends WebTestCase
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
            'task_item_complete[id]' => 2,
            'task_item_complete[completed]' => 1,
            'task_item_complete[_token]' => 'some_token',
        ];

        $dto = new TaskItemComplete($dataArray);
        $this->assertEquals(2, $dto->id);
        $this->assertTrue($dto->completed);
        $this->assertEquals('some_token', $dto->token);

        $incorrectDataArray = [
            'id' => 2,
            'completed' => 1,
            '_token' => 'some_token',
        ];
        $dto = new TaskItemComplete($incorrectDataArray);
        $this->assertNotEquals(2, $dto->id);
        $this->assertNotEquals('some_token', $dto->token);
    }
}