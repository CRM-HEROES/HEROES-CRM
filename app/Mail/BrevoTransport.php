<?php

namespace App\Mail;

use GuzzleHttp\Client;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class BrevoTransport extends AbstractTransport
{
    public function __construct(
        protected string $apiKey,
        protected string $defaultFromAddress,
        protected string $defaultFromName = '',
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $sentMessage): void
    {
        $message = $sentMessage->getOriginalMessage();

        if (!$message instanceof Email) {
            throw new \InvalidArgumentException('Brevo requires an email message.');
        }

        $from = $message->getFrom()[0] ?? new Address($this->defaultFromAddress, $this->defaultFromName);
        $payload = [
            'sender' => $this->address($from),
            'to' => $this->addresses($message->getTo()),
            'subject' => $message->getSubject(),
            'htmlContent' => $message->getHtmlBody() ?: nl2br(e($message->getTextBody() ?: '')),
        ];

        if ($message->getTextBody()) {
            $payload['textContent'] = $message->getTextBody();
        }
        if ($message->getCc()) {
            $payload['cc'] = $this->addresses($message->getCc());
        }
        if ($message->getBcc()) {
            $payload['bcc'] = $this->addresses($message->getBcc());
        }
        if ($message->getReplyTo()) {
            $payload['replyTo'] = $this->address($message->getReplyTo()[0]);
        }
        if ($message->getAttachments()) {
            $payload['attachment'] = array_map(function ($attachment) {
                return [
                    'name' => $attachment->getPreparedHeaders()->get('Content-Disposition')->getParameter('filename'),
                    'content' => base64_encode($attachment->getBody()),
                ];
            }, $message->getAttachments());
        }

        (new Client())->post('https://api.brevo.com/v3/smtp/email', [
            'headers' => [
                'accept' => 'application/json',
                'api-key' => $this->apiKey,
                'content-type' => 'application/json',
            ],
            'json' => $payload,
        ]);
    }

    public function __toString(): string
    {
        return 'brevo';
    }

    protected function address(Address $address): array
    {
        return array_filter(['email' => $address->getAddress(), 'name' => $address->getName()]);
    }

    protected function addresses(array $addresses): array
    {
        return array_map(fn (Address $address) => $this->address($address), $addresses);
    }
}