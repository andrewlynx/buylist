<?php

namespace App\Tests;

use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;

trait TestTrait
{
    public function getContainer()
    {
        if (null === self::$container) {
            $kernel = static::bootKernel();
        }

        return self::$container;
    }

    public function getUser(int $id): ?User
    {
        $userRepository = self::getContainer()->get(UserRepository::class);

        return $userRepository->find($id);
    }

    public function findUser(string $email): ?User
    {
        $userRepository = self::getContainer()->get(UserRepository::class);

        return $userRepository->findUser($email);
    }

    public function getTaskList(int $id): ?TaskList
    {
        $taskListRepository = self::getContainer()->get(TaskListRepository::class);

        return $taskListRepository->find($id);
    }
}
