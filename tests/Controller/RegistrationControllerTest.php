<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegister()
    {
        $client = static::createClient();
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_register')
        );
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="registration_form"]')->form();
        $form->setValues([
            'registration_form[email]' => 'some@valid.email',
            'registration_form[plainPassword]' => 'some_password',
        ]);
        $client->submit($form);
        $client->followRedirect();
        $client->followRedirect();
        $client->followRedirect();

        $this->assertContains(
            'No records found',
            $client->getResponse()->getContent()
        );

        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('some@valid.email');
        $this->assertNotEmpty($testUser);
        $this->assertEquals('en', $testUser->getLocale());
    }

    public function testRegisterInvalidEmail()
    {
        $client = static::createClient();
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('app_register')
        );

        $form = $crawler->filter('form[name="registration_form"]')->form();
        $form->setValues([
            'registration_form[email]' => 'some_invalid_email',
            'registration_form[plainPassword]' => 'some_password',
        ]);
        $client->submit($form);
        $this->assertContains(
            'Incorrect Email',
            $client->getResponse()->getContent()
        );
    }
}
