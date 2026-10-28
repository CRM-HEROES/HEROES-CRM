<?php

namespace Tests\Unit;

use App\Exceptions\BrevoTransportException;
use App\Mail\BrevoTransport;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;

class BrevoTransportTest extends TestCase
{
    /** @dataProvider blockedAddresses */
    public function test_blocked_ip_uses_complete_response_and_returns_actionable_safe_message(string $ip): void
    {
        $providerMessage = 'We have detected you are using an unrecognised IP address '.$ip.'. '
            .str_repeat('Additional account security information. ', 12)
            .'Private provider details: secret-api-key and private-email-content.';
        $exception = $this->failure(new Response(401, ['Content-Type' => 'application/json'], json_encode([
            'message' => $providerMessage,
        ])));

        $this->assertInstanceOf(TransportExceptionInterface::class, $exception);
        $this->assertStringContainsString($ip, $exception->getMessage());
        $this->assertStringContainsString('Brevo', $exception->getMessage());
        $this->assertStringContainsString('IP autorisées', $exception->getMessage());
        $this->assertSafeMessage($exception->getMessage());
    }

    public static function blockedAddresses(): array
    {
        return [['41.74.210.228'], ['2001:db8:85a3::8a2e:370:7334']];
    }

    public function test_invalid_credentials_are_reported_without_exposing_provider_payload(): void
    {
        $exception = $this->failure(new Response(401, [], json_encode([
            'message' => 'Key not found: secret-api-key. Request body: private-email-content.',
            'code' => 'unauthorized',
        ])));

        $this->assertStringContainsString('Brevo', $exception->getMessage());
        $this->assertStringContainsString('clé API', $exception->getMessage());
        $this->assertSafeMessage($exception->getMessage());
    }

    public function test_non_authentication_rejections_do_not_echo_arbitrary_response_bodies(): void
    {
        $exception = $this->failure(new Response(400, [], json_encode([
            'message' => 'Invalid email: private-email-content; authentication header: secret-api-key.',
        ])));

        $this->assertStringContainsString('Brevo', $exception->getMessage());
        $this->assertSafeMessage($exception->getMessage());
    }

    public function test_non_json_server_failure_still_produces_a_safe_transport_exception(): void
    {
        $exception = $this->failure(new Response(502, [], '<html>secret-api-key private-email-content</html>'));

        $this->assertStringContainsString('Brevo', $exception->getMessage());
        $this->assertSafeMessage($exception->getMessage());
    }

    public function test_network_failure_does_not_expose_connection_exception_details(): void
    {
        $exception = $this->failure(new ConnectException(
            'Connection failed: secret-api-key private-email-content',
            new Request('POST', 'https://api.brevo.com/v3/smtp/email')
        ));

        $this->assertStringContainsString('Brevo', $exception->getMessage());
        $this->assertSafeMessage($exception->getMessage());
    }

    private function failure($result): BrevoTransportException
    {
        $client = new Client(['handler' => HandlerStack::create(new MockHandler([$result]))]);
        $transport = new BrevoTransport('secret-api-key', 'sender@example.com', 'CRM', $client);
        $email = (new Email())->from('sender@example.com')->to('recipient@example.com')
            ->subject('Prospect')->text('private-email-content');

        try {
            $transport->send($email);
            $this->fail('The transport must fail when the provider rejects the request.');
        } catch (BrevoTransportException $exception) {
            return $exception;
        }
    }

    private function assertSafeMessage(string $message): void
    {
        $this->assertStringNotContainsString('secret-api-key', $message);
        $this->assertStringNotContainsString('private-email-content', $message);
        $this->assertStringNotContainsString('(truncated', $message);
        $this->assertStringNotContainsString('Client error:', $message);
    }
}
