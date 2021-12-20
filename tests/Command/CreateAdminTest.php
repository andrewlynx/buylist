<?php

namespace App\Tests\Command;

use App\Entity\User;
use App\Tests\TestTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateAdminTest extends KernelTestCase
{
    use TestTrait;

    /**
     * @var CommandTester|null
     */
    protected $commandTester;

    /**
     * @var ObjectManager|null
     */
    protected $entityManager;

    public function testExecuteFail()
    {
        $this->expectExceptionMessage('Not enough arguments (missing: "user").');
        $this->commandTester->execute([]);
    }

    public function testExecute()
    {
        $user = $this->getUser(1);
        $this->assertNotContains(User::ROLE_ADMIN, $user->getRoles());

        $this->commandTester->execute(['user' => $user->getEmail()]);

        $this->assertContains(User::ROLE_ADMIN, $user->getRoles());
    }

    public function testUnexistingUser()
    {
        $this->commandTester->execute(['user' => 'some@wrong.email']);

        $this->assertStringContainsString(
            'Error: User some@wrong.email not found',
            $this->commandTester->getDisplay()
        );
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $command = $application->find('app:create-admin');
        $this->commandTester = new CommandTester($command);

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager = null;
    }
}
