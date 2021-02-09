<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginIncorrectEmail()
    {
        $client = static::createClient();
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_login')
        );
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="login_form"]')->form();
        $form->setValues([
            'email' => 'some_invalid_email',
            'password' => 'some_password',
        ]);
        $client->submit($form);
        $client->followRedirect();

        $this->assertContains(
            'Email could not be found',
            $client->getResponse()->getContent()
        );
    }

    public function testLogin()
    {
        $client = static::createClient();
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_login')
        );
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="login_form"]')->form();
        $form->setValues([
            'email' => 'user1@example.com',
            'password' => 'test',
        ]);
        $client->submit($form);
        $client->followRedirect();
        $client->followRedirect();

        $this->assertSame('/en/task-list/', $client->getResponse()->headers->get('Location'));
    }
}
