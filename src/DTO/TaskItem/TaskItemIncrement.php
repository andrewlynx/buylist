<?php

namespace App\DTO\TaskItem;

class TaskItemIncrement
{
    public const FORM_NAME = 'task_item_increment';
    public const FIELD_ID = self::FORM_NAME.'[id]';
    public const FIELD_TOKEN = self::FORM_NAME.'[_token]';

    /**
     * @var string
     */
    public $token;

    /**
     * @var int
     */
    public $id;

    /**
     * @param array|null $dataArray
     */
    public function __construct(array $dataArray = null)
    {
        $this->token = $dataArray[self::FIELD_TOKEN] ?? null;
        $this->id = $dataArray[self::FIELD_ID] ?? null;
    }
}
