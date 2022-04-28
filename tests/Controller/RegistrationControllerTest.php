<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\TestTrait;
use App\UseCase\Email\RegistrationEmailHandler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Mime\RawMessage;

class RegistrationControllerTest extends ControllerTestHelper
{
    use TestTrait;

    public function testRegister()
    {
        $client = static::createClient();
        $form = $this->getForm($client);

        $form->setValues($this->getValidData());
        $client->submit($form);
        $client->followRedirect();
        $client->followRedirect();
        $client->followRedirect();

        $this->assertContains(
            'No records found',
            $client->getResponse()->getContent()
        );

        $testUser = $this->findUser('some@valid.email');
        $this->assertNotEmpty($testUser);
        $this->assertEquals('en', $testUser->getLocale());
    }

    public function testRegisterLoggedIn()
    {
        $client = static::createClient();
        $client = $this->logInUser($client);
        $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_register')
        );
        $this->assertResponseRedirects();
    }

    public function testRegisterInvalidEmail()
    {
        $client = static::createClient();
        $form = $this->getForm($client);

        $form->setValues($this->getInvalidData());
        $client->submit($form);
        $this->assertContains(
            'Incorrect Email',
            $client->getResponse()->getContent()
        );
    }

    public function testVerifyUserEmail()
    {
        $client = static::createClient();
        $user = $this->getUser(1);
        $client = $this->logInUser($client);

        $message = $this->sendConfirmationEmail($user);
        preg_match_all('/"(https?:\/\/[a-zA-Z0-9\-.]+(\/\S*)?)"/', $message->getHtmlBody(), $urls);
        $confirmationLink = $urls[1][0];

        $client->request(
            'GET',
            $confirmationLink
        );

        $client->followRedirect();
        $this->assertContains("Your email address has been verified.", $client->getResponse()->getContent());
        $user = $this->getUser(1);
        $this->assertEquals(true, $user->isVerified());
    }

    public function testVerifyWrongToken()
    {
        $client = static::createClient();
        $user = $this->getUser(1);
        $client->loginUser($user);
        $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_verify_email').'?wrong_token'
        );
        $client->followRedirect();
        $this->assertContains("The link to verify your email is invalid", $client->getResponse()->getContent());
    }

    public function testVerifyEmailNotLoggedIn()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_verify_email').'?wrong_token'
        );
        $client->followRedirect();
        $this->assertContains("You should be logged in to continue", $client->getResponse()->getContent());
    }

    private function getForm(KernelBrowser $client): Form
    {
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_register')
        );
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="registration_form"]')->form();

        return $form;
    }

    private function getValidData(): array
    {
        return [
            'registration_form[email]' => 'some@valid.email',
            'registration_form[plainPassword]' => 'some_password',
        ];
    }

    private function getInvalidData(): array
    {
        return [
            'registration_form[email]' => 'some_invalid_email',
            'registration_form[plainPassword]' => 'some_password',
        ];
    }

    private function sendConfirmationEmail(User $user): RawMessage
    {
        $emailHandler = self::getContainer()->get(RegistrationEmailHandler::class);
        $emailHandler->sendConfirmationEmail($user);

        $this->assertEmailCount(1);

        $message = $this->getMailerMessage(0);
        $this->assertContains('Confirm my Email', $message->getHtmlBody());

        return $message;
    }
}
