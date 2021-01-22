<?php

namespace App\Tests\DTO\TaskItem;

use App\DTO\TaskList\TaskListShare;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskListShareTest extends WebTestCase
{
    public function testConstructor()
    {
        $dataArray = [
            'share_list_email[email]' => 'some_email',
            'share_list_email[_token]' => 'some_token',
        ];

        $dto = new TaskListShare($dataArray);
        $this->assertEquals('some_email', $dto->email);
        $this->assertEquals('some_token', $dto->token);

        $incorrectDataArray = [
            'email' => 'some_email',
            '_token' => 'some_token',
        ];
        $dto = new TaskListShare($incorrectDataArray);
        $this->assertNotEquals('some_name', $dto->email);
        $this->assertNotEquals('some_token', $dto->token);
    }
}