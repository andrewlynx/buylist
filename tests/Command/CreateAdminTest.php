<?php

namespace App\Tests\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateAdminTest extends KernelTestCase
{
    public function testExecuteFail()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:create-admin');
        $commandTester = new CommandTester($command);
        $this->expectExceptionMessage('Not enough arguments (missing: "user").');
        $commandTester->execute([]);
    }

    public function testExecute()
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);

        $userRepository = static::$container->get(UserRepository::class);
        /** @var User $user */
        $user = $userRepository->find(1);
        $this->assertNotContains(User::ROLE_ADMIN, $user->getRoles());

        $command = $application->find('app:create-admin');
        $commandTester = new CommandTester($command);

        $commandTester->execute(['user' => $user->getEmail()]);

        $this->assertContains(User::ROLE_ADMIN, $user->getRoles());
    }

    public function testUnexistingUser()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:create-admin');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['user' => 'some@wrong.email']);
        $this->assertStringContainsString('Error: User some@wrong.email not found', $commandTester->getDisplay());
    }
}
