<?php

namespace App\Tests\Controller;

use App\Constant\AppConstant;
use App\DTO\TaskList\TaskListShare;
use App\Entity\TaskList;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskListControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', $client->getContainer()->get('router')->generate('task_list_index', ['_locale' => 'en']));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/login', $client->getResponse()->headers->get('Location'));

        $client = $this->logInUser($client);
        $client->request('GET', $client->getContainer()->get('router')->generate('task_list_index', ['_locale' => 'en']));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testCreate()
    {
        $client = static::createClient();

        /** @var TaskListRepository $userRepository */
        $listRepository = static::$container->get(TaskListRepository::class);

        $this->assertCount(1, $listRepository->findAll());

        $client = $this->logInUser($client);
        $client->request('GET', $this->generateRoute('task_list_create', 1));
        $this->assertCount(2, $listRepository->findAll());
    }

    public function testView()
    {
        $client = static::createClient();

        $client = $this->logInUser($client);

        $client->request('GET', $this->generateRoute('task_list_view', 1));
        $this->assertResponseIsSuccessful();
    }

    public function testViewAccessDenied()
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client = $this->logInUser($client, 'user2@example.com');
        $this->expectExceptionMessage('Access Denied.');
        $client->request('GET', $this->generateRoute('task_list_view', 1));
    }

    public function testDeleteInvalidCsrf()
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client = $this->logInUser($client);
        $this->expectExceptionMessage('Invalid CSRF token');
        $client->request('GET', $this->generateRoute('task_list_delete', 1));
    }

    public function testDeleteAccessDenied()
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client = $this->logInUser($client, 'user3@example.com');
        $this->expectExceptionMessage('Access Denied.');
        $client->request(
            'DELETE',
            $this->generateRoute('task_list_delete', 1),
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
            $this->generateRoute('task_list_delete', 1),
            [
                '_token' => $this->getToken('delete1'),
            ]
        );
        $this->assertResponseStatusCodeSame(302);

        /** @var TaskListRepository $userRepository */
        $listRepository = static::$container->get(TaskListRepository::class);
        $this->assertCount(0, $listRepository->findAll());
    }

    public function testTaskListShareJsonFail()
    {
        $client = static::createClient();
        $client = $this->logInUser($client);

        $client->request(
            'POST',
            $this->generateRoute('task_list_share', 1),
            [],
            [],
            [],
            'not-json'
        );
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $responseArray);
        $this->assertArrayHasKey('data', $responseArray);
        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_ERROR);
    }

    public function testTaskListShareTokenFail()
    {
        $client = static::createClient();
        $client = $this->logInUser($client);

        $client->request(
            'POST',
            $this->generateRoute('task_list_share', 1),
            [],
            [],
            [],
            json_encode([
                'share_list_email[_token]' => 'wrong_token',
                'share_list_email[email]' => 'email@example.com'
            ])
        );
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_ERROR);
        $this->assertEquals($responseArray['data'], 'Invalid CSRF token');
    }

    public function testTaskListShareExistingUser()
    {
        $client = static::createClient();
        $client = $this->logInUser($client);

        $client->request(
            'POST',
            $this->generateRoute('task_list_share', 1),
            [],
            [],
            [],
            json_encode([
                'share_list_email[_token]' => $this->getToken(TaskListShare::FORM_NAME),
                'share_list_email[email]' => 'user2@example.com'
            ])
        );
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_SUCCESS);

        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user2@example.com');

        /** @var TaskList $taskList */
        $taskList = static::$container->get(TaskListRepository::class)->find(1);
        $this->assertTrue($taskList->getShared()->contains($testUser));
    }

    public function testTaskListShareAuthor()
    {
        $client = static::createClient();
        $client = $this->logInUser($client);

        $client->request(
            'POST',
            $this->generateRoute('task_list_share', 1),
            [],
            [],
            [],
            json_encode([
                'share_list_email[_token]' => $this->getToken(TaskListShare::FORM_NAME),
                'share_list_email[email]' => 'user1@example.com'
            ])
        );
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_ERROR);
        $this->assertEquals($responseArray['data'], 'This user is this List author');
    }

    private function logInUser(KernelBrowser $client, $email = 'user1@example.com'): KernelBrowser
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail($email);
        $client->loginUser($testUser);

        return $client;
    }

    private function generateRoute(string $route, int $id, string $locale = 'en'): string
    {
        return static::$container->get('router')->generate($route, ['id' => $id, '_locale' => $locale]);
    }

    private function getToken(string $name): string
    {
        return static::$container->get('security.csrf.token_manager')->getToken($name)->getValue();
    }
}