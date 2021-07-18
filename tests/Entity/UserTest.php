<?php

namespace App\Tests\Entity;

use App\Entity\TaskList;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    public function testEntity()
    {
        $lastLogin = new \DateTime();
        $user = new User();
        $user2 = new User();
        $user3 = new User();
        $taskList = new TaskList();
        $taskList2 = new TaskList();
        $user
            ->setEmail('test@test.test')
            ->setHelpers(true)
            ->setRoles([User::ROLE_USER])
            ->setNickName('nickName')
            ->setLastLogin($lastLogin)
            ->setLocale('fr')
            ->setIsVerified(false)
            ->setPassword('plainPass')
            ->addShared($taskList2)
            ->addFavouriteUser($user2)
            ->banUser($user3)
            ->addRole(User::ROLE_ADMIN)
            ->addTaskList($taskList);

        $this->assertNull($user->getId());
        $this->assertSame(true, $user->getHelpers());
        $this->assertSame([User::ROLE_USER, User::ROLE_ADMIN], $user->getRoles());
        $this->assertSame('nickName', $user->getNickName());
        $this->assertSame($lastLogin, $user->getLastLogin());
        $this->assertSame('fr', $user->getLocale());
        $this->assertSame(false, $user->isVerified());
        $this->assertSame('plainPass', $user->getPassword());
        $this->assertSame($taskList2, $user->getShared()->first());
        $this->assertSame($user2, $user->getFavouriteUsers()->first());
        $this->assertSame($user3, $user->getBannedUsers()->first());
        $this->assertSame($taskList, $user->getTaskLists()->first());
        $this->assertEmpty($user->getNotifications());

        $user->removeShared($taskList2);
        $this->assertEmpty($user->getShared());

        $user->removeFromFavouriteUsers($user2);
        $this->assertEmpty($user->getFavouriteUsers());

        $user->removeFromBan($user3);
        $this->assertEmpty($user->getBannedUsers());

        $user->removeTaskList($taskList);
        $this->assertEmpty($user->getTaskLists());
    }
}