<?php

namespace App\Console\Commands;

use App\Calls\CallCompletion;
use App\Calls\CallEligibility;
use App\Calls\Voice\VoiceProvider;
use App\Models\Agent;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Console\Command;

class CallsSimulate extends Command
{
    protected $signature = 'calls:simulate {prospect_id}';

    protected $description = 'Simule une tentative d’appel sans contacter de fournisseur vocal réel.';

    public function handle(CallEligibility $eligibility, VoiceProvider $provider, CallCompletion $completion): int
    {
        $prospect = Prospect::withoutGlobalScopes()->findOrFail((int) $this->argument('prospect_id'));
        $project = Project::withoutGlobalScopes()->findOrFail($prospect->project_id);
        $agent = Agent::query()->findOrFail((int) $project->call_agent_id);
        $attempt = $eligibility->prepare($prospect, $agent, $project);

        if ($attempt->status !== 'queued') {
            $this->warn('Appel ignoré : ' . $attempt->skip_reason);
            return self::SUCCESS;
        }

        $attempt->update(['status' => 'calling', 'started_at' => now()]);
        $completion->complete($attempt, $prospect, $project, $agent->user, $provider->start($attempt, $prospect));
        $this->info('Simulation terminée : ' . $attempt->fresh()->outcome);

        return self::SUCCESS;
    }
}
