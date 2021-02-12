<?php

namespace App\Entity;

use App\Repository\EmailInvitationRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmailInvitationRepository::class)
 */
class EmailInvitation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity=TaskList::class, inversedBy="emailInvitations", cascade={"persist"})
     *
     * @var TaskList|null
     */
    private $taskList;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var DateTimeInterface
     */
    private $createdDate;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return TaskList
     */
    public function getTaskList(): TaskList
    {
        return $this->taskList;
    }

    /**
     * @param TaskList $taskList
     *
     * @return $this
     */
    public function setTaskList(TaskList $taskList): self
    {
        $this->taskList = $taskList;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedDate(): ?DateTimeInterface
    {
        return $this->createdDate;
    }

    /**
     * @param DateTimeInterface $createdDate
     *
     * @return $this
     */
    public function setCreatedDate(DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }
}
