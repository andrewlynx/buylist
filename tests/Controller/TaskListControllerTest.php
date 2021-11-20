<?php

namespace App\Tests\Controller;

use App\Constant\AppConstant;
use App\DTO\TaskList\TaskListShare;
use App\Entity\TaskList;
use App\Form\TaskListType;
use App\Repository\NotificationRepository;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use App\Service\Notification\NotificationService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskListControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            $client->getContainer()->get('router')->generate('task_list_index')
        );
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/login', $client->getResponse()->headers->get('Location'));

        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            $client->getContainer()->get('router')->generate('task_list_index')
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testShared()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            $client->getContainer()->get('router')->generate('task_list_index_shared')
        );
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/login', $client->getResponse()->headers->get('Location'));

        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            $client->getContainer()->get('router')->generate('task_list_index_shared')
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testArchive()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            $client->getContainer()->get('router')->generate('task_list_archive')
        );
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/login', $client->getResponse()->headers->get('Location'));

        $client = ControllerTestHelper::logInUser($client);
        $client->request(
            'GET',
            $client->getContainer()->get('router')->generate('task_list_archive')
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
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

        /** @var TaskListRepository $listRepository */
        $listRepository = static::$container->get(TaskListRepository::class);
        $count = $listRepository->count([]);

        $client->request(
            'DELETE',
            ControllerTestHelper::generateRoute('task_list_delete', 1),
            [
                '_token' => ControllerTestHelper::getToken('delete1'),
            ]
        );
        $this->assertResponseStatusCodeSame(302);

        $this->assertCount($count - 1, $listRepository->findAll());
    }

    public function testLoadMore()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'GET',
            static::$container->get('router')
                ->generate('task_list_load_more', [
                    'page' => 0
                ])
        );
        $this->assertResponseIsSuccessful();
        $this->assertContains(
            '<div class="tl">',
            $client->getResponse()->getContent()
        );

        $client->request(
            'GET',
            static::$container->get('router')
                ->generate('task_list_load_more', [
                    'page' => 1
                ])
        );
        $this->assertResponseIsSuccessful();
        $this->assertNotContains(
            '<div class="tl">',
            $client->getResponse()->getContent()
        );
    }

    public function testLoadMoreShared()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'GET',
            static::$container->get('router')
                ->generate('task_list_load_more_shared', [
                    'page' => 1
                ])
        );
        $this->assertResponseIsSuccessful();
    }

    public function testLoadMoreArchive()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $client->request(
            'GET',
            static::$container->get('router')
                ->generate('task_list_load_more_archive', [
                    'page' => 1
                ])
        );
        $this->assertResponseIsSuccessful();
    }

    public function testCreatePost()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $crawler = $client->request(
            'POST',
            static::$container->get('router')->generate('task_list_create'),
            $this->getInvalidPostData('task_list')
        );

        $this->assertResponseIsSuccessful();
        $this->assertContains(
            'Incorrect Email',
            $client->getResponse()->getContent()
        );

        $crawler = $client->request(
            'POST',
            static::$container->get('router')->generate('task_list_create'),
            [
                'task_list' => [
                    '_token' => ControllerTestHelper::getToken('task_list'),
                    'name' => 'cool name',
                    'description' => 'nice description',
                    'users' => [
                        0 => [
                            'email' => 'test4@test.test',
                            'active' => 1,
                        ],
                        1 => [
                            'email' => 'user1@example.com',
                            'active' => 1,
                        ],
                        2 => [
                            'email' => 'test66@test.test',
                            'active' => 1,
                        ],
                    ],
                ],
            ]
        );

        $client->followRedirect();
        $this->assertContains(
            'List created',
            $client->getResponse()->getContent()
        );
    }

    public function testCreateCounterPost()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $crawler = $client->request(
            'POST',
            static::$container->get('router')->generate('task_list_create_counter'),
            $this->getInvalidPostData('task_list_counter')
        );

        $this->assertResponseIsSuccessful();
        $this->assertContains(
            'Incorrect Email',
            $client->getResponse()->getContent()
        );
    }

    public function testEdit()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        /** @var TaskList $taskList */
        $taskList = static::$container->get(TaskListRepository::class)->find(1);

        $this->assertNotEquals('new cool name', $taskList->getName());
        $this->assertNotEquals('awesome description', $taskList->getDescription());

        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('task_list_edit', 1)
        );
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="task_list"]')->form();
        $form->setValues([
            'task_list[name]' => 'new cool name',
            'task_list[description]' => 'awesome description',
        ]);
        $client->submit($form);
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertContains(
            'List updated',
            $client->getResponse()->getContent()
        );

        /** @var TaskList $taskList */
        $taskList = static::$container->get(TaskListRepository::class)->find(1);
        $this->assertEquals('new cool name', $taskList->getName());
        $this->assertEquals('awesome description', $taskList->getDescription());
    }

    public function testEditCounter()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        /** @var TaskList $taskList */
        $taskList = static::$container->get(TaskListRepository::class)->find(2);

        $this->assertEquals('New Counter List', $taskList->getName());

        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('task_list_edit', 2)
        );
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="task_list_counter"]')->form();
        $form->setValues([
            'task_list_counter[name]' => 'new cool name',
            'task_list_counter[description]' => 'awesome description',
        ]);
        $client->submit($form);
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertContains(
            'List updated',
            $client->getResponse()->getContent()
        );
    }

    public function testHideCompleted()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);

        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('task_list_view', 1)
        );
        $this->assertResponseIsSuccessful();
        $this->assertNotContains(
            'hidden-completed',
            $client->getResponse()->getContent()
        );

        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('task_list_hide_completed', 1)
        );
        $this->assertResponseIsSuccessful();

        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('task_list_view', 1)
        );
        $this->assertContains(
            'hidden-completed',
            $client->getResponse()->getContent()
        );
    }

    public function testHideCompletedNotAuthor()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client, 'user2@example.com');

        $crawler = $client->request(
            'GET',
            ControllerTestHelper::generateRoute('task_list_hide_completed', 1)
        );
        $this->assertResponseIsSuccessful();
        $this->assertContains(
            'Access Denied.',
            $client->getResponse()->getContent()
        );
    }

    private function getInvalidPostData(string $formName): array
    {
        return [
            $formName => [
                '_token' => ControllerTestHelper::getToken($formName),
                'name' => 'cool name',
                'description' => 'nice description',
                'users' => [
                    1 => [
                        'email' => 'test4_test.test',
                        'active' => 1,
                    ],
                ],
            ],
        ];
    }
}
