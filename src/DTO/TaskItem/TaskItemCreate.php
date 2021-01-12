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
     * @param array $dataArray
     */
    public function __construct(array $dataArray)
    {
        $this->token = $dataArray[self::FIELD_TOKEN];
        $this->name = $dataArray[self::FIELD_NAME];
        $this->qty = $dataArray[self::FIELD_QTY];
        $this->listId = $dataArray[self::FIELD_LIST_ID];
    }
}