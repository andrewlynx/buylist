<?php

namespace App\Constant;

class TaskListColorLabel
{
    public const RED = 1;
    public const YELLOW = 2;
    public const GREEN = 3;

    /**
     * @return array
     */
    public static function getFreeLabels(): array
    {
        return [
            self::RED,
            self::YELLOW,
            self::GREEN,
        ];
    }

    /**
     * @return array
     */
    public static function getFreeLabelsSelect(): array
    {
        return array_merge([0], self::getFreeLabels());
    }
}
