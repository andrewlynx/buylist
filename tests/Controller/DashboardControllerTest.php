<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DashboardControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', static::$container->get('router')->generate('index'));

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/welcome', $client->getResponse()->headers->get('Location'));
    }
}
