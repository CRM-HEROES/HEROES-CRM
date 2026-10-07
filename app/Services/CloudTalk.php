<?php

namespace App\Services;

use App\Models\Line;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudTalk
{
    protected const AGENTS_URL = 'https://my.cloudtalk.io/api/agents/index.json';
    protected const CALL_HISTORY_URL = 'https://my.cloudtalk.io/api/calls/index.json';
    protected const MAKE_CALL_URL = 'https://my.cloudtalk.io/api/calls/create.json';
    protected const RECORDING_URL = 'https://my.cloudtalk.io/api/calls/recording/%s.json';

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
     * Extract the call id from a CloudTalk API response when the endpoint
     * provides one. Some Make a Call responses only confirm the request.
     */
    public function callId(array $response): ?string
    {
        foreach ([
            'responseData.data.call_id',
            'responseData.data.id',
            'responseData.data.Call.id',
            'responseData.call_id',
            'responseData.id',
            'responseData.call.id',
            'responseData.call_uuid',
            'data.call_id',
            'data.id',
            'Call.id',
            'call.id',
            'call_id',
            'id',
            'call_uuid',
        ] as $path) {
            $value = data_get($response, $path);

            if ($value) {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * Fetch CloudTalk call history for the configured line.
     */
    public function callHistory(Line $line, array $filters = []): array
    {
        $config = $line->config ?: [];

        $this->validateConfig($config);

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withBasicAuth($config['api_key_id'], $config['api_key_secret'])
                ->get(self::CALL_HISTORY_URL, array_filter($filters, fn ($value) => $value !== null && $value !== ''));
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
            ->map(fn ($item) => $this->normalizeCallHistoryItem($item))
            ->filter(fn ($item) => !empty($item['id']))
            ->values()
            ->all();
    }

    /**
     * Fetch recording media without persisting it locally.
     */
    public function recordingMedia(Line $line, string $callId): array
    {
        $config = $line->config ?: [];

        $this->validateConfig($config);

        try {
            $response = Http::timeout(60)
                ->withBasicAuth($config['api_key_id'], $config['api_key_secret'])
                ->get(sprintf(self::RECORDING_URL, $callId));
        } catch (ConnectionException $e) {
            throw new RuntimeException("Impossible de joindre l'API CloudTalk.", 503);
        }

        if (!$response->successful()) {
            $body = $response->json();
            $status = (int) data_get($body, 'responseData.status', $response->status());
            $message = data_get($body, 'responseData.message');

            throw new RuntimeException(
                $this->messageForStatus($status, $message),
                $status ?: $response->status()
            );
        }

        return [
            'body' => $response->body(),
            'content_type' => $response->header('Content-Type') ?: 'audio/x-wav',
        ];
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

    public function normalizePublicNumber(?string $number): string
    {
        return preg_replace('/\D+/', '', (string) $number);
    }

    protected function normalizeCallHistoryItem(array $item): array
    {
        $cdr = data_get($item, 'Cdr', []);
        $id = (string) data_get($cdr, 'id', '');
        $recorded = filter_var(
            data_get($cdr, 'recorded', false),
            FILTER_VALIDATE_BOOLEAN
        );

        return [
            'id' => $id,
            'uuid' => data_get($cdr, 'uuid')
                ?: data_get($cdr, 'call_uuid')
                ?: data_get($item, 'Call.uuid')
                ?: data_get($item, 'Call.call_uuid')
                ?: data_get($item, 'uuid')
                ?: data_get($item, 'call_uuid'),
            'type' => data_get($cdr, 'type'),
            'status' => data_get($cdr, 'status'),
            'number' => data_get($cdr, 'public_external')
                ?: data_get($cdr, 'external_number')
                ?: data_get($item, 'Call.external_number'),
            'from_number' => data_get($cdr, 'public_internal')
                ?: data_get($cdr, 'internal_number')
                ?: data_get($item, 'Call.internal_number'),
            'recorded' => $recorded,
            'recording_link' => data_get($cdr, 'recording_link'),
            'recording_url' => $recorded && $id
                ? sprintf(self::RECORDING_URL, $id)
                : null,
            'started_at' => data_get($cdr, 'started_at'),
            'answered_at' => data_get($cdr, 'answered_at'),
            'ended_at' => data_get($cdr, 'ended_at'),
            'talking_time' => data_get($cdr, 'talking_time'),
            'waiting_time' => data_get($cdr, 'waiting_time'),
            'wrapup_time' => data_get($cdr, 'wrapup_time'),
            'agent' => data_get($item, 'Agent'),
            'contact' => data_get($item, 'Contact'),
            'raw' => $item,
        ];
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
            410 => 'Enregistrement CloudTalk expire.',
            500 => 'Erreur API CloudTalk.',
            default => $message ?: 'Erreur CloudTalk.',
        };
    }
}
