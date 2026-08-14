<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessKavkomCall;
use App\Models\KavkomCall;
use App\Models\Prospect;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KavkomWebhookController extends Controller
{
    public function cdr(Request $request)
    {
        $payload = $request->json()->all() ?: $request->all();
        if (!$this->isAuthorized($request, $payload)) {
            Log::channel('kavkom')->warning('Rejected Kavkom CDR webhook.');
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // Kavkom's documented CDR uses camelCase; accept the common mapped
        // variants too so a configured field mapping cannot silently lose a call.
        $callUuid = data_get($payload, 'callUuid') ?: data_get($payload, 'call_uuid')
            ?: data_get($payload, 'data.callUuid') ?: data_get($payload, 'data.call_uuid')
            ?: data_get($payload, 'cdr.callUuid') ?: data_get($payload, 'cdr.call_uuid')
            ?: data_get($payload, 'xml_cdr_uuid') ?: data_get($payload, 'data.xml_cdr_uuid')
            ?: data_get($payload, 'cdr.xml_cdr_uuid')
            ?: data_get($payload, 'uuid');

        if (!is_string($callUuid) || $callUuid === '') {
            // Kavkom's configuration UI has a connectivity test which does
            // not necessarily send a CDR/call UUID. A valid authenticated
            // test must validate the endpoint without queuing a fake call.
            Log::channel('kavkom')->info('Kavkom webhook connectivity test accepted.', [
                'event' => data_get($payload, 'event'),
            ]);
            return response()->json(['received' => true, 'test' => true]);
        }

        $recordingUrl = data_get($payload, 'recordingUrl')
            ?: data_get($payload, 'recording_url')
            ?: data_get($payload, 'record_url')
            ?: data_get($payload, 'recording.url')
            ?: data_get($payload, 'recording.path')
            ?: data_get($payload, 'data.recordingUrl')
            ?: data_get($payload, 'data.recording_url')
            ?: data_get($payload, 'data.recording.path')
            ?: data_get($payload, 'cdr.recordingUrl')
            ?: data_get($payload, 'cdr.recording_url')
            ?: data_get($payload, 'cdr.recording.path');

        $recordingUrl = $this->recordingUrl($recordingUrl);

        $call = KavkomCall::firstOrNew(['call_uuid' => $callUuid]);
        $alreadyProcessed = (bool) $call->processed_at;
        $beingProcessed = $call->status === 'processing';
        if (!$call->exists) {
            $call->prospect_id = $this->findProspectId($payload);
        }
        $call->fill([
            'domain_uuid' => data_get($payload, 'domainUuid') ?: data_get($payload, 'domain_uuid') ?: data_get($payload, 'cdr.domain_uuid') ?: $call->domain_uuid,
            'destination' => data_get($payload, 'number') ?: data_get($payload, 'destination') ?: data_get($payload, 'cdr.destination') ?: $call->destination,
            'recording_url' => $recordingUrl ?: $call->recording_url,
            'webhook_payload' => $payload,
            'completed_at' => now(),
        ])->save();

        if (!$alreadyProcessed && !$beingProcessed) {
            $call->update(['status' => 'cdr_received']);
        }

        Log::channel('kavkom')->info('Kavkom CDR received.', [
            'call_uuid' => $callUuid,
            'prospect_id' => $call->prospect_id,
            'has_recording_url' => (bool) $call->recording_url,
            'direction' => data_get($payload, 'direction') ?: data_get($payload, 'cdr.direction'),
            'duration_seconds' => data_get($payload, 'duration') ?: data_get($payload, 'cdr.duration'),
            'provider_status' => data_get($payload, 'status') ?: data_get($payload, 'cdr.status'),
        ]);

        $callStatus = strtolower((string) (data_get($payload, 'status') ?: data_get($payload, 'cdr.status')));
        if (in_array($callStatus, ['failed', 'no_answer', 'no_user_response', 'busy', 'cancelled', 'canceled', 'unallocated_number'], true)) {
            $call->update([
                'status' => 'ignored',
                'error' => 'Call was not connected (Kavkom status: '.$callStatus.').',
            ]);
            Log::channel('kavkom')->info('Kavkom CDR ignored: call was not connected.', [
                'call_uuid' => $callUuid,
                'status' => $callStatus,
            ]);

            return response()->json(['received' => true, 'ignored' => true], 202);
        }

        if (!$alreadyProcessed && !$beingProcessed) {
            ProcessKavkomCall::dispatch($call->id)->delay(now()->addSeconds(20));
        }

        return response()->json(['received' => true], 202);
    }

    private function isAuthorized(Request $request, array $payload): bool
    {
        $secret = (string) config('services.kavkom.webhook_secret');
        $provided = (string) $request->header('X-Kavkom-Webhook-Secret', $request->bearerToken());

        return $secret !== '' && $provided !== '' && hash_equals($secret, $provided);
    }

    private function findProspectId(array $payload): ?int
    {
        $number = data_get($payload, 'destination') ?: data_get($payload, 'destination_number')
            ?: data_get($payload, 'data.destination') ?: data_get($payload, 'cdr.destination')
            ?: data_get($payload, 'cdr.destination_number');
        if (!is_string($number)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number);
        if ($digits === '') {
            return null;
        }

        return Prospect::withoutGlobalScopes()
            ->whereRaw(
                "REPLACE(REPLACE(REPLACE(REPLACE(phone_number, ' ', ''), '-', ''), '.', ''), '+', '') = ?
                 OR REPLACE(REPLACE(REPLACE(REPLACE(mobile_phone_number, ' ', ''), '-', ''), '.', ''), '+', '') = ?",
                [$digits, $digits]
            )
            ->value('id');
    }

    private function recordingUrl(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') return null;
        if (filter_var($value, FILTER_VALIDATE_URL)) return $value;

        $base = rtrim((string) config('services.kavkom.recording_base_url'), '/');
        return $base !== '' && str_starts_with($value, '/') ? $base.$value : null;
    }
}
