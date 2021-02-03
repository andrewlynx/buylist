<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\Entity\JsonResponse\JsonError;
use App\Entity\JsonResponse\JsonSuccess;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\UseCase\Notification\NotificationHandler;
use Exception;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidatorException;
use Throwable;

/**
 * @Route("/{_locale}/notification", name="notification_", requirements={"_locale": "[a-z]{2}"})
 *
 * @IsGranted("IS_AUTHENTICATED_FULLY")
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
        $taskLists = $repository->getUsersNotifications($this->getUser());

        return $this->render(
            'notification/index.html.twig',
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
    public function read(Notification $notification, Request $request, NotificationHandler $notificationHandler): Response
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

            if (!$this->isCsrfTokenValid('delete'.$notification->getId(), $dataArray['_token'])) {
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
}