<?php

namespace App\UseCase\TaskList;

use App\Constant\TaskListTypes;
use App\DTO\TaskList\TaskListUsers;
use App\DTO\TaskList\TaskListUsersRaw;
use App\Entity\Object\Email;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Service\Notification\NotificationFactory;
use App\UseCase\InvitationHandler\InvitationHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class TaskListHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var InvitationHandler
     */
    private $invitationHandler;

    /**
     * @var SharedListProcessor
     */
    private $sharedListProcessor;

    /**
     * @var NotificationFactory
     */
    private $notificationFactory;

    /**
     * @param EntityManagerInterface $em
     * @param InvitationHandler      $invitationHandler
     * @param SharedListProcessor    $sharedListProcessor
     * @param NotificationFactory    $notificationFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        InvitationHandler $invitationHandler,
        SharedListProcessor $sharedListProcessor,
        NotificationFactory $notificationFactory
    ) {
        $this->em = $em;
        $this->invitationHandler = $invitationHandler;
        $this->sharedListProcessor = $sharedListProcessor;
        $this->notificationFactory = $notificationFactory;
    }

    /**
     * @param User $user
     *
     * @return TaskList
     *
     * @throws Exception
     */
    public function create(User $user): TaskList
    {
        $taskList = (new TaskList())
            ->setCreator($user)
            ->setType(TaskListTypes::DEFAULT)
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());

        return $taskList;
    }

    /**
     * @param User $user
     *
     * @return TaskList
     *
     * @throws Exception
     */
    public function createCounter(User $user): TaskList
    {
        $taskList = (new TaskList())
            ->setCreator($user)
            ->setType(TaskListTypes::COUNTER)
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());

        return $taskList;
    }

    /**
     * @param TaskList $taskList
     * @param array    $users
     *
     * @return TaskList
     */
    public function updateSharedUsers(TaskList $taskList, array $users): TaskList
    {
        foreach ($taskList->getShared() as $user) {
            if (!in_array($user, $users)) {
                $taskList->removeShared($user);
            }
        }
        foreach ($users as $user) {
            $taskList->addShared($user);
        }

        return $taskList;
    }

    /**
     * @param TaskListUsersRaw $taskListUsers
     * @param TaskList $taskList
     *
     * @return TaskListUsers
     *
     * @throws Exception
     */
    public function processSharedList(TaskListUsersRaw $taskListUsers, TaskList $taskList): TaskListUsers
    {
        $this->sharedListProcessor->setTaskList($taskList);

        /** @var Email $email */
        foreach ($taskListUsers->users as $email) {

            $this->sharedListProcessor->setEmail($email);
            $this->sharedListProcessor->process();
        }

        return $this->sharedListProcessor->getDto();
    }

    /**
     * @param TaskList $taskList
     *
     * @return TaskList
     *
     * @throws Exception
     */
    public function edit(TaskList $taskList): TaskList
    {
        $taskList
            ->setUpdatedAt(new DateTime());

        $this->em->persist($taskList);
        $this->em->flush();

        return $taskList;
    }

    /**
     * @param TaskList $taskList
     * @param bool $status
     *
     * @return TaskList
     *
     * @throws Exception
     */
    public function archive(TaskList $taskList, bool $status): TaskList
    {
        $taskList->setArchived($status);
        $this->em->flush();

        $this->notificationFactory->makeListArchived()
            ->forUsers($taskList->getShared()->toArray())
            ->aboutTaskList($taskList)
            ->setUserInvolved($taskList->getCreator())
            ->createOrUpdate();

        return $taskList;
    }

    /**
     * @param TaskList $taskList
     * @param User $user
     *
     * @return TaskList
     *
     * @throws Exception
     */
    public function unsubscribe(TaskList $taskList, User $user): TaskList
    {
        $taskList->removeShared($user);
        $this->em->flush();

        $this->notificationFactory->makeUserUnsubscribed()
            ->for($taskList->getCreator())
            ->aboutTaskList($taskList)
            ->setUserInvolved($user);

        return $taskList;
    }

    /**
     * @param TaskList $taskList
     *
     * @throws Exception
     */
    public function delete(TaskList $taskList): void
    {
        $shared = $taskList->getShared()->toArray();
        $creator = $taskList->getCreator();
        $name = $taskList->getName();

        foreach ($taskList->getNotifications() as $notification) {
            $this->em->remove($notification);
        }
        $this->em->remove($taskList);
        $this->em->flush();

        $this->notificationFactory->makeListRemoved()
            ->forUsers($shared)
            ->setUserInvolved($creator)
            ->addText($name)
            ->createOrUpdate();
    }

    /**
     * @param User $user
     */
    public function clearArchive(User $user): void
    {
        /** @var TaskListRepository $taskListRepo */
        $taskListRepo = $this->em->getRepository(TaskList::class);
        $taskLists = $taskListRepo->getArchivedUsersTasks($user);

        foreach ($taskLists as $taskList) {
            $this->em->remove($taskList);
        }
        $this->em->flush();
    }

    /**
     * @param TaskList $taskList
     *
     * @return bool
     */
    public function hideCompleted(TaskList $taskList): bool
    {
        $hideCompletedState = !$taskList->isHideCompleted();

        $taskList->setHideCompleted($hideCompletedState);
        $this->em->flush();

        return $hideCompletedState;
    }

    /**
     * @param TaskList $taskList
     * @param User     $user
     *
     * @return TaskList
     */
    public function toggleUsersFavourites(TaskList $taskList, User $user): TaskList
    {
        if ($taskList->isInFavourites($user)) {
            $user->removeFromFavourites($taskList);
        } else {
            $user->addToFavourites($taskList);
        }
        $this->em->flush();

        return $taskList;
    }
}
