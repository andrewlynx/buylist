<?php

namespace App\DTO\TaskList;

class TaskListShare
{
    public const FORM_NAME = 'share_list_email';
    public const FIELD_EMAIL = self::FORM_NAME.'[email]';
    public const FIELD_TOKEN = self::FORM_NAME.'[_token]';

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $email;

    /**
     * @param array|null $dataArray
     */
    public function __construct(array $dataArray = null)
    {
        $this->token = $dataArray[self::FIELD_TOKEN] ?? null;
        $this->email = $dataArray[self::FIELD_EMAIL] ?? null;
    }
}
