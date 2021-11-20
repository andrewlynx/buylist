<?php

namespace App\Tests\UseCase\TaskList;

use App\Constant\TaskListTypes;
use App\DTO\TaskList\TaskListUsersRaw;
use App\Entity\EmailInvitation;
use App\Entity\Notification;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use App\UseCase\TaskList\TaskListHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskListHandlerTest extends WebTestCase
{
    protected function setUp(): void
    {
        if (null === static::$kernel) {
            self::bootKernel();
        }
    }

    public function testCreate()
    {
        $user = $this->getUser(1);
        $taskListHandler = $this->getTaskListHandler();

        $taskList = $taskListHandler->create($user);

        $this->assertNull($taskList->getId());
        $this->assertEquals($user, $taskList->getCreator());
        $this->assertEquals(TaskListTypes::DEFAULT, $taskList->getType());
        $this->assertNull($taskList->getDescription());
        $this->assertNull($taskList->getName());
    }

    public function testUpdateSharedUsers()
    {
        $taskListRepository = static::$container->get(TaskListRepository::class);
        /** @var TaskList $taskList */
        $taskList = $taskListRepository->find(1);

        $user2 = $this->getUser(2);
        $user3 = $this->getUser(3);
        $user4 = $this->getUser(4);

        $taskList->addShared($user2);

        $taskListHandler = $this->getTaskListHandler();
        $taskList = $taskListHandler->updateSharedUsers($taskList, [$user3, $user4]);
        $this->assertContains($user3, $taskList->getShared());
        $this->assertContains($user4, $taskList->getShared());
        $this->assertNotContains($user2, $taskList->getShared());
    }

    public function testProcessSharedList()
    {
        $taskListHandler = $this->getTaskListHandler();
        $taskListRepository = static::$container->get(TaskListRepository::class);
        /** @var TaskList $taskList */
        $taskList = $taskListRepository->find(1);

        $user1 = $this->getUser(1);
        $user2 = $this->getUser(2);
        $user3 = $this->getUser(3);
        $user4 = $this->getUser(4);

        $taskList->addShared($user3);

        $user2->banUser($user1);

        $testData = [
            [
                "email" => "user1@example.com",
                "active" => "1"
            ],
            [
                "email" => "user2@example.com",
                "active" => "1"
            ],
            [
                "email" => "user3@example.com",
                "active" => "1"
            ],
            [
                "email" => "user4@example.com",
                "active" => "1"
            ],
            [
                "email" => "user88@example.com",
                "active" => "1"
            ],
        ];
        $taskListUsersDTO = new TaskListUsersRaw($testData);

        $usersDTO = $taskListHandler->processSharedList($taskListUsersDTO, $taskList);

        $this->assertContains('user1@example.com', $usersDTO->notAllowed);
        $this->assertContains('user2@example.com', $usersDTO->notAllowed);
        $this->assertContains($user3, $usersDTO->registered);
        $this->assertContains($user4, $usersDTO->registered);
        $this->assertContains('user88@example.com', $usersDTO->invitationSent);

        $testData = [
            [
                "email" => "user88@example.com",
                "active" => "1"
            ],
        ];
        $taskListUsersDTO = new TaskListUsersRaw($testData);

        $usersDTO = $taskListHandler->processSharedList($taskListUsersDTO, $taskList);
        $this->assertContains('user88@example.com', $usersDTO->invitationExists);
    }

    public function testUpdate()
    {
        $taskListHandler = $this->getTaskListHandler();
        $taskListRepository = static::$container->get(TaskListRepository::class);
        /** @var TaskList $taskList */
        $taskList = $taskListRepository->find(1);
        $this->assertNotEquals('New awesome name', $taskList->getName());

        $taskList->setName('New awesome name');
        $editedTaskList = $taskListHandler->edit($taskList);
        $this->assertEquals('New awesome name', $editedTaskList->getName());
    }

    public function testArchive()
    {
        $taskListHandler = $this->getTaskListHandler();
        $taskListRepository = static::$container->get(TaskListRepository::class);
        /** @var TaskList $taskList */
        $taskList = $taskListRepository->find(1);

        $taskList = $taskListHandler->archive($taskList, true);
        $this->assertTrue($taskList->isArchived());
    }

    public function testUnsubscribe()
    {
        $taskListHandler = $this->getTaskListHandler();
        $taskListRepository = static::$container->get(TaskListRepository::class);
        /** @var TaskList $taskList */
        $taskList = $taskListRepository->find(1);
        $user2 = $this->getUser(2);

        $taskList->addShared($user2);
        $this->assertContains($user2, $taskList->getShared());

        $editedTaskList = $taskListHandler->unsubscribe($taskList, $user2);
        $this->assertNotContains($user2, $editedTaskList->getShared());
    }

    public function testDelete()
    {
        $taskListRepository = static::$container->get(TaskListRepository::class);
        $taskList = $taskListRepository->find(1);
        $user = $this->getUser(1);
        $user2 = $this->getUser(2);
        $date = new \DateTime();
        $notification = new Notification();
        $notification
            ->setTaskList($taskList)
            ->setText('text')
            ->setSeen(false)
            ->setUser($user)
            ->setUserInvolved($user2)
            ->setDate($date)
            ->setEvent(3);
        static::$container->get('doctrine.orm.entity_manager')->persist($notification);
        static::$container->get('doctrine.orm.entity_manager')->flush();

        $taskListHandler = $this->getTaskListHandler();

        $taskListHandler->delete($taskList);
        $this->assertNull($taskListRepository->find(1));
    }

    public function testClearArchive()
    {
        $taskListRepository = static::$container->get(TaskListRepository::class);
        /** @var TaskList $taskList */
        $taskList = $taskListRepository->find(1);
        $taskList->setArchived(true);
        static::$container->get('doctrine.orm.entity_manager')->persist($taskList);
        static::$container->get('doctrine.orm.entity_manager')->flush();

        $user = $this->getUser(1);
        $taskListHandler = $this->getTaskListHandler();

        $this->assertNotEmpty($taskListRepository->getArchivedUsersTasks($user));
        $taskListHandler->clearArchive($user);
        $this->assertEmpty($taskListRepository->getArchivedUsersTasks($user));
    }

    private function getUser(int $id): User
    {
        $userRepository = static::$container->get(UserRepository::class);

        return $userRepository->find($id);
    }

    private function getTaskListHandler(): TaskListHandler
    {
        /** @var TaskListHandler */
        return static::$container->get(TaskListHandler::class);
    }
}
