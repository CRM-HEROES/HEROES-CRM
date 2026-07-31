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
        $callUuid = data_get($payload, 'call_uuid') ?: data_get($payload, 'data.call_uuid') ?: data_get($payload, 'uuid');

        if (!is_string($callUuid) || $callUuid === '') {
            return response()->json(['message' => 'Missing call_uuid'], 422);
        }

        $recordingUrl = data_get($payload, 'recording_url')
            ?: data_get($payload, 'record_url')
            ?: data_get($payload, 'recording.url')
            ?: data_get($payload, 'data.recording_url');

        $call = KavkomCall::firstOrNew(['call_uuid' => $callUuid]);
        if (!$call->exists) {
            $call->prospect_id = $this->findProspectId($payload);
        }
        $call->fill([
            'status' => 'cdr_received',
            'recording_url' => is_string($recordingUrl) ? $recordingUrl : $call->recording_url,
            'webhook_payload' => $payload,
            'completed_at' => now(),
        ])->save();

        Log::channel('kavkom')->info('Kavkom CDR accepted and queued.', [
            'call_uuid' => $callUuid,
            'prospect_id' => $call->prospect_id,
            'has_recording_url' => (bool) $call->recording_url,
        ]);

        ProcessKavkomCall::dispatch($call->id)->delay(now()->addSeconds(20));

        return response()->json(['received' => true], 202);
    }

    /** The webhook uses the API token already saved in the Kavkom settings modal. */
    private function isAuthorized(Request $request, array $payload): bool
    {
        $domainUuid = data_get($payload, 'domain_uuid') ?: data_get($payload, 'data.domain_uuid');
        $provided = (string) $request->header('X-API-TOKEN', $request->bearerToken());
        if (!is_string($domainUuid) || $domainUuid === '' || $provided === '') return false;

        return UserSetting::query()->whereNull('project_id')->where('key', 'kavkom')->get()
            ->contains(fn (UserSetting $setting) => data_get($setting->value, 'domain_uuid') === $domainUuid
                && hash_equals((string) data_get($setting->value, 'api_token'), $provided));
    }

    private function findProspectId(array $payload): ?int
    {
        $number = data_get($payload, 'destination') ?: data_get($payload, 'destination_number') ?: data_get($payload, 'data.destination');
        if (!is_string($number)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number);
        if ($digits === '') {
            return null;
        }

        return Prospect::withoutGlobalScopes()
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone_number, ' ', ''), '-', ''), '.', ''), '+', '') = ?", [$digits])
            ->value('id');
    }
}
