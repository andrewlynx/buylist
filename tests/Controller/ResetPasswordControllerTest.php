<?php

namespace App\Tests\Controller;

use App\Tests\TestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetPasswordControllerTest extends WebTestCase
{
    use TestTrait;

    public function testRequest()
    {
        $client = static::createClient();
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_forgot_password_request')
        );

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="reset_password_request_form"]')->form();
        $form->setValues([
            'reset_password_request_form[email]' => 'user1@example.com',
        ]);
        $client->submit($form);

        $this->assertEmailCount(1);
        $message = $this->getMailerMessage(0);
        $this->assertContains('To reset your password, please visit the following link', $message->getHtmlBody());

        $client->followRedirect();
        $this->assertContains(
            'Success, please login with your new password',
            $client->getResponse()->getContent()
        );
    }

    public function testRequestWrongEmail()
    {
        $client = static::createClient();
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_forgot_password_request')
        );

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="reset_password_request_form"]')->form();
        $form->setValues([
            'reset_password_request_form[email]' => 'some@wrong.email',
        ]);
        $client->submit($form);
        $this->assertEmailCount(0);

        $client->followRedirect();
        $this->assertContains(
            'An email has been sent that contains a link that you can click to reset your password',
            $client->getResponse()->getContent()
        );
    }
}
