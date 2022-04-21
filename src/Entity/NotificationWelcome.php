<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class NotificationWelcome extends Notification
{
    /**
     * @var int
     */
    protected $event = NotificationService::EVENT_WELCOME;

    /**
     * @var string
     */
    protected $description = 'notification.welcome';
}
