<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class NotificationListRemoved extends Notification
{
    /**
     * @var int
     */
    protected $event = NotificationService::EVENT_LIST_REMOVED;

    /**
     * @var string
     */
    protected $description = 'notification.list_removed';
}
