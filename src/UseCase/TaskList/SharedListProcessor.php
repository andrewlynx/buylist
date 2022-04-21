<?php

namespace App\UseCase\TaskList;

use App\DTO\TaskList\TaskListUsers;
use App\Entity\Object\Email;
use App\Entity\TaskList;
use App\Entity\User;
use App\Service\Notification\NotificationFactory;
use App\UseCase\InvitationHandler\InvitationHandler;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class SharedListProcessor
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var InvitationHandler
     */
    private $invitationHandler;

    /**
     * @var TaskList
     */
    private $taskList;

    /**
     * @var TaskListUsers
     */
    private $taskListUsersDto;

    /**
     * @var User|null
     */
    private $user;

    /**
     * @var Email
     */
    private $email;

    /**
     * @var NotificationFactory
     */
    private $notificationFactory;

    /**
     * @param InvitationHandler      $invitationHandler
     * @param EntityManagerInterface $em
     * @param NotificationFactory    $notificationFactory
     */
    public function __construct(
        InvitationHandler $invitationHandler,
        EntityManagerInterface $em,
        NotificationFactory $notificationFactory
    ) {
        $this->invitationHandler = $invitationHandler;
        $this->em = $em;
        $this->notificationFactory = $notificationFactory;
        $this->taskListUsersDto = new TaskListUsers();
    }

    /**
     *
     */
    public function process(): void
    {
        if ($this->userCanBeShared()) {
            $this->taskList->addShared($this->user);
            $this->notificationFactory->makeInvited()
                ->for($this->user)
                ->aboutTaskList($this->taskList)
                ->setUserInvolved($this->taskList->getCreator())
                ->createOrUpdate();

            $this->taskListUsersDto->addRegistered($this->user);
        } elseif ($this->userIsCreator()) {
            $this->taskListUsersDto->addNotAllowed($this->user->getEmail());
        } elseif ($this->userIsBanned()) {
            $this->taskListUsersDto->addNotAllowed($this->user->getEmail());
        } else {
            try {
                $this->invitationHandler->createInvitation(
                    $this->email,
                    $this->taskList
                );
                $this->taskListUsersDto->addInvitationSent($this->email->getValue());
            } catch (InvalidArgumentException $e) {
                $this->taskListUsersDto->addInvitationExists($this->email->getValue());
            } catch (\Throwable $e) {
                //@todo log exception
            }
        }
    }

    public function getDto(): TaskListUsers
    {
        $dto = $this->taskListUsersDto;
        $this->clear();

        return $dto;
    }

    /**
     * @param TaskList $taskList
     *
     * @return SharedListProcessor
     */
    public function setTaskList(TaskList $taskList): SharedListProcessor
    {
        $this->taskList = $taskList;

        return $this;
    }

    /**
     * @param Email $email
     *
     * @return SharedListProcessor
     */
    public function setEmail(Email $email): SharedListProcessor
    {
        $this->email = $email;
        $this->user = $this->em->getRepository(User::class)->findOneBy(['email' => $email->getValue()]);

        return $this;
    }

    /**
     * @return bool
     */
    private function userCanBeShared(): bool
    {
        return $this->user &&
            !$this->userIsCreator() &&
            !$this->userIsBanned()
            ;
    }

    /**
     * @return bool
     */
    private function userIsCreator(): bool
    {
        return $this->user === $this->taskList->getCreator();
    }

    /**
     * @return bool
     */
    private function userIsBanned(): bool
    {
        return $this->user && $this->user->isBanned($this->taskList->getCreator());
    }

    /**
     *
     */
    private function clear(): void
    {
        $this->user = null;
        $this->email = null;
        $this->taskListUsersDto = new TaskListUsers();
        $this->taskList = null;
    }
}