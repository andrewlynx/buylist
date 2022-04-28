<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\TestTrait;
use App\UseCase\User\UserHandler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserControllerTest extends WebTestCase
{
    use TestTrait;

    public function testSettings()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('user_settings')
        );
        $this->assertResponseIsSuccessful();

        $testUser = $this->getUser(1);
        $this->assertNull($testUser->getLocale());

        $form = $crawler->filter('form[name="user_settings"]')->form();
        $form->setValues([
            'user_settings[locale]' => 'ua',
        ]);
        $client->submit($form);
        $this->assertSame('/ua/user/settings', $client->getResponse()->headers->get('Location'));

        $testUser = $this->getUser(1);
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

    public function testUsersPage()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            ControllerTestHelper::generateRoute('user_users')
        );
        $this->assertResponseIsSuccessful();
    }

    public function testUserFound()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            'en/user/show/user2@example.com'
        );
        $this->assertResponseIsSuccessful();
    }

    public function testUserNotFound()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);
        $client->request(
            'GET',
            'en/user/show/incorrect_user@name'
        );
    }

    public function testAddUserToFavourites()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            'en/user/add-to-favourites/user2@example.com'
        );
        $client->followRedirect();
        $this->assertContains('Added to favourites', $client->getResponse()->getContent());

        $testUser = $this->getUser(1);
        $favouriteUser = $this->getUser(2);
        $this->assertContains($favouriteUser, $testUser->getFavouriteUsers());
    }

    public function testAddToFavouritesIncorrectUser()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            'en/user/add-to-favourites/incorrect_user@name'
        );
        $client->followRedirect();
        $this->assertContains(
            'User not found',
            $client->getResponse()->getContent()
        );
    }

    public function testRemoveUserFromFavourites()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        /** @var UserHandler $userHandler */
        $userHandler = static::$container->get(UserHandler::class);

        /** @var User $testUser */
        $testUser = $this->getUser(1);
        $favouriteUser = $this->getUser(2);
        $userHandler->addToFavourites($testUser, $favouriteUser);

        $client->request(
            'GET',
            'en/user/remove-from-favourites/user2@example.com'
        );
        $client->followRedirect();
        $this->assertContains('Removed from favourites', $client->getResponse()->getContent());

        $testUser = $this->getUser(1);
        $favouriteUser = $this->getUser(2);
        $this->assertNotContains($favouriteUser, $testUser->getFavouriteUsers());
    }

    public function testRemoveFromFavouritesIncorrectUser()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            'en/user/remove-from-favourites/incorrect_user@name'
        );
        $client->followRedirect();
        $this->assertContains(
            'User not found',
            $client->getResponse()->getContent()
        );
    }

    public function testBlockUser()
    {
        $client = static::createClient();

        $user = $this->getUser(1);
        $user2 = $this->getUser(2);
        $taskList2 = $this->getTaskList(2);
        $taskList3 = $this->getTaskList(3);
        $this->assertTrue($taskList2->getShared()->contains($user2));
        $this->assertTrue($taskList3->getShared()->contains($user));

        $this->blockUser($client);

        $this->assertTrue($user->isBanned($user2));
        $taskList2 = $this->getTaskList(2);
        $taskList3 = $this->getTaskList(3);
        $this->assertFalse($taskList2->getShared()->contains($user2));
        $this->assertFalse($taskList3->getShared()->contains($user));
    }

    public function testBlockUserWrongEmail()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            '/en/user/block/some@wrong.email'
        );
        $client->followRedirect();
        $this->assertContains(
            'User not found',
            $client->getResponse()->getContent()
        );
    }

    public function testUnblockUser()
    {
        $client = static::createClient();
        $this->blockUser($client);
        $user = $this->getUser(1);
        $user2 = $this->getUser(2);
        $this->assertTrue($user->isBanned($user2));

        $client->request(
            'GET',
            '/en/user/unblock/user2@example.com'
        );
        $client->followRedirect();
        $this->assertContains(
            'User unblocked',
            $client->getResponse()->getContent()
        );

        $user = $this->getUser(1);
        $user2 = $this->getUser(2);
        $this->assertFalse($user->isBanned($user2));
    }

    public function testUnblockUserWrongEmail()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            '/en/user/unblock/some@wrong.email'
        );
        $client->followRedirect();
        $this->assertContains(
            'User not found',
            $client->getResponse()->getContent()
        );
    }

    private function blockUser(KernelBrowser $client): KernelBrowser
    {
        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            '/en/user/block/user2@example.com'
        );
        $client->followRedirect();
        $this->assertContains(
            'User blocked',
            $client->getResponse()->getContent()
        );

        return $client;
    }
}
