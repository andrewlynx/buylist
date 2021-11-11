<?php

namespace App\UseCase\InvitationHandler;

use App\Entity\EmailInvitation;
use App\Entity\Object\Email;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\EmailInvitationRepository;
use App\Service\Notification\NotificationService;
use App\UseCase\Email\InvitationEmailHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class InvitationHandler
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
     * @param Email    $email
     * @param TaskList $taskList
     *
     * @throws TransportExceptionInterface
     */
    public function createInvitation(Email $email, TaskList $taskList): void
    {
        /** @var EmailInvitationRepository $repo */
        $repo = $this->em->getRepository(EmailInvitation::class);
        if (empty($repo->getPendingInvitations($email->getValue(), $taskList->getId()))) {
            $invitation = (new EmailInvitation())
                ->setEmail($email->getValue())
                ->setCreatedDate(new DateTime())
                ->setTaskList($taskList);
            $this->em->persist($invitation);
            $this->em->flush();

            $this->emailHandler->sendInvitationEmail($taskList->getCreator(), $email);
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @param User $user
     *
     * @throws Exception
     */
    public function sendPendingInvitations(User $user): void
    {
        // Check for pending invitations to lists
        /** @var EmailInvitationRepository $emailInvitationRepo */
        $emailInvitationRepo = $this->em->getRepository(EmailInvitation::class);
        $pending = $emailInvitationRepo->getPendingInvitations($user->getEmail());
        /** @var EmailInvitation $invitation */
        foreach ($pending as $invitation) {
            $taskList = $invitation->getTaskList()->addShared($user);
            $this->em->persist($taskList);
            $this->em->remove($invitation);

            $this->notificationService->createOrUpdate(
                NotificationService::EVENT_INVITED,
                $user,
                $taskList,
                $taskList->getCreator()
            );
        }
    }
}
