<?php

namespace App\Service\Notification;

use App\Entity\Notification;
use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractNotification implements NotificationInterface
{
    /**
     * @var Collection<Notification>
     */
    protected $notifications;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var array
     */
    protected $requiredFields;

    /**
     * @var TaskList
     */
    protected $taskList;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var User
     */
    protected $userInvolved;

    /**
     * @var Collection<User>
     */
    protected $users;

    /**
     * @param NotificationService   $notificationService
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(NotificationService $notificationService, TokenStorageInterface $tokenStorage)
    {
        $this->notificationService = $notificationService;
        $this->notifications = new ArrayCollection();
        $this->tokenStorage = $tokenStorage;
        $this->users = new ArrayCollection();
    }

    /**
     * @param User $user
     *
     * @return NotificationInterface
     */
    public function for(User $user): NotificationInterface
    {
        $this->users->add($user);

        return $this;
    }

    /**
     * @param array<User> $users
     *
     * @return NotificationInterface
     */
    public function forUsers(array $users): NotificationInterface
    {
        foreach ($users as $user) {
            $this->users->add($user);
        }

        return $this;
    }

    /**
     * @param TaskList $taskList
     *
     * @return NotificationInterface
     */
    public function aboutTaskList(TaskList $taskList): NotificationInterface
    {
        $this->taskList = $taskList;

        return $this;
    }

    /**
     * @param string $text
     *
     * @return NotificationInterface
     */
    public function addText(string $text): NotificationInterface
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param User $user
     *
     * @return NotificationInterface
     */
    public function setUserInvolved(User $user): NotificationInterface
    {
        $this->userInvolved = $user;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function createOrUpdate()
    {
        $this->checkRequiredFields();

        /** @var User $user */
        foreach ($this->users->getIterator() as $user) {
            if ($user === $this->getUser()) {
                continue;
            }
            $notification = $this->notificationService->getOrCreate(
                $this->getType(),
                $user,
                $this->taskList,
                $this->userInvolved,
                $this->text
            );

            $this->notifications->add($notification);
        }

        $this->notificationService->save($this->notifications);
    }

    /**
     *
     */
    private function checkRequiredFields()
    {
        foreach ($this->requiredFields as $required) {
            if ($this->$required === null) {
                throw new InvalidArgumentException(sprintf('%s should not be empty for %s', $required, self::class));
            }
        }
    }

    /**
     * @return User|null
     */
    private function getUser(): ?User
    {
        if ($this->tokenStorage->getToken()) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            return $user;
        }

        return null;
    }
}
