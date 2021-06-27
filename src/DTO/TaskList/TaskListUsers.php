<?php

namespace App\DTO\TaskList;

use App\Entity\User;

class TaskListUsers
{
    /**
     * @var User[]
     */
    public $registered = [];

    /**
     * @var string[]
     */
    public $notAllowed = [];

    /**
     * @var string[]
     */
    public $invitationSent = [];

    /**
     * @param User $user
     *
     * @return $this
     */
    public function addRegistered(User $user): self
    {
        $this->registered[] = $user;

        return $this;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function addNotAllowed(string $email): self
    {
        $this->notAllowed[] = $email;

        return $this;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function addInvitationSent(string $email): self
    {
        $this->invitationSent[] = $email;

        return $this;
    }
}
