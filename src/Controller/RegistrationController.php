<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\DTO\User\Registration;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use App\UseCase\Email\RegistrationEmailHandler;
use App\UseCase\User\RegistrationHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Throwable;

class RegistrationController extends TranslatableController
{
    /**
     * @Route("/{_locale}/register", name="app_register", locale="en", requirements={"_locale": "[a-z]{2}"})
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
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('index');
        }
        $form = $this->createForm(RegistrationFormType::class, new User());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $registrationData = new Registration();
                $registrationData->email = $form->get('email')->getData();
                $registrationData->plainPassword = $form->get('plainPassword')->getData();
                $registrationData->nickName = $form->get('nickName')->getData();
                $registrationData->locale = $request->getLocale();

                $user = $registrationHandler->register($registrationData);

                // Confirmation email is commented until this functionality is required
                //$emailHandler->sendConfirmationEmail($user);

                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $authenticator,
                    'main' // firewall name in security.yaml
                );
            } catch (Throwable $e) {
                $this->addFlash('danger', $this->translator->trans($e->getMessage()));
            }
        }

        return $this->render('v1/public/registration/register.html.twig', [
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
            /** @var User $user */
            $user = $this->getUser();
            $emailHandler->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('danger', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
