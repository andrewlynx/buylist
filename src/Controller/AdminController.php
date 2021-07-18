<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\Form\Admin\CreateAdminNotificationType;
use App\Repository\UserRepository;
use App\UseCase\Admin\NotificationHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @Route("{_locale}/master", name="admin_", locale="en", requirements={"_locale": "[a-z]{2}"})
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
     * @Route("/remove-notification", name="remove_read_notifications")
     *
     * @param NotificationHandler $notificationHandler
     *
     * @return Response
     */
    public function removeReadNotifications(NotificationHandler $notificationHandler): Response
    {
        try {
            $notificationHandler->clear();
            $this->addFlash('success', 'Message(s) cleared');
        } catch (Throwable $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('admin_panel');
    }
}
