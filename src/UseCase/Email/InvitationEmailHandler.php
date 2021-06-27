<?php

namespace App\UseCase\Email;

use App\DTO\TaskList\TaskListShare;
use App\Entity\Object\Email;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class InvitationEmailHandler extends AbstractEmailHandler
{
    /**
     * @param User  $from
     * @param Email $mail
     *
     * @throws TransportExceptionInterface
     */
    public function sendInvitationEmail(User $from, Email $mail): void
    {
        $email = (new TemplatedEmail())
            ->to($mail->getValue())
            ->subject($from->getEmail().' has shared a list with you')
            ->htmlTemplate('public/email/invitation.html.twig')
            ->context([
                'from' => $from->getEmail(),
            ])
        ;

        $this->mailer->send($email);
    }
}
