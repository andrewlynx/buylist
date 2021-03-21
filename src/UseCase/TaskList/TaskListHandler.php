<?php

namespace App\UseCase\TaskList;

use App\DTO\TaskList\TaskListShare;
use App\Entity\EmailInvitation;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Service\Notification\NotificationFactory;
use App\Service\Notification\NotificationService;
use App\UseCase\Email\InvitationEmailHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class TaskListHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var InvitationEmailHandler
     */
    private $emailHandler;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @param EntityManagerInterface $em
     * @param InvitationEmailHandler $emailHandler
     * @param NotificationService    $notificationService
     */
    public function __construct(
        EntityManagerInterface $em,
        InvitationEmailHandler $emailHandler,
        NotificationService $notificationService
    ) {
        $this->em = $em;
        $this->emailHandler = $emailHandler;
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
     * @param TaskList      $taskList
     * @param TaskListShare $dto
     *
     * @return User
     *
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function share(TaskList $taskList, TaskListShare $dto): User
    {
        /** @var User|null $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $dto->email]);
        if ($user && $user !== $taskList->getCreator()) {
            $taskList->addShared($user);
            $this->notificationService->createOrUpdate(
                NotificationService::EVENT_INVITED,
                $user,
                $taskList,
                $taskList->getCreator()
            );

            return $user;
        } elseif ($user === $taskList->getCreator()) {
            throw new Exception('share_list.user_is_list_author');
        } else {
            $invitation = (new EmailInvitation())
                ->setEmail($dto->email)
                ->setCreatedDate(new DateTime())
                ->setTaskList($taskList);
            $this->em->persist($invitation);
            $this->em->flush();

            $this->emailHandler->sendInvitationEmail($taskList->getCreator(), $dto);

            throw new Exception('share_list.user_not_found');
        }
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
        $this->em->remove($taskList);
        $this->em->flush();

        $this->notificationService->createForManyUsers(
            NotificationService::EVENT_UNSUBSCRIBED,
            $taskList->getShared()->toArray(),
            $taskList,
            $taskList->getCreator()
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
