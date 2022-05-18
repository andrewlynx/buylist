<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SearchControllerTest extends WebTestCase
{
    public function testIndexNotLogged()
    {
        $client = static::createClient();

        $client->request('GET', ControllerTestHelper::generateRoute('search_index'));

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/login', $client->getResponse()->headers->get('Location'));
    }

    public function testIndex()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('task_list_index')
        );
        $form = $crawler->filter(".sf")->form();
        $form->setValues([
            'value' => 'New',
        ]);
        $client->submit($form);
        $this->assertContains(
            'My New Task List',
            $client->getResponse()->getContent()
        );
    }
}
