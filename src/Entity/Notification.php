<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use App\Service\Notification\NotificationService;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=TaskList::class)
     */
    private $taskList;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $userInvolved;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $seen = false;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getEvent(): ?int
    {
        return $this->event;
    }

    /**
     * @param int $event
     *
     * @return $this
     */
    public function setEvent(int $event): self
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     *
     * @return $this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return TaskList|null
     */
    public function getTaskList(): ?TaskList
    {
        return $this->taskList;
    }

    /**
     * @param TaskList|null $taskList
     *
     * @return $this
     */
    public function setTaskList(?TaskList $taskList): self
    {
        $this->taskList = $taskList;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string|null $text
     *
     * @return $this
     */
    public function setText($text): ?string
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUserInvolved(): ?User
    {
        return $this->userInvolved;
    }

    /**
     * @param User|null $userInvolved
     *
     * @return $this
     */
    public function setUserInvolved(?User $userInvolved): self
    {
        $this->userInvolved = $userInvolved;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @param DateTimeInterface $date
     *
     * @return $this
     */
    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isSeen(): ?bool
    {
        return $this->seen;
    }

    /**
     * @param bool $seen
     *
     * @return $this
     */
    public function setSeen(bool $seen): self
    {
        $this->seen = $seen;

        return $this;
    }

    /**
     * Returns translation string to display on a page
     *
     * @return string
     */
    public function getDescription(): string
    {
        return NotificationService::getDescription($this);
    }

    /**
     * Returns url to wrap the notification on a page o null of it's not required
     *
     * @return array|null
     */
    public function getUrlParams(): ?array
    {
        return NotificationService::getUrlParams($this);
    }
}
