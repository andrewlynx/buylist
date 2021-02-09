<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testSettings()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('user_settings')
        );
        $this->assertResponseIsSuccessful();

        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->find(1);
        $this->assertNull($testUser->getLocale());

        $form = $crawler->filter('form[name="user_settings"]')->form();
        $form->setValues([
            'user_settings[locale]' => 'ua',
        ]);
        $client->submit($form);
        $this->assertSame('/ua/user/settings', $client->getResponse()->headers->get('Location'));

        $testUser = $userRepository->find(1);
        $this->assertEquals('ua', $testUser->getLocale());
    }

    public function testSettingsPasswordNotValid()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('user_settings')
        );
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="user_settings"]')->form();
        $form->setValues([
            'user_settings[current_password]' => 'wrong_password',
            'user_settings[locale]' => 'en',
        ]);
        $client->submit($form);
        $this->assertContains(
            'Incorrect Current Password',
            $client->getResponse()->getContent()
        );
    }
}
