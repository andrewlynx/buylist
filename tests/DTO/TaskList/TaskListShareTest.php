<?php

namespace App\Tests\DTO\TaskItem;

use App\DTO\TaskList\TaskListShare;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskListShareTest extends WebTestCase
{
    public function testConstructor()
    {
        $dto = new TaskListShare($this->getValidData());
        $this->assertEquals('some_email', $dto->email);
        $this->assertEquals('some_token', $dto->token);

        $dto = new TaskListShare($this->getInvalidData());
        $this->assertNotEquals('some_name', $dto->email);
        $this->assertNotEquals('some_token', $dto->token);
    }

    private function getValidData()
    {
        return [
            'share_list_email[email]' => 'some_email',
            'share_list_email[_token]' => 'some_token',
        ];
    }

    private function getInvalidData()
    {
        return [
            'email' => 'some_email',
            '_token' => 'some_token',
        ];
    }
}
