<?php

namespace App\Service\Calendar;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class Day
{
    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var ArrayCollection
     */
    private $lists;

    /**
     * @param DateTime        $date
     * @param ArrayCollection $lists
     */
    public function __construct(DateTime $date, ArrayCollection $lists)
    {
        $this->date = $date;
        $this->lists = $lists;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @return ArrayCollection
     */
    public function getLists(): ArrayCollection
    {
        return $this->lists;
    }
}
