<?php

namespace App\Http\Controllers\Webhook\Project;

use App\Http\Controllers\Controller;
use App\Models\Interaction;
use App\Models\Project;
use App\Models\Prospect;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Webhook for CloudTalk message and call recording events.
 */
class CloudTalkController extends Controller
{
    /**
     * Dispatch CloudTalk events to the matching handler.
     */
    public function store(Request $request, Project $project)
    {

        $payload = $request->all();
        Log::info('CloudTalk Webhook - Payload reçu', [
            'project_id' => $project->id,
            'payload' => $payload,
        ]);
        $event = data_get($payload, 'type');

        if (in_array($event, ['call.recording_ready', 'call.ended'], true)) {
            return DB::transaction(function () use ($event, $payload, $project) {
                // Serialize callbacks for this project to preserve both events and avoid duplicate voicemails.
                $project->newQuery()->whereKey($project->id)->lockForUpdate()->first();

                return $event === 'call.recording_ready'
                    ? $this->storeRecording($payload, $project)
                    : $this->storeCallEnded($payload, $project);
            }, 3);
        }

        if (Str::startsWith((string) data_get($payload, 'type'), 'call.')) {
            return ['message' => 'ignored', 'reason' => 'unsupported_event'];
        }

        return $this->storeIncomingMessage($payload, $project);
    }

    /**
     * Keep the original SMS endpoint compatible with CloudTalk events.
     */
    public function sms(Request $request, Project $project)
    {
        return $this->store($request, $project);
    }

    /**
     * Store an incoming CloudTalk SMS when it can be matched to a prospect.
     */
    protected function storeIncomingMessage(array $payload, Project $project)
    {
        if (!$this->isIncomingMessage($payload)) {
            return ['message' => 'ignored'];
        }

        $number = $this->extractPhoneNumber($payload);
        $message = $this->extractMessage($payload);
        $media = $this->extractMedia($payload);

        if ($number === null || ($message === null && $media === [])) {
            return [
                'message' => 'ignored',
                'reason' => 'missing_number_or_message',
            ];
        }

        $prospect = $this->findProspectByPhone(
            $project,
            $this->normalizePhone($number)
        );

        if (!$prospect) {
            return [
                'message' => 'ignored',
                'reason' => 'prospect_not_found',
            ];
        }

        $receivedAt = $this->extractDate($payload);
        $attributes = [
            'message' => $message ?? '',
            'source' => 'cloudtalk',
            'from_user' => 0,
            'is_incoming' => 1,
            'creator_id' => null,
        ];

        if ($media !== []) {
            $attributes['media'] = $media;
        }

        if ($receivedAt) {
            $attributes['created_at'] = $receivedAt;
            $attributes['sent_at'] = $receivedAt;
        }

        $sms = $prospect->sms()->create($attributes);

        return response()->json([
            'message' => 'SMS CloudTalk entrant enregistre.',
            'sms_id' => $sms->id,
            'prospect_id' => $prospect->id,
        ], 201);
    }

    /**
     * Attach the recording reference to the existing call, without downloading it.
     */
    protected function storeRecording(array $payload, Project $project)
    {
        $callId = $this->firstFilled($payload, ['data.call_id']);
        $callUuid = $this->firstFilled($payload, ['data.call_uuid']);

        if ($callId === null) {
            return ['message' => 'ignored', 'reason' => 'missing_call_id'];
        }

        $interaction = $this->findCallInteraction($project, $callId, $callUuid);

        if (!$interaction) {
            return ['message' => 'ignored', 'reason' => 'interaction_not_found'];
        }

        $data = array_merge($interaction->data ?? [], [
            'call_id' => $callId,
            'recorded' => true,
            'cloudtalk_recording' => $payload,
        ]);

        if ($callUuid !== null) {
            $data['call_uuid'] = $callUuid;
        }

        $interaction->update(['data' => $data]);

        return response()->json([
            'message' => 'Enregistrement CloudTalk associe a l\'interaction.',
            'interaction_id' => $interaction->id,
            'prospect_id' => $interaction->prospect_id,
        ]);
    }

    /**
     * Store completed calls and voicemail without requiring an open CRM session.
     */
    protected function storeCallEnded(array $payload, Project $project)
    {
        $callId = $this->firstFilled($payload, ['data.call_id']);
        $callUuid = $this->firstFilled($payload, ['data.call_uuid']);

        if ($callId === null) {
            return ['message' => 'ignored', 'reason' => 'missing_call_id'];
        }

        $isVoicemail = filter_var(data_get($payload, 'data.is_voicemail', false), FILTER_VALIDATE_BOOLEAN);
        $interaction = $this->findCallInteraction($project, $callId, $callUuid);

        if (!$interaction) {
            $number = $this->extractPhoneNumber($payload);
            $prospect = $number !== null
                ? $this->findProspectByPhone($project, $this->normalizePhone($number))
                : null;

            if (!$prospect) {
                return ['message' => 'ignored', 'reason' => 'prospect_not_found'];
            }

            $interaction = $prospect->interactions()->make([
                'source' => 'cloudtalk', 'creator_id' => null, 'from_user' => false,
                'status' => 'ended',
            ]);
        }

        $agentId = $this->firstFilled($payload, ['data.user.id']);
        if (!$interaction->creator_id && $agentId !== null) {
            $line = $project->lines()->where('operator', 'cloudtalk')
                ->get(['user_id', 'config'])
                ->first(fn ($line) => (string) data_get($line->config, 'agent_id') === $agentId);
            $interaction->creator_id = $line?->user_id;
        }

        $created = !$interaction->exists;
        $data = array_merge($interaction->data ?? [], [
            'call_id' => $callId,
            'cloudtalk_ended' => $payload,
        ]);

        if ($callUuid !== null) {
            $data['call_uuid'] = $callUuid;
        }

        $isVoicemail = $isVoicemail || $interaction->call_outcome === 'voicemail';
        $data['is_voicemail'] = $isVoicemail;
        if ($isVoicemail) {
            $data['has_voicemail'] = true;
            $data['outcome'] = 'voicemail';
            $interaction->status = 'voicemail';
        } elseif ((int) data_get($payload, 'data.talking_time', 0) > 0) {
            $data['outcome'] = 'answered';
            $interaction->status = 'answered';
        }

        $direction = strtolower((string) data_get($payload, 'data.direction'));
        if (in_array($direction, ['inbound', 'incoming', 'outbound', 'outgoing'], true)) {
            $incoming = in_array($direction, ['inbound', 'incoming'], true);
            $data['direction'] = $incoming ? 'incoming' : 'outgoing';
            $interaction->from_user = !$incoming;
        }

        foreach ([
            'number' => 'data.external_number',
            'from_number' => 'data.internal_number.number_e164',
            'started_at' => 'data.started_at',
            'ended_at' => 'data.ended_at',
        ] as $attribute => $path) {
            $value = $this->firstFilled($payload, [$path]);
            if ($value !== null) {
                if (in_array($attribute, ['started_at', 'ended_at'], true)) {
                    $date = $this->extractDate(['date' => $value]);
                    if ($date === null) {
                        continue;
                    }
                    $value = $date->format('Y-m-d H:i:s');
                }
                $interaction->{$attribute} = $value;
            }
        }

        $interaction->data = $data;
        $interaction->save();

        return response()->json([
            'message' => 'Appel CloudTalk enregistre.',
            'interaction_id' => $interaction->id,
            'prospect_id' => $interaction->prospect_id,
        ], $created ? 201 : 200);
    }

    /**
     * Match provider references inside the current project's existing interactions.
     */
    protected function findCallInteraction(Project $project, string $callId, ?string $callUuid): ?Interaction
    {
        $references = array_values(array_unique(array_filter([$callId, $callUuid])));
        // Older iframe payloads can store a JSON number instead of a string.
        if (ctype_digit($callId) && (string) (int) $callId === $callId) {
            $references[] = (int) $callId;
        }

        $paths = [
            'call_id', 'cdr_id', 'id', 'call_uuid', 'uuid',
            'cloudtalk_call.id', 'cloudtalk_call.uuid', 'cloudtalk_call.call_uuid',
            'cloudtalk_history.Cdr.id', 'cloudtalk_history.Cdr.uuid',
            'cloudtalk_history.Cdr.call_uuid', 'cloudtalk_history.Call.uuid',
            'cloudtalk_history.Call.call_uuid',
        ];

        return Interaction::query()
            ->where('source', 'cloudtalk')
            ->whereIn('prospect_id', $project->prospects()->select('prospects.id'))
            ->where(function ($query) use ($references, $paths) {
                foreach ($paths as $path) {
                    $query->orWhereIn('data->' . str_replace('.', '->', $path), $references);
                }
            })
            ->first();
    }

    /**
     * Determine whether the payload is an incoming message event.
     */
    protected function isIncomingMessage(array $payload): bool
    {
        $direction = strtolower((string) $this->firstFilled($payload, [
            'data.direction',
            'direction',
            'payload.direction',
            'message.direction',
            'properties.direction',
            'event.properties.direction',
            'type',
        ]));

        if (in_array($direction, ['inbound', 'incoming', 'received'])) {
            return true;
        }

        if (in_array($direction, ['outbound', 'outgoing', 'sent'])) {
            return false;
        }

        $event = strtolower((string) $this->firstFilled($payload, [
            'event',
            'type',
            'name',
            'action',
            'event_type',
            'payload.event',
            'payload.type',
            'payload.action',
            'data.event',
            'data.type',
            'data.action',
            'event.type',
            'event.action',
        ]));

        $normalizedEvent = str_replace(['_', ' '], '.', $event);

        if (
            Str::contains($normalizedEvent, ['message', 'sms']) &&
            Str::contains($normalizedEvent, ['received', 'incoming', 'inbound'])
        ) {
            return true;
        }

        if (
            Str::contains($normalizedEvent, ['message', 'sms']) &&
            Str::contains($normalizedEvent, ['sent', 'outgoing', 'outbound'])
        ) {
            return false;
        }

        return (bool) ($this->extractPhoneNumber($payload) && $this->extractMessage($payload));
    }

    /**
     * Extract the sender phone number from CloudTalk or automation payloads.
     */
    protected function extractPhoneNumber(array $payload): ?string
    {
        return $this->firstFilled($payload, [
            'data.external_number',
            'external_number',
            'payload.external_number',
            'from',
            'from_number',
            'sender',
            'sender_number',
            'number',
            'phone',
            'phone_number',
            'payload.from',
            'payload.from_number',
            'payload.sender',
            'payload.sender_number',
            'payload.number',
            'payload.phone',
            'payload.phone_number',
            'payload.contact.phone',
            'payload.contact.phone_number',
            'data.from',
            'data.from_number',
            'data.sender',
            'data.sender_number',
            'data.number',
            'data.phone',
            'data.phone_number',
            'data.contact.phone',
            'data.contact.phone_number',
            'data.contact.number',
            'data.customer.phone',
            'data.customer.phone_number',
            'data.customer.number',
            'message.from',
            'message.from_number',
            'message.sender',
            'message.sender_number',
            'message.contact.phone',
            'message.contact.phone_number',
            'properties.from',
            'properties.from_number',
            'properties.sender',
            'properties.sender_number',
            'properties.number',
            'properties.contact.phone',
            'properties.contact.phone_number',
            'event.properties.from',
            'event.properties.from_number',
            'event.properties.sender',
            'event.properties.sender_number',
            'event.properties.number',
            'event.properties.contact.phone',
            'event.properties.contact.phone_number',
        ], [
            'number',
            'phone',
            'phone_number',
            'from',
            'from_number',
            'value',
        ]);
    }

    /**
     * Preserve the media references provided by CloudTalk message events.
     */
    protected function extractMedia(array $payload): array
    {
        $media = data_get($payload, 'data.media', []);
        if (!is_array($media)) {
            return [];
        }

        return collect($media)->filter(function ($attachment) {
            $url = is_array($attachment) ? ($attachment['url'] ?? null) : null;
            return is_string($url) && filter_var($url, FILTER_VALIDATE_URL)
                && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
        })->map(function (array $attachment) {
            return [
                'url' => $attachment['url'],
                'file_name' => is_string($attachment['file_name'] ?? null)
                    ? $attachment['file_name'] : basename(parse_url($attachment['url'], PHP_URL_PATH) ?: 'attachment'),
                'extension' => is_string($attachment['extension'] ?? null) ? $attachment['extension'] : null,
            ];
        })->values()->all();
    }

    /**
     * Extract the SMS text from CloudTalk or automation payloads.
     */
    protected function extractMessage(array $payload): ?string
    {
        return $this->firstFilled($payload, [
            'message',
            'text',
            'body',
            'content',
            'payload.message',
            'payload.text',
            'payload.body',
            'payload.content',
            'data.message',
            'data.text',
            'data.body',
            'data.content',
            'message.message',
            'message.text',
            'message.body',
            'message.content',
            'properties.message',
            'properties.text',
            'properties.body',
            'properties.content',
            'event.properties.message',
            'event.properties.text',
            'event.properties.body',
            'event.properties.content',
        ], [
            'message',
            'text',
            'body',
            'content',
            'value',
        ]);
    }

    /**
     * Extract the received date when CloudTalk provides one.
     */
    protected function extractDate(array $payload): ?Carbon
    {
        $value = $this->firstFilled($payload, [
            'received_at',
            'created_at',
            'timestamp',
            'date',
            'payload.received_at',
            'payload.created_at',
            'payload.timestamp',
            'payload.date',
            'data.received_at',
            'data.created_at',
            'data.timestamp',
            'data.date',
            'occurred_at',
            'message.received_at',
            'message.created_at',
            'message.timestamp',
            'properties.received_at',
            'properties.created_at',
            'properties.timestamp',
            'event.created_at',
            'event.properties.received_at',
            'event.properties.created_at',
            'event.properties.timestamp',
        ]);

        if (!$value) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::createFromTimestamp((int) $value);
            }

            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Return the first non-empty scalar value found at one of the paths.
     */
    protected function firstFilled(array $payload, array $paths, array $arrayKeys = ['value']): ?string
    {
        foreach ($paths as $path) {
            $value = data_get($payload, $path);
            $value = $this->stringValue($value, $arrayKeys);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Convert scalar payload values to strings and read known keys from arrays.
     */
    protected function stringValue($value, array $arrayKeys): ?string
    {
        if (is_array($value)) {
            foreach ($arrayKeys as $key) {
                $nested = $this->stringValue(data_get($value, $key), $arrayKeys);

                if ($nested !== null) {
                    return $nested;
                }
            }

            return null;
        }

        if (is_bool($value) || $value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    /**
     * Find a prospect in the current project from a normalized phone number.
     */
    protected function findProspectByPhone(Project $project, string $normalized): ?Prospect
    {
        if (strlen($normalized) < 6) {
            return null;
        }

        $suffix = substr($normalized, -8);
        $phoneExpression = $this->normalizedPhoneExpression('phone_number');
        $mobileExpression = $this->normalizedPhoneExpression('mobile_phone_number');

        return $project
            ->prospects()
            ->select('id', 'project_id', 'phone_number', 'mobile_phone_number')
            ->where(function($query) use($suffix, $phoneExpression, $mobileExpression) {
                $query
                    ->whereRaw("{$phoneExpression} LIKE ?", ["%{$suffix}"])
                    ->orWhereRaw("{$mobileExpression} LIKE ?", ["%{$suffix}"]);
            })
            ->limit(20)
            ->get()
            ->first(function($prospect) use($normalized) {
                return $this->phonesMatch($prospect->phone_number, $normalized) ||
                    $this->phonesMatch($prospect->mobile_phone_number, $normalized);
            });
    }

    /**
     * Normalize a phone number to digits only.
     */
    protected function normalizePhone(?string $number): string
    {
        return preg_replace('/\D+/', '', (string) $number);
    }

    /**
     * Match phone numbers by exact digits or a safe shared suffix.
     */
    protected function phonesMatch(?string $phoneNumber, string $normalized): bool
    {
        $phoneNumber = $this->normalizePhone($phoneNumber);

        if (!$phoneNumber || !$normalized) {
            return false;
        }

        if ($phoneNumber === $normalized) {
            return true;
        }

        $length = min(strlen($phoneNumber), strlen($normalized), 9);

        return $length >= 6 &&
            substr($phoneNumber, -$length) === substr($normalized, -$length);
    }

    /**
     * SQL expression used to compare phone numbers without common separators.
     */
    protected function normalizedPhoneExpression(string $column): string
    {
        $expression = "`{$column}`";

        foreach ([' ', '.', '-', '(', ')', '+', '/'] as $character) {
            $expression = "REPLACE({$expression}, '{$character}', '')";
        }

        return $expression;
    }
}
