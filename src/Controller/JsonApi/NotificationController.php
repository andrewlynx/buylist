<?php

namespace App\Controller\JsonApi;

use App\Controller\Extendable\TranslatableController;
use App\Controller\Traits\FormsTrait;
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
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * @Route("/{_locale}/json-api/notification",
 *     name="notification_", locale="en",
 *     requirements={"_locale": "[a-z]{2}"}, methods={"POST"}
 * )
 */
class NotificationController extends TranslatableController
{
    use FormsTrait;

    /**
     * @var NotificationHandler
     */
    private $notificationHandler;

    /**
     * @param TranslatorInterface $translator
     * @param NotificationHandler $notificationHandler
     */
    public function __construct(TranslatorInterface $translator, NotificationHandler $notificationHandler)
    {
        parent::__construct($translator);
        $this->notificationHandler = $notificationHandler;
    }


    /**
     * @Route("/read/{id}", name="read")
     *
     * @param Notification        $notification
     * @param Request             $request
     *
     * @return Response
     */
    public function read(Notification $notification, Request $request): Response
    {
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
            $this->checkCsrf('read_notification'.$notification->getId(), $dataArray['_token']);

            $this->notificationHandler->read($notification);

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
     * @param AdminNotification|null   $notification
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

    /**
     * @Route("/check-updates", name="check_updates")
     *
     * @param NotificationRepository $repository
     *
     * @return Response
     */
    public function checkUpdates(NotificationRepository $repository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $haveUpdates = false;

        try {
            $haveUpdates = $repository->checkUpdates($user);
        } catch (Exception $e) {
            //@todo handle exception
        }
        $response = $haveUpdates ? new JsonSuccess(true) : new JsonError(false);

        return $response;
    }
}
