<?php

namespace App\Service\Calendar;

use App\Entity\User;
use App\Service\Calendar\CalendarInterval\CalendarIntervalFactory;
use App\Service\Calendar\CalendarInterval\CalendarIntervalInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;

class Calendar
{
    /**
     * @var CalendarIntervalFactory
     */
    private $factory;

    /**
     * @var CalendarIntervalInterface
     */
    private $interval;

    public function __construct(CalendarIntervalFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param DateTime $day
     * @param User     $user
     *
     * @return Calendar
     */
    public function createWeek(DateTime $day, User $user): self
    {
        $this->interval = $this->factory->create();
        $this->interval
            ->setCurrentDay($day)
            ->setUser($user);

        return $this;
    }

    /**
     * @param DateTime $day
     * @param User     $user
     *
     * @return Calendar
     */
    public function createMonth(DateTime $day, User $user): self
    {
        $this->interval = $this->factory->create(CalendarIntervalFactory::MONTH);
        $this->interval
            ->setCurrentDay($day)
            ->setUser($user);

        return $this;
    }

    /**
     * @return ArrayCollection
     *
     * @throws Exception
     */
    public function getDays(): ArrayCollection
    {
        if ($this->interval === null) {
            throw new Exception('Create interval first');
        }

        return $this->interval->getDays();
    }
}