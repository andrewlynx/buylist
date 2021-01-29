<?php

namespace App\Tests\Controller;

use App\Constant\AppConstant;
use App\DTO\TaskList\TaskListShare;
use App\Entity\TaskList;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class TaskListControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', $client->getContainer()->get('router')->generate('task_list_index', ['_locale' => 'en']));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/login', $client->getResponse()->headers->get('Location'));

        $client = ControllerTestHelper::logInUser($client);
        $client->request('GET', $client->getContainer()->get('router')->generate('task_list_index', ['_locale' => 'en']));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testShared()
    {
        $client = static::createClient();

        $client->request('GET', $client->getContainer()->get('router')->generate('task_list_index_shared', ['_locale' => 'en']));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/login', $client->getResponse()->headers->get('Location'));

        $client = ControllerTestHelper::logInUser($client);
        $client->request('GET', $client->getContainer()->get('router')->generate('task_list_index_shared', ['_locale' => 'en']));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testCreate()
    {
        $client = static::createClient();

        /** @var TaskListRepository $userRepository */
        $listRepository = static::$container->get(TaskListRepository::class);

        $this->assertCount(1, $listRepository->findAll());

        $client = ControllerTestHelper::logInUser($client);
        $client->request('GET', ControllerTestHelper::generateRoute('task_list_create', 1));
        $this->assertCount(2, $listRepository->findAll());
    }

    public function testView()
    {
        $client = static::createClient();

        $client = ControllerTestHelper::logInUser($client);

        $client->request('GET', ControllerTestHelper::generateRoute('task_list_view', 1));
        $this->assertResponseIsSuccessful();
    }

    public function testViewAccessDenied()
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client = ControllerTestHelper::logInUser($client, 'user2@example.com');
        $this->expectExceptionMessage('Access Denied.');
        $client->request('GET', ControllerTestHelper::generateRoute('task_list_view', 1));
    }

    public function testDeleteInvalidCsrf()
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client = ControllerTestHelper::logInUser($client);
        $client->request('GET', ControllerTestHelper::generateRoute('task_list_delete', 1));

        /** @var TaskList $taskList */
        $taskList = static::$container->get(TaskListRepository::class)->find(1);
        $this->assertEquals(1, $taskList->getId());
    }

    public function testDeleteAccessDenied()
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client = ControllerTestHelper::logInUser($client, 'user3@example.com');
        $this->expectExceptionMessage('Access Denied.');
        $client->request(
            'DELETE',
            ControllerTestHelper::generateRoute('task_list_delete', 1),
            [
                '_token' => static::$container->get('security.csrf.token_manager')->getToken('delete1'),
            ]
        );
    }

    public function testDelete()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'DELETE',
            ControllerTestHelper::generateRoute('task_list_delete', 1),
            [
                '_token' => ControllerTestHelper::getToken('delete1'),
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
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('task_list_share', 1),
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
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('task_list_share', 1),
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
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('task_list_share', 1),
            [],
            [],
            [],
            json_encode([
                'share_list_email[_token]' => ControllerTestHelper::getToken(TaskListShare::FORM_NAME),
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
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('task_list_share', 1),
            [],
            [],
            [],
            json_encode([
                'share_list_email[_token]' => ControllerTestHelper::getToken(TaskListShare::FORM_NAME),
                'share_list_email[email]' => 'user1@example.com'
            ])
        );
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_ERROR);
        $this->assertEquals($responseArray['data'], 'This user is this List author');
    }

    public function testTaskListInviteUser()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'POST',
            ControllerTestHelper::generateRoute('task_list_share', 1),
            [],
            [],
            [],
            json_encode([
                'share_list_email[_token]' => ControllerTestHelper::getToken(TaskListShare::FORM_NAME),
                'share_list_email[email]' => 'non-existing-user@example.com'
            ])
        );
        $responseArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($responseArray['status'], AppConstant::JSON_STATUS_ERROR);
        $this->assertEquals($responseArray['data'], 'User not found. The registration invitation was send on this email');
    }

    public function testEditList()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $crawler = $client->request('GET', ControllerTestHelper::generateRoute('task_list_view', 1));
        $form = $crawler->filter('form[name="task_list"]')->form();
        $form->setValues([
            'task_list[name]' => 'New Name',
            'task_list[description]' => 'New Description',
        ]);

        $client->submit($form);
        $client->followRedirect();

        $this->assertContains(
            'List updated',
            $client->getResponse()->getContent()
        );
    }

    public function testArchiveList()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        /** @var TaskList $taskList */
        $taskList = static::$container->get(TaskListRepository::class)->find(1);
        $this->assertFalse($taskList->isArchived());

        $crawler = $client->request('GET', ControllerTestHelper::generateRoute('task_list_view', 1));
        $form = $crawler->filter('form[name="list_archive"]')->form();

        $client->submit($form);
        $client->followRedirect();

        $this->assertContains(
            'List archived',
            $client->getResponse()->getContent()
        );

        /** @var TaskList $taskList */
        $taskList = static::$container->get(TaskListRepository::class)->find(1);
        $this->assertTrue($taskList->isArchived());
    }
}