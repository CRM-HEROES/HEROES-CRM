<?php

namespace App\Observers;

use App\Jobs\AiCalls\LaunchImportAiCallsJob;
use App\Models\Import;
use Illuminate\Support\Facades\Log;

class ImportAiAgentObserver
{
    /**
     * Dispatch batch AI call jobs when import's AI agents are changed.
     */
    public function updated(Import $import): void
    {
        Log::info('Import AI agents changed, launching batch call job......');
        
        // Check if ai_agents was changed
        if (!$import->isDirty('ai_agents')) {
            return;
        }

        // Only proceed if ai_agents is not empty
        if (empty($import->ai_agents)) {
            return;
        }

        $maxConcurrent = (int) config('services.ai_phone_agent.max_concurrent', 3);

        Log::info('Import AI agents changed, launching batch call job.', [
            'import_id' => $import->id,
            'project_id' => $import->project_id,
            'old_agents' => $import->getOriginal('ai_agents'),
            'new_agents' => $import->ai_agents,
        ]);

        LaunchImportAiCallsJob::dispatch($import, $maxConcurrent)
            ->onQueue('call_ai');
    }
}
