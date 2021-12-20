<?php

namespace App\Tests\Controller;

use App\Tests\TestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;

class RegistrationControllerTest extends WebTestCase
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
}
