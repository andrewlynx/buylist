<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $mailerName;

    /**
     * @var string
     */
    private $mailerAddress;

    /**
     * @param string $mailerName
     * @param string $mailerAddress
     */
    public function __construct(string $mailerName, string $mailerAddress)
    {
        $this->mailerName = $mailerName;
        $this->mailerAddress = $mailerAddress;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => 'onMailerSend'
        ];
    }

    /**
     * @param MessageEvent $event
     */
    public function onMailerSend(MessageEvent $event)
    {
        $email = $event->getMessage();
        if (!$email instanceof Email) {
            return;
        }

        $email->from(new Address($this->mailerAddress, $this->mailerName));
    }
}