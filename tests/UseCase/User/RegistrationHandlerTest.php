<?php

namespace App\Tests\UseCase\TaskItem;

use App\DTO\TaskList\TaskListShare;
use App\DTO\User\Registration;
use App\DTO\User\Settings;
use App\Entity\EmailInvitation;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use App\UseCase\User\RegistrationHandler;
use DateTime;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

    public function testUpdateSettingsIncorrectPassword()
    {
        $user = $this->getUser(1);
        $dto = new Settings();
        $dto->locale = 'en';
        $dto->newPassword = 'new_password';
        $dto->oldPassword = 'wrong_password';

        /** @var RegistrationHandler $registrationHandler */
        $registrationHandler = static::$container->get(RegistrationHandler::class);
        $this->expectExceptionMessage('user.incorrect_current_password');
        $registrationHandler->updateSettings($user, $dto);
    }

    public function testUpdateSettings()
    {
        $user = $this->getUser(1);
        $oldPass = $user->getPassword();
        $dto = new Settings();
        $dto->locale = 'en';
        $dto->newPassword = 'new_password';
        $dto->oldPassword = 'test';

        /** @var RegistrationHandler $registrationHandler */
        $registrationHandler = static::$container->get(RegistrationHandler::class);
        $updatedUser = $registrationHandler->updateSettings($user, $dto);

        $this->assertNotEquals($oldPass, $updatedUser->getPassword());
    }

    private function getUser(int $id): User
    {
        $userRepository = static::$container->get(UserRepository::class);

        return $userRepository->find($id);
    }
}
