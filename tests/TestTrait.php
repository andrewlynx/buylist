<?php

namespace App\Tests;

use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

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

    public function getSimpleRoute(KernelBrowser $client, string $route): KernelBrowser
    {
        $client->request(
            'GET',
            $client->getContainer()->get('router')->generate($route)
        );

        return $client;
    }

    public function getSimpleRouteWithParams(KernelBrowser $client, string $route, string $params): KernelBrowser
    {
        $client->request(
            'GET',
            $client->getContainer()->get('router')->generate($route).$params
        );

        return $client;
    }
}
