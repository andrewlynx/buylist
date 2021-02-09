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

    public static function generateRoute(string $route, ?int $id = null, string $locale = 'en'): string
    {
        if ($id !== null) {
            $params = ['id' => $id, '_locale' => $locale];
        } else {
            $params = ['_locale' => $locale];
        }
        return static::$container->get('router')->generate($route, $params);
    }

    public static function getToken(string $name): string
    {
        return static::$container->get('security.csrf.token_manager')->getToken($name)->getValue();
    }
}
