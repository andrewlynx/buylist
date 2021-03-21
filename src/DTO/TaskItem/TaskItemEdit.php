<?php

namespace App\DTO\TaskItem;

use App\Entity\TaskItem;

class TaskItemEdit
{
    public const FORM_NAME = 'task_item_edit';
    public const FIELD_NAME = self::FORM_NAME.'[name]';
    public const FIELD_QTY = self::FORM_NAME.'[qty]';
    public const FIELD_TOKEN = self::FORM_NAME.'[_token]';

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $qty;

    /**
     * @var TaskItem
     */
    public $taskItem;

    /**
     * @param array|null $dataArray
     */
    public function __construct(array $dataArray = null)
    {
        $this->token = $dataArray[self::FIELD_TOKEN] ?? null;
        $this->name = $dataArray[self::FIELD_NAME] ?? null;
        $this->qty = $dataArray[self::FIELD_QTY] ?? null;
    }
}
