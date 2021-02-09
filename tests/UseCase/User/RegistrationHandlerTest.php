<?php

namespace App\Tests\UseCase\TaskItem;

use App\DTO\TaskList\TaskListShare;
use App\DTO\User\Registration;
use App\Entity\EmailInvitation;
use App\Entity\TaskList;
use App\Repository\TaskListRepository;
use App\UseCase\User\RegistrationHandler;
use DateTime;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationHandlerTest extends WebTestCase
{
    protected function setUp(): void
    {
        if (null === static::$kernel) {
            self::bootKernel();
        }
    }

    public function testRegisterInvalidEmail()
    {
        $dto = new Registration();
        $dto->email = 'invalid email';
        $dto->plainPassword = 'some_password';

        /** @var RegistrationHandler $registrationHandler */
        $registrationHandler = static::$container->get(RegistrationHandler::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('validation.incorrect_email');
        $registrationHandler->register($dto);
    }

    public function testRegister()
    {
        /** @var TaskList $taskList */
        $taskList = static::$container->get(TaskListRepository::class)->find(1);

        $dto = new Registration();
        $dto->email = 'some_valid@email.com';
        $dto->plainPassword = 'some_password';
        $dto->nickName = 'nick_name';

        $taskListShareData = new TaskListShare();
        $taskListShareData->email = 'some_valid@email.com';

        $invitation = (new EmailInvitation())
            ->setEmail($dto->email)
            ->setCreatedDate(new DateTime())
            ->setTaskList($taskList);
        static::$container->get('doctrine.orm.entity_manager')->persist($invitation);
        static::$container->get('doctrine.orm.entity_manager')->flush();

        /** @var RegistrationHandler $registrationHandler */
        $registrationHandler = static::$container->get(RegistrationHandler::class);
        $user = $registrationHandler->register($dto);

        $this->assertNotEmpty($user->getId());
        $this->assertEquals('some_valid@email.com', $user->getEmail());
        $this->assertContains($user, $taskList->getShared());
    }
}
