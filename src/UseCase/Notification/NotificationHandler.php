<?php

namespace App\UseCase\Notification;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var NotificationRepository
     */
    private $repository;

    /**
     * @param EntityManagerInterface $em
     * @param NotificationRepository $repository
     */
    public function __construct(EntityManagerInterface $em, NotificationRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * @param Notification $notification
     */
    public function read(Notification $notification): void
    {
        $notification->setSeen(true);
        $this->em->flush();
    }

    /**
     * @param User $user
     */
    public function readAll(User $user): void
    {
        $this->repository->readAll($user);
    }

    /**
     * @param User $user
     */
    public function clearAll(User $user): void
    {
        $this->repository->clearAll($user);
    }
}
