<?php

namespace App\UseCase\TaskItem;

use App\DTO\TaskItem\TaskItemComplete;
use App\DTO\TaskItem\TaskItemCreate;
use App\DTO\TaskItem\TaskItemEdit;
use App\DTO\TaskItem\TaskItemIncrement;
use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Entity\User;
use App\Service\Notification\NotificationFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class TaskItemHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var NotificationFactory
     */
    private $notificationFactory;

    /**
     * @param EntityManagerInterface  $em
     * @param NotificationFactory     $notificationFactory
     */
    public function __construct(EntityManagerInterface $em, NotificationFactory $notificationFactory)
    {
        $this->em = $em;
        $this->notificationFactory = $notificationFactory;
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
        /** @var TaskList|null $taskList */
        $taskList = $taskListRepo->find($dto->listId);

        if ($taskList->isArchived()) {
            throw new Exception('list.activate_list_to_edit');
        }

        if (!$taskList || !($taskList->getCreator() === $user || $taskList->getShared()->contains($user))) {
            throw new Exception('task_item.error_assign_to_list');
        }

        $taskItem = (new TaskItem())
            ->setName($dto->name ?? TaskItem::DEFAULT_NAME)
            ->setQty($dto->qty)
            ->setTaskList($taskList->setUpdatedAt(new DateTime()));

        $this->notificationFactory->makeListChanged()
            ->forUsers($taskList->getAllUsers())
            ->aboutTaskList($taskList)
            ->setUserInvolved($user)
            ->createOrUpdate();

        $this->em->persist($taskItem);
        $this->em->flush();

        return $taskItem;
    }

    /**
     * @param TaskItemComplete $dto
     * @param User             $user
     *
     * @return TaskItem
     *
     * @throws Exception
     */
    public function complete(TaskItemComplete $dto, User $user): TaskItem
    {
        $taskItemRepo = $this->em->getRepository(TaskItem::class);
        /** @var TaskItem $taskItem */
        $taskItem = $taskItemRepo->find($dto->id);
        $taskItem->setCompleted(!$dto->completed);
        $taskList = $taskItem->getTaskList();
        $this->setTaskListUpdatedTime($taskList);

        $this->notificationFactory->makeListChanged()
            ->forUsers($taskList->getAllUsers())
            ->aboutTaskList($taskList)
            ->setUserInvolved($user)
            ->createOrUpdate();

        $this->em->flush();

        return $taskItem;
    }

    /**
     * @param TaskItemIncrement $dto
     * @param User $user
     *
     * @return TaskItem
     *
     * @throws Exception
     */
    public function increment(TaskItemIncrement $dto, User $user): TaskItem
    {
        $taskItemRepo = $this->em->getRepository(TaskItem::class);
        /** @var TaskItem $taskItem */
        $taskItem = $taskItemRepo->find($dto->id);
        $taskItem->incrementQty();
        $this->setTaskListUpdatedTime($taskItem->getTaskList());

        $this->em->flush();

        return $taskItem;
    }

    /**
     * @param TaskItemEdit $dto
     * @param User         $user
     *
     * @return TaskItem
     *
     * @throws Exception
     */
    public function edit(TaskItemEdit $dto, User $user): TaskItem
    {
        $taskItem = $dto->taskItem;
        $taskItem->setName($dto->name);
        $taskItem->setQty($dto->qty);
        $taskList = $taskItem->getTaskList();
        $this->setTaskListUpdatedTime($taskList);

        $this->notificationFactory->makeListChanged()
            ->forUsers($taskList->getAllUsers())
            ->aboutTaskList($taskList)
            ->setUserInvolved($user)
            ->createOrUpdate();

        $this->em->flush();

        return $taskItem;
    }

    /**
     * @param TaskList $taskList
     *
     * @return TaskList
     *
     * @throws Exception
     */
    private function setTaskListUpdatedTime(TaskList $taskList): TaskList
    {
        $taskList->setUpdatedAt(new DateTime());

        return $taskList;
    }
}
