<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\Entity\AdminNotification;
use App\Entity\JsonResponse\JsonError;
use App\Entity\JsonResponse\JsonSuccess;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\UseCase\Admin\NotificationHandler as AdminNotificationHandler;
use App\UseCase\Notification\NotificationHandler;
use Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidatorException;
use Throwable;

/**
 * @Route("/{_locale}/notification", name="notification_", locale="en", requirements={"_locale": "[a-z]{2}"})
 */
class NotificationController extends TranslatableController
{
    /**
     * @Route("/", name="index")
     *
     * @param NotificationRepository $repository
     *
     * @return Response
     */
    public function index(NotificationRepository $repository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $taskLists = $repository->getUsersNotifications($user);

        return $this->render(
            'v1/notification/index.html.twig',
            [
                'notifications' => $taskLists,
            ]
        );
    }

    /**
     * @Route("/read/{id}", name="read")
     *
     * @param Notification        $notification
     * @param Request             $request
     * @param NotificationHandler $notificationHandler
     *
     * @return Response
     *
     * @throws Exception
     */
    public function read(
        Notification $notification,
        Request $request,
        NotificationHandler $notificationHandler
    ): Response {
        try {
            if ($notification->getUser() !== $this->getUser()) {
                throw new ValidatorException('validation.invalid_submission');
            }

            $dataArray = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(
                    $this->translator->trans('validation.invalid_json_request', ['error' => json_last_error_msg()])
                );
            }

            if (!$this->isCsrfTokenValid('read_notification'.$notification->getId(), $dataArray['_token'])) {
                throw new ValidatorException('validation.invalid_csrf');
            }

            $notificationHandler->read($notification);

            return new JsonSuccess(
                'read'
            );
        } catch (Throwable $e) {
            return new JsonError(
                $this->translator->trans($e->getMessage())
            );
        }
    }

    /**
     * @Route("/read-notification/{id}", name="read_admin")
     *
     * @param AdminNotification|null $notification
     * @param AdminNotificationHandler $notificationHandler
     *
     * @return Response
     */
    public function readAdminNotification(
        ?AdminNotification $notification = null,
        AdminNotificationHandler $notificationHandler
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
