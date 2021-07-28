<?php

namespace App\UseCase\InvitationHandler;

use App\Entity\EmailInvitation;
use App\Entity\Object\Email;
use App\Entity\TaskList;
use App\Repository\EmailInvitationRepository;
use App\UseCase\Email\InvitationEmailHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
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
     * @param EntityManagerInterface $em
     * @param InvitationEmailHandler $emailHandler
     */
    public function __construct(
        EntityManagerInterface $em,
        InvitationEmailHandler $emailHandler
    ) {
        $this->em = $em;
        $this->emailHandler = $emailHandler;
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
}
