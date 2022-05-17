<?php

namespace App\Service\Calendar\CalendarInterval;

use App\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

interface CalendarIntervalInterface
{
    /**
     * @param DateTime $day
     *
     * @return CalendarIntervalInterface
     */
    public function setCurrentDay(DateTime $day): self;

    /**
     * @param User $user
     *
     * @return CalendarIntervalInterface
     */
    public function setUser(User $user): self;

    /**
     * @return ArrayCollection
     */
    public function getDays(): ArrayCollection;
}