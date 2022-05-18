<?php

namespace App\Service\Calendar\CalendarInterval;

class CalendarIntervalFactory
{
    public const WEEK = 0;
    public const MONTH = 1;

    /**
     * @var Week
     */
    private $week;

    /**
     * @var Month
     */
    private $month;

    /**
     * @param Week  $week
     * @param Month $month
     */
    public function __construct(Week $week, Month $month)
    {
        $this->week = $week;
        $this->month = $month;
    }

    /**
     * @param int $interval
     *
     * @return CalendarIntervalInterface
     */
    public function create(int $interval = self::WEEK): CalendarIntervalInterface
    {
        switch ($interval) {
            case self::MONTH:
                return $this->month;

            default:
                return $this->week;
        }
    }
}
