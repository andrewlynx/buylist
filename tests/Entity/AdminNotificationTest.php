<?php

namespace App\Tests\Entity;

use App\Entity\AdminNotification;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminNotificationTest extends WebTestCase
{
    public function testEntity()
    {
        $user = new User();
        $adminNotification = new AdminNotification();
        $adminNotification
            ->setUser($user)
            ->setSeen(false)
            ->setText('text');

        $this->assertSame($user, $adminNotification->getUser());
        $this->assertFalse($adminNotification->isSeen());
        $this->assertSame('text', $adminNotification->getText());
    }
}
