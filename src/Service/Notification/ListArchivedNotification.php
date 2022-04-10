<?php

namespace App\Service\Notification;

class ListArchivedNotification extends AbstractNotification
{
    protected $requiredFields = [
        'taskList',
        'userInvolved'
    ];

    /**
     * @return int
     */
    public function getType(): int
    {
        return NotificationService::EVENT_LIST_ARCHIVED;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return null;
    }
}
