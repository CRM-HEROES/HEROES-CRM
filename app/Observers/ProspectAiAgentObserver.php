<?php

namespace App\Observers;

use App\Jobs\AiCalls\CallProspectWithAiJob;
use App\Models\Prospect;
use Illuminate\Support\Facades\Log;

class ProspectAiAgentObserver
{
    /**
     * Dispatch an AI call job when prospect's AI agent is assigned or changed.
     */

    public function updated(Prospect $prospect): void
    {
        Log::info('Prospect AI agent .....');
        if (!$prospect->isDirty('ai_agent_id')) {
            return;
        }

        if (empty($prospect->ai_agent_id)) {
            return;
        }

        $maxConcurrent = (int) config('services.ai_phone_agent.max_concurrent', 3);

        Log::info('Prospect AI agent changed, launching call job.', [
            'prospect_id' => $prospect->id,
            'old_agent_id' => $prospect->getOriginal('ai_agent_id'),
            'new_agent_id' => $prospect->ai_agent_id,
        ]);

        CallProspectWithAiJob::dispatch(
            $prospect->id,
            $prospect->ai_agent_id,
            $maxConcurrent
        )->onQueue('call_ai');
    }
}
