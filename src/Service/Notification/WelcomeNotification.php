<?php

namespace App\Service\Notification;

class WelcomeNotification extends AbstractNotification
{
    protected $requiredFields = [];

    /**
     * @return int
     */
    public function getType(): int
    {
        return NotificationService::EVENT_WELCOME;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
       return null;
    }
}
