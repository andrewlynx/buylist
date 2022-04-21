<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class NotificationUnsubscribed extends Notification
{
    /**
     * @var int
     */
    protected $event = NotificationService::EVENT_UNSUBSCRIBED;

    /**
     * @var string
     */
    protected $description = 'notification.unsubscribed';
}
