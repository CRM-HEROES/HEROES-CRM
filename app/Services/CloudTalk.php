<?php

namespace App\Services;

use App\Models\Line;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CloudTalk
{
    protected const AGENTS_URL = 'https://my.cloudtalk.io/api/agents/index.json';
    protected const CALL_HISTORY_URL = 'https://my.cloudtalk.io/api/calls/index.json';
    protected const MAKE_CALL_URL = 'https://my.cloudtalk.io/api/calls/create.json';
    protected const SEND_SMS_URL = 'https://my.cloudtalk.io/api/sms/send.json';
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
     * Send an outbound SMS through the CloudTalk sender number configured
     * on the agent line.
     */
    public function sendSms(Line $line, string $recipient, string $message, array $media = []): array
    {
        $config = $line->config ?: [];

        $this->validateCredentials($config);

        $sender = $this->formatSmsNumber((string) data_get($config, 'numero'));
        $recipient = $this->formatSmsNumber($recipient);
        $message = trim($message);

        if ($message === '' && $media === []) {
            throw new RuntimeException('Le message SMS CloudTalk est vide.', 422);
        }

        $streams = [];

        try {
            $request = Http::timeout(30)
                ->acceptJson()
                ->withBasicAuth($config['api_key_id'], $config['api_key_secret']);

            if ($media === []) {
                $request->asJson();
            } else {
                foreach ($media as $index => $attachment) {
                    $stream = Storage::disk('sms')->readStream($attachment['path']);
                    if (!is_resource($stream)) {
                        throw new RuntimeException('Piece jointe CloudTalk introuvable.', 422);
                    }
                    $streams[] = $stream;
                    $request->attach('images[' . $index . ']', $stream, $attachment['file_name'], [
                        'Content-Type' => $attachment['mime_type'],
                    ]);
                }
            }

            $response = $request->post(self::SEND_SMS_URL, [
                'recipient' => $recipient,
                'sender' => $sender,
                'message' => $message,
            ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException("Impossible de joindre l'API CloudTalk.", 503);
        } finally {
            foreach ($streams as $stream) {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }

        $body = $response->json();
        $cloudTalkStatus = (int) data_get($body, 'responseData.status', $response->status());
        $success = data_get($body, 'responseData.success', data_get($body, 'success', true));

        if (
            !$response->successful() ||
            !in_array($cloudTalkStatus, [200, 201], true) ||
            $success === false ||
            $success === 'false'
        ) {
            $data = data_get($body, 'responseData.data');
            $message = data_get($body, 'responseData.message')
                ?: data_get($body, 'responseData.data.message')
                ?: (is_string($data) ? $data : null);

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

    protected function formatSmsNumber(string $number): string
    {
        $number = preg_replace('/[\s().-]+/', '', trim($number));

        if (!preg_match('/^\+[1-9]\d{1,14}$/', $number)) {
            throw new RuntimeException('Le numero SMS CloudTalk doit etre au format E.164, par exemple +33612345678.', 406);
        }

        return $number;
    }

    protected function normalizeCallHistoryItem(array $item): array
    {
        $cdr = data_get($item, 'Cdr', []);
        $id = (string) data_get($cdr, 'id', '');
        $direction = $this->normalizeCallDirection(
            data_get($cdr, 'type')
                ?: data_get($cdr, 'direction')
                ?: data_get($item, 'Call.direction')
                ?: data_get($item, 'direction')
        );
        $recorded = filter_var(
            data_get($cdr, 'recorded', false),
            FILTER_VALIDATE_BOOLEAN
        );
        $hasVoicemail = $this->cloudTalkHistoryHasVoicemail($cdr, $item);
        $status = $this->normalizeCallHistoryStatus(
            $cdr,
            $item,
            $direction,
            $hasVoicemail
        );
        $voicemailLink = data_get($cdr, 'voicemail_link')
            ?: data_get($cdr, 'voicemail_url')
            ?: data_get($cdr, 'voicemail_recording_link')
            ?: data_get($item, 'Voicemail.recording_link')
            ?: data_get($item, 'Voicemail.url');

        return [
            'id' => $id,
            'uuid' => data_get($cdr, 'uuid')
                ?: data_get($cdr, 'call_uuid')
                ?: data_get($item, 'Call.uuid')
                ?: data_get($item, 'Call.call_uuid')
                ?: data_get($item, 'uuid')
                ?: data_get($item, 'call_uuid'),
            'type' => $direction ?: data_get($cdr, 'type'),
            'direction' => $direction,
            'status' => $status,
            'cloudtalk_status' => data_get($cdr, 'status'),
            'outcome' => $status,
            'missed' => $status === 'missed',
            'has_voicemail' => $hasVoicemail,
            'voicemail_link' => $voicemailLink,
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

    protected function normalizeCallDirection(?string $direction): ?string
    {
        return match (strtolower((string) $direction)) {
            'inbound', 'incoming', 'in' => 'incoming',
            'outbound', 'outgoing', 'out' => 'outgoing',
            'internal' => 'internal',
            default => null,
        };
    }

    protected function normalizeCallHistoryStatus(
        array $cdr,
        array $item,
        ?string $direction,
        bool $hasVoicemail
    ): ?string {
        $rawStatus = data_get($cdr, 'status')
            ?: data_get($cdr, 'result')
            ?: data_get($cdr, 'disposition')
            ?: data_get($item, 'Call.status')
            ?: data_get($item, 'status');

        if ($hasVoicemail) {
            return 'voicemail';
        }

        $statusText = strtolower((string) $rawStatus);

        if ($this->containsAny($statusText, [
            'voicemail',
            'voice_mail',
            'voice mail',
            'messagerie',
        ])) {
            return 'voicemail';
        }

        if ($this->containsAny($statusText, [
            'missed',
            'no_answer',
            'not_answered',
            'unanswered',
            'abandoned',
            'lost',
        ])) {
            return 'missed';
        }

        if (
            $direction === 'incoming' &&
            !$this->hasPositiveDuration(data_get($cdr, 'talking_time')) &&
            !data_get($cdr, 'answered_at') &&
            $this->containsAny($statusText, ['busy', 'failed', 'reject', 'cancel'])
        ) {
            return 'missed';
        }

        return $rawStatus ? (string) $rawStatus : null;
    }

    protected function cloudTalkHistoryHasVoicemail(array $cdr, array $item): bool
    {
        foreach ([
            data_get($cdr, 'has_voicemail'),
            data_get($cdr, 'voicemail'),
            data_get($cdr, 'is_voicemail'),
        ] as $value) {
            if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                return true;
            }
        }

        return (bool) (
            data_get($item, 'Voicemail.id') ||
            data_get($item, 'Voicemail.recording_link') ||
            data_get($cdr, 'voicemail_link') ||
            data_get($cdr, 'voicemail_url') ||
            data_get($cdr, 'voicemail_recording_link') ||
            $this->containsAny(strtolower((string) data_get($cdr, 'status')), [
                'voicemail',
                'voice_mail',
                'voice mail',
            ])
        );
    }

    protected function hasPositiveDuration($value): bool
    {
        return is_numeric($value) && (float) $value > 0;
    }

    protected function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
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
            402 => 'Credit CloudTalk insuffisant pour envoyer le SMS.',
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
