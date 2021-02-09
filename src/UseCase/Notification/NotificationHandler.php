<?php

namespace App\UseCase\Notification;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

class NotificationHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Notification $notification
     */
    public function read(Notification $notification): void
    {
        $notification->setSeen(true);
        $this->em->flush();
    }
}
