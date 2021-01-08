<?php

namespace App\Entity;

use App\Repository\EmailInvitationRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity=TaskList::class, inversedBy="emailInvitations", cascade={"remove", "persist"})
     */
    private $TaskList;

    /**
     * @ORM\Column(type="datetime")
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
     * @return EmailInvitation
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
        return $this->TaskList;
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
     * @return EmailInvitation
     */
    public function setCreatedDate(DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }
}
