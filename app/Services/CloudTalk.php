<?php

namespace App\Services;

use App\Models\Line;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudTalk
{
    protected const AGENTS_URL = 'https://my.cloudtalk.io/api/agents/index.json';
    protected const MAKE_CALL_URL = 'https://my.cloudtalk.io/api/calls/create.json';

    /**
     * Check CloudTalk API credentials by calling an authenticated endpoint.
     */
    public function verifyCredentials(array $config): bool
    {
        $this->agents($config, 1);

        return true;
    }

    /**
     * Fetch CloudTalk agents available for the given API credentials.
     */
    public function agents(array $config, int $limit = 1000, int $page = 1): array
    {
        $this->validateCredentials($config);

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withBasicAuth($config['api_key_id'], $config['api_key_secret'])
                ->get(self::AGENTS_URL, [
                    'limit' => $limit,
                    'page' => $page,
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException("Impossible de joindre l'API CloudTalk.", 503);
        }

        $body = $response->json();
        $cloudTalkStatus = (int) data_get($body, 'responseData.status', 0);

        if (!$response->successful() || ($cloudTalkStatus && $cloudTalkStatus !== 200)) {
            $message = data_get($body, 'responseData.message');
            $status = $cloudTalkStatus ?: $response->status();

            throw new RuntimeException(
                $this->messageForStatus($status, $message),
                $status
            );
        }

        return collect(data_get($body, 'responseData.data', []))
            ->map(function ($item) {
                $agent = data_get($item, 'Agent', $item);
                $firstname = trim((string) data_get($agent, 'firstname', ''));
                $lastname = trim((string) data_get($agent, 'lastname', ''));
                $email = trim((string) data_get($agent, 'email', ''));
                $extension = trim((string) data_get($agent, 'extension', ''));
                $name = trim($firstname . ' ' . $lastname);

                return [
                    'id' => (string) data_get($agent, 'id'),
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'name' => $name,
                    'email' => $email,
                    'extension' => $extension,
                    'label' => $this->agentLabel($name, $email, $extension),
                ];
            })
            ->filter(fn ($agent) => !empty($agent['id']))
            ->values()
            ->all();
    }

    /**
     * Make an outbound call through CloudTalk.
     */
    public function makeCall(Line $line, string $calleeNumber): array
    {
        $config = $line->config ?: [];

        $this->validateConfig($config);

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->withBasicAuth($config['api_key_id'], $config['api_key_secret'])
                ->post(self::MAKE_CALL_URL, [
                    'agent_id' => (int) $config['agent_id'],
                    'callee_number' => $calleeNumber,
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException("Impossible de joindre l'API CloudTalk.", 503);
        }

        $body = $response->json();
        $cloudTalkStatus = (int) data_get($body, 'responseData.status', $response->status());

        if (!$response->successful() || $cloudTalkStatus !== 200) {
            $message = data_get($body, 'responseData.message');
            throw new RuntimeException(
                $this->messageForStatus($cloudTalkStatus, $message),
                $cloudTalkStatus ?: $response->status()
            );
        }

        return is_array($body) ? $body : [];
    }

    /**
     * Format and validate the callee number expected by CloudTalk.
     */
    public function formatCalleeNumber(string $number): string
    {
        $number = preg_replace('/[\s().-]+/', '', trim($number));

        if (!preg_match('/^\+[1-9]\d{1,14}$/', $number)) {
            throw new RuntimeException('Le numero doit etre au format E.164, par exemple +33612345678.', 406);
        }

        return $number;
    }

    protected function validateConfig(array $config): void
    {
        $this->validateCredentials($config);

        if (empty($config['agent_id']) || !is_numeric($config['agent_id'])) {
            throw new RuntimeException('Agent CloudTalk invalide.', 422);
        }
    }

    protected function validateCredentials(array $config): void
    {
        foreach (['api_key_id', 'api_key_secret'] as $field) {
            if (empty($config[$field])) {
                throw new RuntimeException('Identifiants API CloudTalk incomplets.', 422);
            }
        }
    }

    protected function agentLabel(string $name, string $email, string $extension): string
    {
        $label = $name ?: $email;

        if ($extension) {
            $label .= " (#{$extension})";
        }

        return $label ?: 'Agent CloudTalk';
    }

    protected function messageForStatus(int $status, ?string $message): string
    {
        return match ($status) {
            401 => 'Identifiants API CloudTalk invalides.',
            403 => 'Agent CloudTalk non connecte.',
            404 => 'Ressource CloudTalk introuvable.',
            406 => 'Donnees CloudTalk invalides. Verifiez le numero E.164 et l agent.',
            409 => 'Agent CloudTalk deja en appel.',
            500 => 'Erreur API CloudTalk.',
            default => $message ?: 'Erreur CloudTalk.',
        };
    }
}
