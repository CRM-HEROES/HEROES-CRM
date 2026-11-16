<?php

namespace App\Console\Commands;

use App\Calls\CallCompletion;
use App\Calls\CallEligibility;
use App\Calls\Voice\VoiceProvider;
use App\Models\Agent;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Console\Command;

class CallsChat extends Command
{
    protected $signature = 'calls:chat {prospect_id}';

    protected $description = 'Simule une conversation d’appel en texte, sans fournisseur vocal réel.';

    public function handle(CallEligibility $eligibility, VoiceProvider $provider, CallCompletion $completion): int
    {
        $prospect = Prospect::withoutGlobalScopes()->findOrFail((int) $this->argument('prospect_id'));
        $project = Project::withoutGlobalScopes()->findOrFail($prospect->project_id);
        $agent = Agent::query()->findOrFail((int) $project->call_agent_id);
        $this->line('Simulation textuelle. Saisissez une réponse du prospect (ou "stop" pour terminer).');

        $turns = [];
        while (count($turns) < 10) {
            $reply = trim((string) $this->ask('Réponse simulée du prospect'));
            if (mb_strtolower($reply) === 'stop') {
                break;
            }
            $turns[] = $reply;
            $this->line('Agent simulé : Merci pour votre réponse. Souhaitez-vous un rappel ou un transfert ?');
        }

        $outcome = 'no_answer';
        foreach ($turns as $turn) {
            $lower = mb_strtolower($turn);
            if (str_contains($lower, 'ne plus m’appeler') || str_contains($lower, 'ne plus m\'appeler')) {
                $outcome = 'refused';
                break;
            }
            if (str_contains($lower, 'intéress')) {
                $outcome = 'interested';
                break;
            }
            if (str_contains($lower, 'rappel')) {
                $outcome = 'callback';
            }
        }

        config(['calls.fake_outcome' => $outcome]);
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
