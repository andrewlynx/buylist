<?php

namespace App\UseCase\TaskItem;

use App\DTO\TaskItem\TaskItemComplete;
use App\DTO\TaskItem\TaskItemCreate;
use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Entity\User;
use App\Service\Notification\NotificationService;
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
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @param EntityManagerInterface $em
     * @param NotificationService    $notificationService
     */
    public function __construct(EntityManagerInterface $em, NotificationService $notificationService)
    {
        $this->em = $em;
        $this->notificationService = $notificationService;
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

        $notificationList = array_merge($taskList->getShared()->toArray(), [$taskList->getCreator()]);

        $this->notificationService->createForManyUsers(
            NotificationService::EVENT_LIST_CHANGED,
            $notificationList,
            $taskList,
            $user
        );

        $this->em->persist($taskItem);
        $this->em->flush();

        return $taskItem;
    }

    /**
     * @param TaskItemComplete $dto
     * @param User $user
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

        $notificationList = array_merge($taskList->getShared()->toArray(), [$taskList->getCreator()]);

        $this->notificationService->createForManyUsers(
            NotificationService::EVENT_LIST_CHANGED,
            $notificationList,
            $taskList,
            $user
        );

        $this->em->flush();

        return $taskItem;
    }
}
