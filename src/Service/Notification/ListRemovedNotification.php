<?php

namespace App\Service\Notification;

class ListRemovedNotification extends AbstractNotification
{
    protected $requiredFields = [
        'text',
        'userInvolved'
    ];

    /**
     * @return int
     */
    public function getType(): int
    {
        return NotificationService::EVENT_LIST_REMOVED;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return null;
    }
}
