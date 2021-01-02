<?php

namespace App\Entity;

use App\Repository\TaskItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity(repositoryClass=TaskItemRepository::class)
 */
class TaskItem
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
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $qty;

    /**
     * @var TaskList
     *
     * @ORM\ManyToOne(targetEntity=TaskList::class, inversedBy="taskItems", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $taskList;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $completed = false;

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
    public function getQty(): ?string
    {
        return $this->qty;
    }

    /**
     * @param string|null $qty
     *
     * @@return $this
     */
    public function setQty(?string $qty): self
    {
        $this->qty = $qty;

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
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     *
     * @return $this
     */
    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }
}
