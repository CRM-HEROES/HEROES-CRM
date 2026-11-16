<?php

namespace App\Calls;

use App\Models\Prospect;
use Illuminate\Support\Facades\Log;

class ProspectCallConsent
{
    public function update(Prospect $prospect, bool $callConsent, bool $doNotCall, ?int $userId): array
    {
        $before = [
            'call_consent' => $prospect->call_consent,
            'do_not_call' => $prospect->do_not_call,
        ];
        $callConsent = $doNotCall ? false : $callConsent;

        $prospect->update([
            'call_consent' => $callConsent,
            'do_not_call' => $doNotCall,
        ]);
        $prospect->refresh();

        $saved = [
            'call_consent' => (bool) $prospect->call_consent,
            'do_not_call' => (bool) $prospect->do_not_call,
        ];

        if ($before !== $saved) {
            Log::info('Prospect call consent updated', [
                'prospect_id' => $prospect->id,
                'project_id' => $prospect->project_id,
                'user_id' => $userId,
                'before' => $before,
                'after' => $saved,
                'changed_at' => now()->toIso8601String(),
            ]);
        }

        return $saved;
    }
}
