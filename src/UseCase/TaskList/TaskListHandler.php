<?php

namespace App\UseCase\TaskList;

use App\DTO\TaskList\TaskListUsers;
use App\DTO\TaskList\TaskListUsersRaw;
use App\Entity\Object\Email;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Service\Notification\NotificationService;
use App\UseCase\InvitationHandler\InvitationHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

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
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @param EntityManagerInterface $em
     * @param InvitationHandler      $invitationHandler
     * @param NotificationService    $notificationService
     */
    public function __construct(
        EntityManagerInterface $em,
        InvitationHandler $invitationHandler,
        NotificationService $notificationService
    ) {
        $this->em = $em;
        $this->invitationHandler = $invitationHandler;
        $this->notificationService = $notificationService;
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
            ->setName('New List')
            ->setCreator($user)
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());

        $this->em->persist($taskList);
        $this->em->flush();

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
     * @throws TransportExceptionInterface
     */
    public function processSharedList(TaskListUsersRaw $taskListUsers, TaskList $taskList): TaskListUsers
    {
        $usersDTO = new TaskListUsers();

        /** @var Email $email */
        foreach ($taskListUsers->users as $email) {
            /** @var User|null $user */
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email->getValue()]);

            if ($user && $user !== $taskList->getCreator() && !$user->isBanned($taskList->getCreator())) {
                $taskList->addShared($user);
                $this->notificationService->createOrUpdate(
                    NotificationService::EVENT_INVITED,
                    $user,
                    $taskList,
                    $taskList->getCreator()
                );

                $usersDTO->addRegistered($user);
            } elseif ($user === $taskList->getCreator()) {
                $usersDTO->addNotAllowed($user->getEmail());
            } elseif ($user && $user->isBanned($taskList->getCreator())) {
                $usersDTO->addNotAllowed($user->getEmail());
            } else {
                try {
                    $this->invitationHandler->createInvitation($email, $taskList);
                    $usersDTO->addInvitationSent($email->getValue());
                } catch (InvalidArgumentException $e) {
                    $usersDTO->addInvitationExists($email->getValue());
                }
            }
        }

        return $usersDTO;
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
        $taskList->setArchived(!$status);
        $this->em->flush();

        $this->notificationService->createForManyUsers(
            NotificationService::EVENT_LIST_ARCHIVED,
            $taskList->getShared()->toArray(),
            $taskList,
            $taskList->getCreator()
        );

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

        $this->notificationService->createOrUpdate(
            NotificationService::EVENT_UNSUBSCRIBED,
            $taskList->getCreator(),
            $taskList,
            $user
        );

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

        $this->notificationService->createForManyUsers(
            NotificationService::EVENT_LIST_REMOVED,
            $shared,
            null,
            $creator,
            $name
        );
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
}
