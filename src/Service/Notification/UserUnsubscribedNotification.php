<?php

namespace App\Service\Notification;

class UserUnsubscribedNotification extends AbstractNotification
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
        return NotificationService::EVENT_LIST_CHANGED;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return null;
    }
}
