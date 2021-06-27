<?php

namespace App\DTO\TaskList;

use App\Entity\Object\Email;

class TaskListUsersRaw
{
    public const ACTIVE = 'active';
    public const EMAIL = 'email';

    /**
     * @var Email[]
     */
    public $users = [];

    /**
     * @param array $formData
     */
    public function __construct(array $formData)
    {
        foreach ($formData as $userData) {
            if (!empty($userData[self::EMAIL]) && $userData[self::ACTIVE]) {
                $this->users[] = new Email($userData[self::EMAIL]);
            }
        }
    }
}
