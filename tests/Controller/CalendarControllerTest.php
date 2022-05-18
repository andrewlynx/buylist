<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CalendarControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->request('GET', ControllerTestHelper::generateRoute('calendar_index'));
        $this->assertResponseIsSuccessful();

        $this->assertContains(
            'My New Task List',
            $client->getResponse()->getContent()
        );
        $this->assertContains(
            'My New Counter List',
            $client->getResponse()->getContent()
        );
    }

    public function testDay()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            static::$container->get('router')->generate(
                'calendar_day', ['date' => (new \DateTime())->format('Y-m-d'), '_locale' => 'en']
            )
        );

        $this->assertResponseIsSuccessful();
        $this->assertContains(
            'My New Task List',
            $client->getResponse()->getContent()
        );
        $this->assertNotContains(
            'My New Counter List',
            $client->getResponse()->getContent()
        );
    }
}
