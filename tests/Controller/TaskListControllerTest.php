<?php

namespace App\Tests\Controller;

use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TaskListControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/task-list');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/login', $client->getResponse()->headers->get('Location'));

        $client = $this->logInUser($client);
        $client->request('GET', '/task-list');
        $this->assertStringContainsString('/task-list', $client->getResponse()->headers->get('Location'));
    }

    public function testCreate()
    {
        $client = static::createClient();

        /** @var TaskListRepository $userRepository */
        $listRepository = static::$container->get(TaskListRepository::class);

        $this->assertCount(1, $listRepository->findAll());

        $client = $this->logInUser($client);
        $client->request('GET', '/task-list/create');
        $this->assertCount(2, $listRepository->findAll());
    }

    public function testView()
    {
        $client = static::createClient();

        $client = $this->logInUser($client);

        $client->request('GET', '/task-list/1');
        $this->assertResponseIsSuccessful();
    }

    public function testViewAccessDenied()
    {
        $client = static::createClient();

        $client = $this->logInUser($client, 'user2@example.com');
        $client->request('GET', '/task-list/1');
        $this->assertResponseStatusCodeSame(403);
    }

    private function logInUser(KernelBrowser $client, $email = 'user1@example.com'): KernelBrowser
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail($email);
        $client->loginUser($testUser);

        return $client;
    }
}