<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;

class SecurityControllerTest extends ControllerTestHelper
{
    public function testLoginIncorrectEmail()
    {
        $client = static::createClient();
        $form = $this->getForm($client);

        $form->setValues($this->getInvalidData());
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
        $form = $this->getForm($client);

        $form->setValues($this->getValidData());
        $client->submit($form);
        $client->followRedirect();
        $client->followRedirect();

        $this->assertSame('/en/task-list/', $client->getResponse()->headers->get('Location'));
    }

    public function testLoginAlreadyLoggedIn()
    {
        $client = static::createClient();
        $client = $this->logInUser($client);
        $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_login')
        );
        $this->assertResponseRedirects();
        $client->followRedirect();
        $client->followRedirect();
        $this->assertSame('/en/task-list/', $client->getResponse()->headers->get('Location'));
    }

    public function testLogout()
    {
        $client = static::createClient();
        $client = $this->logInUser($client);
        $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_logout')
        );
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSame('/en/welcome', $client->getResponse()->headers->get('Location'));
    }

    private function getForm(KernelBrowser $client): Form
    {
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_login')
        );
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="login_form"]')->form();

        return $form;
    }

    private function getValidData(): array
    {
        return [
            'email' => 'user1@example.com',
            'password' => 'test',
        ];
    }

    private function getInvalidData(): array
    {
        return [
            'email' => 'some_invalid_email',
            'password' => 'some_password',
        ];
    }
}
