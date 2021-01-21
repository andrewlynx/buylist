<?php

namespace App\UseCase\TaskList;

use App\DTO\TaskList\TaskListShare;
use App\Entity\EmailInvitation;
use App\Entity\TaskList;
use App\Entity\User;
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
     * @param EntityManagerInterface $em
     * @param InvitationEmailHandler $emailHandler
     */
    public function __construct(EntityManagerInterface $em, InvitationEmailHandler $emailHandler)
    {
        $this->em = $em;
        $this->emailHandler = $emailHandler;
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
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $dto->email]);
        if ($user && $user !== $taskList->getCreator()) {
            $taskList->addShared($user);
            $this->em->flush();
            // @todo send notification email

            return $user;
        } elseif ($user === $taskList->getCreator()) {
            throw new Exception('This user is this List author');
        } else {
            $invitation = (new EmailInvitation())
                ->setEmail($dto->email)
                ->setCreatedDate(new DateTime())
                ->setTaskList($taskList);
            $this->em->persist($invitation);
            $this->em->flush();

            $this->emailHandler->sendInvitationEmail($taskList->getCreator(), $dto);

            throw new Exception('User not found. The registration invitation was send on this email');
        }
    }
}