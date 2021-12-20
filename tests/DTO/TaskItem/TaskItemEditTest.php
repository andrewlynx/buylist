<?php

namespace App\Tests\DTO\TaskItem;

use App\DTO\TaskItem\TaskItemEdit;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemEditTest extends WebTestCase
{
    public function testConstructor()
    {
        $dto = new TaskItemEdit($this->getValidData());
        $this->assertEquals('some_name', $dto->name);
        $this->assertEquals('some_qty', $dto->qty);
        $this->assertEquals('some_token', $dto->token);

        $dto = new TaskItemEdit($this->getInvalidData());
        $this->assertNotEquals('some_name', $dto->name);
        $this->assertNotEquals('some_qty', $dto->qty);
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
            'task_item_edit[name]' => 'some_name',
            'task_item_edit[qty]' => 'some_qty',
            'task_item_edit[_token]' => 'some_token',
        ];
    }

    private function getInvalidData()
    {
        return [
            'name' => 2,
            'qty' => 1,
            '_token' => 'some_token',
        ];
    }
}
