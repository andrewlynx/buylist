<?php

namespace App\Entity\JsonRequest;

class TaskItemComplete
{
    public const FORM_NAME = 'task_item_complete';
    public const FIELD_ID = self::FORM_NAME.'[id]';
    public const FIELD_COMPLETED = self::FORM_NAME.'[completed]';
    public const FIELD_TOKEN = self::FORM_NAME.'[_token]';

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $completed;

    /**
     * @var int
     */
    private $id;

    /**
     * @param array $dataArray
     */
    public function __construct(array $dataArray)
    {
        $this->token = $dataArray[self::FIELD_TOKEN];
        $this->completed = (bool) ($dataArray[self::FIELD_COMPLETED] ?? 0);
        $this->id = $dataArray[self::FIELD_ID];
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}