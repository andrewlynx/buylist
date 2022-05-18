<?php

namespace App\Service\Calendar\CalendarInterval;

use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Service\Calendar\Day;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class Week implements CalendarIntervalInterface
{
    /**
     * @var DateTime
     */
    private $currentDay;

    /**
     * @var DateTime
     */
    private $endDay;

    /**
     * @var ArrayCollection
     */
    private $days;

    /**
     * @var ArrayCollection
     */
    private $lists;

    /**
     * @var TaskListRepository
     */
    private $repo;

    /**
     * @var User
     */
    private $user;

    /**
     * @param TaskListRepository $repo
     */
    public function __construct(TaskListRepository $repo)
    {
        $this->repo = $repo;
        $this->days = new ArrayCollection();
    }

    /**
     * @param DateTime $day
     *
     * @return CalendarIntervalInterface
     */
    public function setCurrentDay(DateTime $day): CalendarIntervalInterface
    {
        $this->currentDay = $day;
        $endDay = clone $day;
        $this->endDay = $endDay->modify('+6 days');

        return $this;
    }

    /**
     * @param User $user
     *
     * @return CalendarIntervalInterface
     */
    public function setUser(User $user): CalendarIntervalInterface
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return ArrayCollection
     *
     * @throws \Exception
     */
    public function getDays(): ArrayCollection
    {
        $this->lists = $this->getLists();

        $current = $this->currentDay;
        while ($this->endDay->getTimestamp() >= $current->getTimestamp()) {
            $date = (new DateTime)->setTimestamp($current->getTimestamp());

            $lists = $this->lists->filter(function ($taskList) use ($date) {
                return $taskList->getDate()->format('Y-m-d') === $date->format('Y-m-d');
            });

            $this->days->add(new Day($date, $lists));
            $current->modify('+1 day');
        }

        return $this->days;
    }

    /**
     * @return ArrayCollection
     */
    private function getLists(): ArrayCollection
    {
        return $this->repo->getByDates($this->user, $this->currentDay, $this->endDay);
    }
}
