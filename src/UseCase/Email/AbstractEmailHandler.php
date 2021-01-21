<?php

namespace App\UseCase\Email;

use App\Security\EmailVerifier;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;

abstract class AbstractEmailHandler
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @param SessionInterface $session
     * @param MailerInterface  $mailer
     */
    public function __construct(
        SessionInterface $session,
        MailerInterface $mailer
    )
    {
        $this->session = $session;
        $this->mailer = $mailer;
    }
}