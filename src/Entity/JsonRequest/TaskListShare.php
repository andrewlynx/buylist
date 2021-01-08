<?php

namespace App\Entity\JsonRequest;

class TaskListShare
{
    public const FORM_NAME = 'share_list_email';
    public const FIELD_EMAIL = self::FORM_NAME.'[email]';
    public const FIELD_TOKEN = self::FORM_NAME.'[_token]';

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $email;

    /**
     * @param array $dataArray
     */
    public function __construct(array $dataArray)
    {
        $this->token = $dataArray[self::FIELD_TOKEN];
        $this->email = $dataArray[self::FIELD_EMAIL];
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}