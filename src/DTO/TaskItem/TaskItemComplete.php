<?php

namespace App\DTO\TaskItem;

class TaskItemComplete
{
    public const FORM_NAME = 'task_item_complete';
    public const FIELD_ID = self::FORM_NAME.'[id]';
    public const FIELD_COMPLETED = self::FORM_NAME.'[completed]';
    public const FIELD_TOKEN = self::FORM_NAME.'[_token]';

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $completed;

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
        $this->completed = (bool) ($dataArray[self::FIELD_COMPLETED] ?? 0);
        $this->id = $dataArray[self::FIELD_ID] ?? null;
    }
}