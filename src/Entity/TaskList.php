<?php

namespace App\Entity;

use App\Repository\TaskListRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskListRepository::class)
 * @ORM\Table(indexes={@ORM\Index(columns={"name", "description"}, flags={"fulltext"})})
 */
class TaskList
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var DateTimeInterface|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="taskLists")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    /**
     * @var Collection<User>
     *
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="shared")
     */
    private $shared;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var Collection<TaskItem>
     * @ORM\OrderBy({"completed" = "ASC"})
     *
     * @ORM\OneToMany(targetEntity=TaskItem::class, mappedBy="taskList", orphanRemoval=true, cascade={"persist"})
     */
    private $taskItems;

    /**
     * @var Collection<EmailInvitation>
     *
     * @ORM\OneToMany(targetEntity=EmailInvitation::class, mappedBy="taskList")
     */
    private $emailInvitations;

    /**
     * @var Collection<Notification>
     *
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="taskList")
     */
    private $notifications;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $archived = false;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $colorLabel;

    /**
     *
     */
    public function __construct()
    {
        $this->shared = new ArrayCollection();
        $this->taskItems = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->emailInvitations = new ArrayCollection();
    }

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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
     * @param DateTimeInterface|null $date
     *
     * @return $this
     */
    public function setDate(?DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getCreator(): ?User
    {
        return $this->creator;
    }

    /**
     * @param User|null $creator
     *
     * @return $this
     */
    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getShared(): Collection
    {
        return $this->shared;
    }

    /**
     * @param User $shared
     *
     * @return $this
     */
    public function addShared(User $shared): self
    {
        if (!$this->shared->contains($shared)) {
            $this->shared[] = $shared;
        }

        return $this;
    }

    /**
     * @param User $shared
     *
     * @return $this
     */
    public function removeShared(User $shared): self
    {
        $this->shared->removeElement($shared);

        return $this;
    }

    /**
     * @return array
     */
    public function getSimpleUsersEmails(): array
    {
        $emails = [];
        foreach ($this->shared as $sharedUser) {
            if (!$this->creator->getFavouriteUsers()->contains($sharedUser)) {
                $emails[] = ['email' => $sharedUser->getEmail()];
            }
        }

        return $emails;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeInterface $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|TaskItem[]
     */
    public function getTaskItems(): Collection
    {
        return $this->taskItems;
    }

    /**
     * @param TaskItem $taskItem
     *
     * @return $this
     */
    public function addTaskItem(TaskItem $taskItem): self
    {
        if (!$this->taskItems->contains($taskItem)) {
            $this->taskItems[] = $taskItem;
            $taskItem->setTaskList($this);
        }

        return $this;
    }

    /**
     * @param TaskItem $taskItem
     *
     * @return $this
     */
    public function removeTaskItem(TaskItem $taskItem): self
    {
        if ($this->taskItems->removeElement($taskItem)) {
            // set the owning side to null (unless already changed)
            if ($taskItem->getTaskList() === $this) {
                $taskItem->setTaskList(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getEmailInvitations(): Collection
    {
        return $this->emailInvitations;
    }

    /**
     * @param EmailInvitation $emailInvitation
     *
     * @return $this
     */
    public function addEmailInvitations(EmailInvitation $emailInvitation): self
    {
        if (!$this->emailInvitations->contains($emailInvitation)) {
            $this->emailInvitations[] = $emailInvitation;
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->archived;
    }

    /**
     * @param bool $archived
     *
     * @return $this
     */
    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * @return string
     */
    public function getColorLabel(): string
    {
        return $this->colorLabel;
    }

    /**
     * @param string $colorLabel
     *
     * @return $this
     */
    public function setColorLabel(string $colorLabel): TaskList
    {
        $this->colorLabel = $colorLabel;

        return $this;
    }
}
