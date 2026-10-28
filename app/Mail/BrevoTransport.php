<?php

namespace App\Mail;

use App\Exceptions\BrevoTransportException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
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
        protected ?ClientInterface $client = null,
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

        try {
            ($this->client ?? new Client())->request('POST', 'https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'accept' => 'application/json',
                    'api-key' => $this->apiKey,
                    'content-type' => 'application/json',
                ],
                'connect_timeout' => 5,
                'timeout' => 30,
                'json' => $payload,
            ]);
        } catch (RequestException $exception) {
            throw new BrevoTransportException($this->deliveryError($exception), $exception);
        } catch (TransferException $exception) {
            throw new BrevoTransportException('Impossible de joindre Brevo. Réessayez dans quelques instants.', $exception);
        }
    }

    protected function deliveryError(RequestException $exception): string
    {
        $response = $exception->getResponse();
        if (!$response) {
            return 'Impossible de joindre Brevo. Réessayez dans quelques instants.';
        }

        $data = json_decode((string) $response->getBody(), true);
        $message = is_array($data) && is_string($data['message'] ?? null) ? $data['message'] : '';
        if (in_array($response->getStatusCode(), [401, 403], true)
            && preg_match('/\b(?:unrecogni[sz]ed|unauthori[sz]ed) IP(?: address)?\b/i', $message)) {
            $ip = null;
            if (preg_match('/\bIP(?: address)?\s+([0-9a-f:.]+)/i', $message, $matches)) {
                $ip = filter_var(rtrim($matches[1], '.'), FILTER_VALIDATE_IP) ?: null;
            }

            return 'Brevo bloque l’envoi : '.($ip ? 'l’adresse IP '.$ip.' n’est pas autorisée.' : 'l’adresse IP du serveur n’est pas autorisée.')
                .' Autorisez cette adresse dans Brevo, Paramètres > Sécurité > IP autorisées, puis réessayez.';
        }

        if ($response->getStatusCode() === 401) {
            return 'Brevo refuse l’authentification. Vérifiez la clé API dans les paramètres e-mail du projet.';
        }
        if ($response->getStatusCode() === 429) {
            return 'La limite d’envoi Brevo est atteinte. Réessayez plus tard ou vérifiez les quotas de votre compte Brevo.';
        }
        if ($response->getStatusCode() >= 500) {
            return 'Brevo est temporairement indisponible. Réessayez dans quelques instants.';
        }

        return 'Brevo a refusé l’envoi. Vérifiez l’expéditeur, les destinataires et les paramètres e-mail du projet.';
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
