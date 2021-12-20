<?php

namespace App\Tests\DTO\TaskItem;

use App\DTO\TaskItem\TaskItemComplete;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemCompleteTest extends WebTestCase
{
    public function testConstructor()
    {
        $dto = new TaskItemComplete($this->getValidData());
        $this->assertEquals(2, $dto->id);
        $this->assertTrue($dto->completed);
        $this->assertEquals('some_token', $dto->token);

        $dto = new TaskItemComplete($this->getInvalidData());
        $this->assertNotEquals(2, $dto->id);
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
            'task_item_complete[id]' => 2,
            'task_item_complete[completed]' => 1,
            'task_item_complete[_token]' => 'some_token',
        ];
    }

    private function getInvalidData()
    {
        return [
            'id' => 2,
            'completed' => 1,
            '_token' => 'some_token',
        ];
    }
}
