<?php

namespace App\Listeners;

use App\Events\ProspectLabelAttached;
use App\Jobs\GenerateAiQuoteDraft;
use Illuminate\Support\Facades\Log;

/**
 * Second listener on the same event as ProspectLabelAttachedListener —
 * kept separate so this feature never risks breaking the existing
 * revision/pipedrive/category logic. Dispatches the AI quote generation
 * job when a prospect is moved to the configured "assessment completed"
 * label (see config('services.ai_quote')).
 */
class GenerateAiQuoteOnLabelAttachedListener
{
    public function handle(ProspectLabelAttached $event): void
    {
        $triggerLabel = config('services.ai_quote.trigger_label');

        if (!$triggerLabel || $event->label->name !== $triggerLabel) {
            return;
        }

        $prospect = $event->prospect;

        if (data_get($prospect->meta, 'ai_quote_generated_at')) {
            Log::channel('ai-quote')->info('AI quote already generated for this prospect, skipping.', [
                'prospect_id' => $prospect->id,
            ]);
            return;
        }

        Log::channel('ai-quote')->info('AI quote generation triggered.', [
            'prospect_id' => $prospect->id,
            'label' => $event->label->name,
        ]);

        GenerateAiQuoteDraft::dispatch($prospect->id, auth()->id());
    }
}
