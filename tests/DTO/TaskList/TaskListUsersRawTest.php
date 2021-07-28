<?php

namespace App\Tests\DTO\TaskItem;

use App\DTO\TaskList\TaskListUsersRaw;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskListUsersRawTest extends WebTestCase
{
    public function testMethods()
    {
        $testData = [
            [
                "email" => "test4@test.test",
                "active" => "1"
            ],
            [
                "email" => "test3@test.test",
                "active" => "0"
            ],
            [
                "email" => "test@test.test"
            ],
        ];
        $taskListUsersDTO = new TaskListUsersRaw($testData);

        $this->assertEquals(1, count($taskListUsersDTO->users));
    }
}
