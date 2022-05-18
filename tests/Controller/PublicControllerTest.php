<?php

namespace App\Tests\Controller;

use App\Entity\TaskList;
use App\Repository\TaskListRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PublicControllerTest extends WebTestCase
{
    public function testWelcome()
    {
        $client = static::createClient();

        $client->request('GET', ControllerTestHelper::generateRoute('welcome'));
        $this->assertResponseIsSuccessful();

        $client = ControllerTestHelper::logInUser($client);
        $client->request('GET', ControllerTestHelper::generateRoute('welcome'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/task-list/', $client->getResponse()->headers->get('Location'));
    }

    public function testAbout()
    {
        $client = static::createClient();

        $client->request('GET', ControllerTestHelper::generateRoute('about'));
        $this->assertResponseIsSuccessful();

        $client = ControllerTestHelper::logInUser($client);
        $client->request('GET', ControllerTestHelper::generateRoute('about'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/en/task-list/', $client->getResponse()->headers->get('Location'));
    }

    public function testTaskListPublic()
    {
        $client = static::createClient();
        $client = ControllerTestHelper::logInUser($client);
        $crawler = $client->request('GET', ControllerTestHelper::generateRoute('task_list_view', 1));
        $this->assertResponseIsSuccessful();
        $this->assertContains(
            'Make public',
            $client->getResponse()->getContent()
        );

        $form = $crawler->filter('form[name="task_list_public"]')->form();
        $client->submit($form);
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertNotContains(
            'Make public',
            $client->getResponse()->getContent()
        );

        $repo = static::$container->get(TaskListRepository::class);
        /** @var TaskList $taskList */
        $taskList = $repo->find(1);
        $publicId = $taskList->getTaskListPublic()->getId();
        $client->request('GET',
            static::$container->get('router')->generate('list_public', ['id' => $publicId, '_locale' => 'en'])
        );
        $this->assertResponseIsSuccessful();
        $this->assertContains(
            'My New Task List',
            $client->getResponse()->getContent()
        );
    }

    public function testTaskListPublicNotFound()
    {
        $client = static::createClient();
        $client->request('GET',
            static::$container->get('router')->generate('list_public', ['id' => 'sdfsdfsdf', '_locale' => 'en'])
        );
        $this->assertResponseStatusCodeSame(404);
    }
}
