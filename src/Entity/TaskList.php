<?php

namespace App\Entity;

use App\Repository\TaskListRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskListRepository::class)
 */
class TaskList
{
    /**
     * @var int
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
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var DateTimeInterface
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
     * @var User[]
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
     * @var TaskItem[]
     *
     * @ORM\OneToMany(targetEntity=TaskItem::class, mappedBy="taskList", orphanRemoval=true, cascade={"persist"})
     */
    private $taskItems;

    /**
     *
     */
    public function __construct()
    {
        $this->shared = new ArrayCollection();
        $this->taskItems = new ArrayCollection();
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
     * @return TaskList
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
     * @return TaskList
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
     * @return TaskList
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
     * @return TaskList
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
     * @return TaskList
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
     * @return TaskList
     */
    public function removeShared(User $shared): self
    {
        $this->shared->removeElement($shared);

        return $this;
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
     * @return TaskList
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
     * @return TaskList
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
     * @return TaskList
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
     * @return TaskList
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
}
