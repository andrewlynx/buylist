<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request('GET', ControllerTestHelper::generateRoute('notification_index'));
        $this->assertResponseIsSuccessful();
    }
}