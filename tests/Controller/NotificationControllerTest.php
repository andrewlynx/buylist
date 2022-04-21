<?php

namespace App\Tests\Controller;

use App\DTO\TaskList\TaskListUsersRaw;
use App\Entity\TaskList;
use App\Tests\TestTrait;
use App\UseCase\TaskList\TaskListHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationControllerTest extends WebTestCase
{
    use TestTrait;

    public function testIndex()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request('GET', ControllerTestHelper::generateRoute('notification_index'));
        $this->assertResponseIsSuccessful();
    }
}
