<?php

namespace App\Controller;

use App\DTO\User\Registration;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use App\UseCase\Email\RegistrationEmailHandler;
use App\UseCase\User\RegistrationHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/{_locale}/register", name="app_register")
     *
     * @param Request                   $request
     * @param GuardAuthenticatorHandler $guardHandler
     * @param AppAuthenticator          $authenticator
     * @param RegistrationHandler       $registrationHandler
     * @param RegistrationEmailHandler  $emailHandler
     *
     * @return Response
     */
    public function register(
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        AppAuthenticator $authenticator,
        RegistrationHandler $registrationHandler,
        RegistrationEmailHandler $emailHandler
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('index');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registrationData = new Registration();
            $registrationData->email = $form->get('email')->getData();
            $registrationData->plainPassword = $form->get('plainPassword')->getData();
            $user = $registrationHandler->register($user, $registrationData);

            $emailHandler->sendConfirmationEmail($user);

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('public/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     *
     * @param Request                  $request
     * @param RegistrationEmailHandler $emailHandler
     *
     * @return Response
     */
    public function verifyUserEmail(Request $request, RegistrationEmailHandler $emailHandler): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $emailHandler->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
