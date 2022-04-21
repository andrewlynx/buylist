<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class NotificationInvited extends Notification
{
    /**
     * @var int
     */
    protected $event = NotificationService::EVENT_INVITED;

    /**
     * @var string
     */
    protected $description = 'notification.invitation';
}
