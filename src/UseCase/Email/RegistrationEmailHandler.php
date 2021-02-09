<?php

namespace App\UseCase\Email;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Throwable;

class RegistrationEmailHandler extends AbstractEmailHandler
{
    /**
     * @var EmailVerifier
     */
    private $emailVerifier;

    /**
     * @param EmailVerifier    $emailVerifier
     * @param SessionInterface $session
     * @param MailerInterface  $mailer
     */
    public function __construct(
        EmailVerifier $emailVerifier,
        SessionInterface $session,
        MailerInterface $mailer
    ) {
        parent::__construct($this->session = $session, $this->mailer = $mailer);
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @param User $user
     */
    public function sendConfirmationEmail(User $user)
    {
        try {
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('public/registration/confirmation_email.html.twig')
            );
        } catch (Throwable $e) {
            $this->session->getFlashBag()->add('error', $e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param User $user
     *
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, User $user)
    {
        $this->emailVerifier->handleEmailConfirmation($request, $user);
    }

    /**
     * @param User $user
     * @param ResetPasswordToken $token
     *
     * @throws TransportExceptionInterface
     */
    public function sendPasswordResetEmail(User $user, ResetPasswordToken $token)
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('public/reset_password/email.html.twig')
            ->context([
                'resetToken' => $token,
            ])
        ;

        $this->mailer->send($email);
    }
}
