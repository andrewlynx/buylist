<?php

namespace App\Constant;

class TaskListTypes
{
    public const DEFAULT = 0;
    public const COUNTER = 1;
    public const TODO = 2;

    public const QUERY = 'list_type';

    private const VIEW_PATH = [
        self::DEFAULT => 'v1/task-list/view.html.twig',
        self::COUNTER => 'v1/task-list/view-counter.html.twig',
    ];

    /**
     * @param int|null $type
     *
     * @return string
     */
    public static function getViewPath(?int $type): string
    {
        return self::typeExists($type) ? self::VIEW_PATH[$type] : self::VIEW_PATH[self::DEFAULT];
    }

    /**
     * @param int|null $type
     *
     * @return bool
     */
    public static function typeExists(?int $type): bool
    {
        return isset(self::VIEW_PATH[$type]);
    }
}
