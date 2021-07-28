<?php

namespace App\Tests\Entity;

use App\Entity\EmailInvitation;
use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskListTest extends WebTestCase
{
    public function testEntity()
    {
        $taskList = new TaskList();
        $taskItem = new TaskItem();
        $user = new User();
        $user2 = (new User())->setEmail('test@email.qwe');
        $date = new \DateTime();
        $date2 = new \DateTime();
        $date3 = new \DateTime();
        $emailInvitation = new EmailInvitation();

        $taskList
            ->setName('name')
            ->addShared($user2)
            ->addTaskItem($taskItem)
            ->setDate($date)
            ->setArchived(false)
            ->setUpdatedAt($date2)
            ->setCreatedAt($date3)
            ->setDescription('description')
            ->setCreator($user)
            ->setColorLabel('red')
            ->addEmailInvitations($emailInvitation);

        $this->assertNull($taskList->getId());
        $this->assertSame($user2, $taskList->getShared()->first());
        $this->assertSame($taskItem, $taskList->getTaskItems()->first());
        $this->assertSame($date, $taskList->getDate());
        $this->assertFalse($taskList->isArchived());
        $this->assertSame($date2, $taskList->getUpdatedAt());
        $this->assertSame($date3, $taskList->getCreatedAt());
        $this->assertSame('description', $taskList->getDescription());
        $this->assertSame($user, $taskList->getCreator());
        $this->assertSame('red', $taskList->getColorLabel());
        $this->assertSame($emailInvitation, $taskList->getEmailInvitations()->first());
        $this->assertEmpty($taskList->getNotifications());
        $this->assertSame(['email' => 'test@email.qwe'], $taskList->getSimpleUsersEmails()[0]);

        $taskList->removeTaskItem($taskItem);
        $this->assertEmpty($taskList->getTaskItems());

        $taskList->removeShared($user2);
        $this->assertEmpty($taskList->getShared());
    }
}
