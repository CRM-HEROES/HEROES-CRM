<?php

namespace App\Jobs\AiCalls;

use App\Models\Import;
use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LaunchImportAiCallsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $maxConcurrent;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Import $import,
        int $maxConcurrent = 3
    ) {
        $this->maxConcurrent = $maxConcurrent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $prospects = Prospect::query()
            ->where('project_id', $this->import->project_id)
            ->where('import_id', $this->import->id)
            ->get([
                'id',
                'project_id',
                'import_id',
                'ai_agent_id',
                'mobile_phone_number',
                'phone_number',
            ]);

        $agentIds = is_array($this->import->ai_agents) ? $this->import->ai_agents : [];
        $defaultAgentId = null;

        if (!empty($agentIds)) {
            $defaultAgentId = $this->import->project
                ->aiAgents()
                ->where('is_active', true)
                ->whereIn('id', $agentIds)
                ->value('id');
        }

        $eligibleProspects = $prospects->filter(function (Prospect $prospect) {
            return !empty(trim((string) ($prospect->mobile_phone_number ?: $prospect->phone_number)));
        });

        Log::info('Launch AI import calls', [
            'import_id' => $this->import->id,
            'project_id' => $this->import->project_id,
            'prospects_count' => $eligibleProspects->count(),
            'default_agent_id' => $defaultAgentId,
        ]);

        foreach ($eligibleProspects as $prospect) {
            $agentId = $prospect->ai_agent_id ?: $defaultAgentId;

            CallProspectWithAiJob::dispatch($prospect->id, $agentId, $this->maxConcurrent)
                ->onQueue('call_ai');
        }
    }
}
