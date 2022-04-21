<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\Controller\Traits\FormsTrait;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\UseCase\Notification\NotificationHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale}/notification", name="notification_", locale="en", requirements={"_locale": "[a-z]{2}"})
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
        $notifications = $repository->getUsersNotifications($user);
        $this->notificationHandler->readAll($user);

        return $this->render(
            'v1/notification/index.html.twig',
            [
                'notifications' => $notifications,
            ]
        );
    }

    /**
     * @Route("/clear", name="clear")
     *
     * @return Response
     */
    public function clearAll()
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->notificationHandler->clearAll($user);

        return $this->render(
            'v1/notification/index.html.twig',
            [
                'notifications' => [],
            ]
        );
    }
}
