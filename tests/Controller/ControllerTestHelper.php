<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerTestHelper extends WebTestCase
{
    public static function logInUser(KernelBrowser $client, $email = 'user1@example.com'): KernelBrowser
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail($email);
        $client->loginUser($testUser);

        return $client;
    }

    public static function generateRoute(string $route, int $id, string $locale = 'en'): string
    {
        return static::$container->get('router')->generate($route, ['id' => $id, '_locale' => $locale]);
    }

    public static function getToken(string $name): string
    {
        return static::$container->get('security.csrf.token_manager')->getToken($name)->getValue();
    }
}