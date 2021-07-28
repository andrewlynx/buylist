<?php

namespace App\Tests\EventListeners;

use App\EventListener\MailerListener;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class MailerListenerTest extends WebTestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([MessageEvent::class => 'onMailerSend'], MailerListener::getSubscribedEvents());
    }

    public function testOnMailerSend()
    {
        $envelope = $this->createMock(Envelope::class);
        $rawMessage = new RawMessage('notEmail');
        $messageEvent = new MessageEvent($rawMessage, $envelope, 'transport');
        $mailerListener = new MailerListener('newMailerName', 'new@mailer.com');

        $mailerListener->onMailerSend($messageEvent);
        $this->assertSame('notEmail', $messageEvent->getMessage()->toString());

        $rawMessage = (new Email())->from(new Address('old@mailer.com', 'oldMailerName'));
        $messageEvent = new MessageEvent($rawMessage, $envelope, 'transport');
        /** @var Email $email */
        $email = $messageEvent->getMessage();
        $this->assertSame('old@mailer.com', $email->getFrom()[0]->getAddress());
        $this->assertSame('oldMailerName', $email->getFrom()[0]->getName());

        $mailerListener->onMailerSend($messageEvent);
        /** @var Email $email */
        $email = $messageEvent->getMessage();
        $this->assertSame('new@mailer.com', $email->getFrom()[0]->getAddress());
        $this->assertSame('newMailerName', $email->getFrom()[0]->getName());
    }
}
