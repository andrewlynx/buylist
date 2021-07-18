<?php

namespace App\Tests\Entity;

use App\Entity\EmailInvitation;
use App\Entity\TaskList;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmailInvitationTest extends WebTestCase
{
    public function testEntity()
    {
        $taskList = new TaskList();
        $date = new \DateTime();
        $emailInvitation = new EmailInvitation();
        $emailInvitation
            ->setEmail('email')
            ->setTaskList($taskList)
            ->setCreatedDate($date);

        $this->assertNull($emailInvitation->getId());
        $this->assertSame($taskList, $emailInvitation->getTaskList());
        $this->assertSame($date, $emailInvitation->getCreatedDate());
        $this->assertSame('email', $emailInvitation->getEmail());
    }
}