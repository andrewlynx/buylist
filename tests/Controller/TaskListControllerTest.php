<?php

namespace App\Tests\Controller;

use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class TaskListControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', $client->getContainer()->get('router')->generate('task_list_index'));
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
        $client->request('GET', $client->getContainer()->get('router')->generate('task_list_create'));
        $this->assertCount(2, $listRepository->findAll());
    }

    public function testView()
    {
        $client = static::createClient();

        $client = $this->logInUser($client);

        $client->request('GET', $client->getContainer()->get('router')->generate('task_list_view', ['id' => 1]));
        $this->assertResponseIsSuccessful();
    }

    public function testViewAccessDenied()
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client = $this->logInUser($client, 'user2@example.com');
        $this->expectExceptionMessage('Access Denied.');
        $client->request('GET', $client->getContainer()->get('router')->generate('task_list_view', ['id' => 1]));
    }

    public function testDeleteInvalidCsrf()
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client = $this->logInUser($client);
        $this->expectExceptionMessage('Invalid CSRF token');
        $client->request('GET', $client->getContainer()->get('router')->generate('task_list_delete', ['id' => 1]));
    }

    public function testDeleteAccessDenied()
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client = $this->logInUser($client, 'user3@example.com');
        $this->expectExceptionMessage('Access Denied.');
        $client->request(
            'DELETE',
            $client->getContainer()->get('router')->generate('task_list_delete', ['id' => 1]),
            [
                '_token' => static::$container->get('security.csrf.token_manager')->getToken('delete1'),
            ]
        );
    }

    public function testDelete()
    {
        $client = static::createClient();
        $client = $this->logInUser($client);

        $client->request(
            'DELETE',
            $client->getContainer()->get('router')->generate('task_list_delete', ['id' => 1]),
            [
                '_token' => static::$container->get('security.csrf.token_manager')->getToken('delete1'),
            ]
        );
        $this->assertResponseStatusCodeSame(302);

        /** @var TaskListRepository $userRepository */
        $listRepository = static::$container->get(TaskListRepository::class);
        $this->assertCount(0, $listRepository->findAll());
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