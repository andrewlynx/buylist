<?php

namespace App\UseCase\TaskItem;

use App\DTO\TaskItem\TaskItemComplete;
use App\DTO\TaskItem\TaskItemCreate;
use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;

class TaskItemHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param TaskItemCreate $dto
     * @param User $user
     *
     * @return TaskItem
     *
     * @throws Exception
     */
    public function create(TaskItemCreate $dto, User $user): TaskItem
    {
        $taskListRepo = $this->em->getRepository(TaskList::class);
        /** @var TaskList $taskList */
        $taskList = $taskListRepo->find($dto->listId);

        if (!$taskList || $taskList->getCreator() !== $user) {
            throw new Exception('Error assigning new Item to List');
        }

        $taskItem = (new TaskItem())
            ->setName($dto->name)
            ->setQty($dto->qty)
            ->setTaskList($taskList->setUpdatedAt(new DateTime()));

        $this->em->persist($taskItem);
        $this->em->flush();

        return $taskItem;
    }

    /**
     * @param TaskItemComplete $dto
     * @return TaskItem
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function complete(TaskItemComplete $dto): TaskItem
    {
        $taskItemRepo = $this->em->getRepository(TaskItem::class);
        /** @var TaskItem $taskItem */
        $taskItem = $taskItemRepo->find($dto->id);
        $taskItem->setCompleted(!$dto->completed);

        $this->em->flush();

        return $taskItem;
    }
}