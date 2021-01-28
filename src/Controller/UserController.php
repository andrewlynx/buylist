<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\DTO\User\Settings;
use App\Entity\User;
use App\Form\UserSettingsType;
use App\UseCase\User\RegistrationHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @Route("{_locale}/user", name="user_", requirements={"_locale": "[a-z]{2}"})
 */
class UserController extends TranslatableController
{
    /**
     * @Route("/settings", name="settings")
     *
     * @param Request             $request
     * @param RegistrationHandler $registrationHandler
     *
     * @return Response
     */
    public function settings(Request $request, RegistrationHandler $registrationHandler): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(UserSettingsType::class, $user)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $settingData = new Settings();
                $settingData->locale = $form->get('locale')->getData();
                $settingData->oldPassword = $form->get('current_password')->getData();
                $settingData->newPassword = $form->get('new_password')->getData();

                $user = $registrationHandler->updateSettings($user, $settingData);

                return $this->redirectToRoute('user_settings', ['_locale' => $user->getLocale()]);
            } catch (Throwable $e) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans($e->getMessage())
                );
            }
        }

        return $this->render(
            'user/settings.html.twig',
            [
                'task_lists' => $user,
                'form' => $form->createView(),
            ]
        );
    }
}
