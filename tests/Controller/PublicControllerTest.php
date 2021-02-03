<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PublicControllerTest extends WebTestCase
{
    public function testWelcome()
    {
        $client = static::createClient();

        $client->request('GET', static::$container->get('router')->generate('welcome', ['_locale' => 'en']));
        $this->assertResponseIsSuccessful();

        $client = ControllerTestHelper::logInUser($client);
        $client->request('GET', static::$container->get('router')->generate('welcome', ['_locale' => 'en']));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/task-list/', $client->getResponse()->headers->get('Location'));
    }

    public function testAbout()
    {
        $client = static::createClient();

        $client->request('GET', static::$container->get('router')->generate('about', ['_locale' => 'en']));
        $this->assertResponseIsSuccessful();

        $client = ControllerTestHelper::logInUser($client);
        $client->request('GET', static::$container->get('router')->generate('about', ['_locale' => 'en']));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/task-list/', $client->getResponse()->headers->get('Location'));
    }
}