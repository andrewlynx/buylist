<?php

namespace App\Tests\DTO\TaskItem;

use App\DTO\TaskList\TaskListUsers;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskListUsersTest extends WebTestCase
{
    public function testMethods()
    {
        $taskListUsersDTO = new TaskListUsers();
        $this->assertEmpty($taskListUsersDTO->registered);
        $this->assertEmpty($taskListUsersDTO->invitationSent);
        $this->assertEmpty($taskListUsersDTO->notAllowed);

        $user = new User();
        $taskListUsersDTO->addRegistered($user);
        $taskListUsersDTO->addInvitationSent('some_email');
        $taskListUsersDTO->addNotAllowed('some_other_email');

        $this->assertEquals([$user], $taskListUsersDTO->registered);
        $this->assertEquals(['some_email'], $taskListUsersDTO->invitationSent);
        $this->assertEquals(['some_other_email'], $taskListUsersDTO->notAllowed);
    }
}
