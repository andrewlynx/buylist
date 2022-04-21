<?php

namespace App\Service\Notification;

use App\Entity\Notification;
use App\Entity\NotificationInvited;
use App\Entity\NotificationListArchived;
use App\Entity\NotificationListChanged;
use App\Entity\NotificationListRemoved;
use App\Entity\NotificationUnsubscribed;
use App\Entity\NotificationWelcome;
use App\Entity\TaskList;
use App\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotificationFactory
{
    /**
     * @var Collection<Notification>
     */
    private $notifications;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @var TaskList
     */
    private $taskList;

    /**
     * @var string
     */
    private $text;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var User
     */
    private $userInvolved;

    /**
     * @var Collection<User>
     */
    private $users;

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
     * @var Notification
     */
    private $notification;

    /**
     * @return $this
     */
    public function makeInvited(): self
    {
        $this->notification = new NotificationInvited();

        return $this;
    }

    /**
     * @return $this
     */
    public function makeListRemoved(): self
    {
        $this->notification = new NotificationListRemoved();

        return $this;
    }

    /**
     * @return $this
     */
    public function makeListArchived(): self
    {
        $this->notification = new NotificationListArchived();

        return $this;
    }

    /**
     * @return $this
     */
    public function makeListChanged(): self
    {
        $this->notification = new NotificationListChanged();

        return $this;
    }

    /**
     * @return $this
     */
    public function makeUserUnsubscribed(): self
    {
        $this->notification = new NotificationUnsubscribed();

        return $this;
    }

    /**
     * @return $this
     */
    public function makeWelcome(): self
    {
        $this->notification = new NotificationWelcome();

        return $this;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function for(User $user): self
    {
        $this->users->add($user);

        return $this;
    }

    /**
     * @param array<User> $users
     *
     * @return $this
     */
    public function forUsers(array $users): self
    {
        foreach ($users as $user) {
            $this->users->add($user);
        }

        return $this;
    }

    /**
     * @param TaskList $taskList
     *
     * @return $this
     */
    public function aboutTaskList(TaskList $taskList): self
    {
        $this->taskList = $taskList;

        return $this;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function addText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUserInvolved(User $user): self
    {
        $this->userInvolved = $user;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function createOrUpdate(): void
    {
        /** @var User $user */
        foreach ($this->users->getIterator() as $user) {
            if ($user === $this->getUser()) {
                continue;
            }
            $notification = $this->notificationService->checkExistence(
                $this->notification->getEvent(),
                $user,
                $this->taskList,
                $this->userInvolved,
                $this->text
            );
            if (!$notification) {
                $notification = $this->notification
                    ->setUser($user)
                    ->setTaskList($this->taskList)
                    ->setUserInvolved($this->userInvolved)
                    ->setText($this->text);
            }

            $notification->setDate(new DateTime());

            $this->notifications->add($notification);
        }

        $this->notificationService->save($this->notifications);
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