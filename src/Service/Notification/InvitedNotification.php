<?php

namespace App\Service\Notification;

class InvitedNotification extends AbstractNotification
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
        return NotificationService::EVENT_INVITED;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return null;
    }
}
