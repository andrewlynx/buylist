<?php

namespace App\Entity;

use App\Repository\TaskListPublicRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskListPublicRepository::class)
 */
class TaskListPublic
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=32)
     */
    private $id;

    /**
     * @var TaskList
     *
     * @ORM\OneToOne(targetEntity=TaskList::class, inversedBy="taskListPublic",
     *     cascade={"persist", "remove"}, fetch="EAGER"
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $taskList;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $public;

    /**
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return TaskList|null
     */
    public function getTaskList(): ?TaskList
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
     * @return bool|null
     */
    public function isPublic(): ?bool
    {
        return $this->public;
    }

    /**
     * @param bool $public
     *
     * @return $this
     */
    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }
}
