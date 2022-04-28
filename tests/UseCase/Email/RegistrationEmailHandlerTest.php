<?php

namespace App\Tests\UseCase\Email;

use App\Tests\TestTrait;
use App\UseCase\Email\RegistrationEmailHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationEmailHandlerTest extends WebTestCase
{
    use TestTrait;

    public function testSendConfirmationEmail()
    {
        $emailHandler = self::getContainer()->get(RegistrationEmailHandler::class);
        $user = $this->getUser(1);
        $emailHandler->sendConfirmationEmail($user);

        $this->assertEmailCount(1);

        $message = $this->getMailerMessage(0);
        $this->assertContains('Confirm my Email', $message->getHtmlBody());
    }
}