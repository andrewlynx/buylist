<?php

namespace App\Service\Notification;

use App\Entity\TaskList;
use App\Entity\User;

interface NotificationInterface
{
    /**
     * @param User $user
     *
     * @return NotificationInterface
     */
    public function for(User $user): self;

    /**
     * @param array<User> $users
     *
     * @return NotificationInterface
     */
    public function forUsers(array $users): self;

    /**
     * @param TaskList $taskList
     *
     * @return NotificationInterface
     */
    public function aboutTaskList(TaskList $taskList): self;

    /**
     * @param string $text
     *
     * @return NotificationInterface
     */
    public function addText(string $text): self;

    /**
     * @return string|null
     */
    public function getText(): ?string;

    /**
     * @param User $user
     *
     * @return NotificationInterface
     */
    public function setUserInvolved(User $user): self;

    /**
     *
     */
    public function createOrUpdate();

    /**
     * @return int
     */
    public function getType(): int;
}
