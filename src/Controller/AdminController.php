<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\Entity\AdminNotification;
use App\Entity\JsonResponse\JsonError;
use App\Entity\JsonResponse\JsonSuccess;
use App\Entity\User;
use App\Form\Admin\CreateAdminNotificationType;
use App\Repository\UserRepository;
use App\UseCase\Admin\NotificationHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @Route("{_locale}/master", name="admin_", requirements={"_locale": "[a-z]{2}"})
 */
class AdminController extends TranslatableController
{
    /**
     * @Route("/panel", name="panel")
     *
     * @param Request        $request
     * @param UserRepository $userRepository
     *
     * @return Response
     */
    public function panel(Request $request, UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render(
            'admin/panel.html.twig',
            [
                'users' => $users,
            ]
        );
    }

    /**
     * @Route("/create-notification/{id}", name="create_notification")
     *
     * @param int                 $id
     * @param Request             $request
     * @param UserRepository      $userRepository
     * @param NotificationHandler $notificationHandler
     *
     * @return Response
     */
    public function createAdminNotification(
        int $id,
        Request $request,
        UserRepository $userRepository,
        NotificationHandler $notificationHandler
    ): Response {
        $form = $this->createForm(CreateAdminNotificationType::class)->handleRequest($request);
        $users = $id > 0 ? [$userRepository->find($id)] : $userRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $notificationHandler->create($users, $form->get('text')->getData());
                $this->addFlash('success', 'Message(s) created');

                return $this->redirectToRoute('admin_panel');
            } catch (Throwable $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render(
            'admin/create-admin-notification.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/read-notification/{id}", name="read_notification")
     *
     * @param AdminNotification|null $notification
     * @param NotificationHandler    $notificationHandler
     *
     * @return Response
     */
    public function readAdminNotification(
        ?AdminNotification $notification = null,
        NotificationHandler $notificationHandler
    ): Response {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $notificationHandler->markSeen($notification, $user);

            return new JsonSuccess('');
        } catch (Throwable $e) {
            return new JsonError(
                $this->translator->trans($e->getMessage())
            );
        }
    }
}
