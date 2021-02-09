<?php

namespace App\DTO\TaskItem;

class TaskItemCreate
{
    public const FORM_NAME = 'task_item_create';
    public const FIELD_NAME = self::FORM_NAME.'[name]';
    public const FIELD_QTY = self::FORM_NAME.'[qty]';
    public const FIELD_LIST_ID = self::FORM_NAME.'[list_id]';
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
     * @var int
     */
    public $listId;

    /**
     * @param array|null $dataArray
     */
    public function __construct(array $dataArray = null)
    {
        $this->token = $dataArray[self::FIELD_TOKEN] ?? null;
        $this->name = $dataArray[self::FIELD_NAME] ?? null;
        $this->qty = $dataArray[self::FIELD_QTY] ?? null;
        $this->listId = $dataArray[self::FIELD_LIST_ID] ?? null;
    }
}
