<?php

namespace App\Http\Controllers\Webhook\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Webhook for CloudTalk message events.
 */
class CloudTalkController extends Controller
{
    /**
     * Store an incoming CloudTalk SMS when it can be matched to a prospect.
     */
    public function sms(Request $request, Project $project)
    {
        $payload = $request->all();

        if (!$this->isIncomingMessage($payload)) {
            return ['message' => 'ignored'];
        }

        $number = $this->extractPhoneNumber($payload);
        $message = $this->extractMessage($payload);

        if (!$number || !$message) {
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
            'message' => $message,
            'source' => 'cloudtalk',
            'from_user' => 0,
            'is_incoming' => 1,
            'creator_id' => null,
        ];

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
     * Determine whether the payload is an incoming message event.
     */
    protected function isIncomingMessage(array $payload): bool
    {
        $direction = strtolower((string) $this->firstFilled($payload, [
            'direction',
            'type',
            'payload.direction',
            'data.direction',
            'message.direction',
            'properties.direction',
            'event.properties.direction',
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
