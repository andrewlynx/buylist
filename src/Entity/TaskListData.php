<?php

namespace App\Entity;

use App\Repository\TaskListDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskListDataRepository::class)
 */
class TaskListData
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
     * @var TaskList|null
     *
     * @ORM\OneToOne(targetEntity=TaskList::class, inversedBy="taskListData", cascade={"persist", "remove"})
     */
    private $taskList;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $type;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $cloned;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     *
     * @return $this
     */
    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isCloned(): ?bool
    {
        return $this->cloned;
    }

    /**
     * @param bool|null $cloned
     *
     * @return $this
     */
    public function setCloned(?bool $cloned): self
    {
        $this->cloned = $cloned;

        return $this;
    }
}
