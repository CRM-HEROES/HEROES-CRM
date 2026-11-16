<?php

namespace App\Jobs;

use App\Agents\AgentConversationService;
use App\Calls\CallEligibility;
use App\Calls\CallSettings;
use App\Models\Agent;
use App\Models\CallAttempt;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PrepareAutomaticCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $prospectId)
    {
    }

    public function handle(CallEligibility $eligibility, AgentConversationService $conversations): void
    {
        if (!CallSettings::globalEnabled()) {
            return;
        }

        $prospect = Prospect::withoutGlobalScopes()->find($this->prospectId);
        $project = $prospect ? Project::withoutGlobalScopes()->find($prospect->project_id) : null;
        $agent = $project?->call_agent_id
            ? Agent::query()->where('project_id', $project->id)->find($project->call_agent_id)
            : null;
        if (!$prospect || !$project || !$agent || !CallSettings::projectEnabled($project) || $agent->status !== Agent::ACTIVE) {
            return;
        }
        if (CallAttempt::query()->where('prospect_id', $prospect->id)
            ->where('agent_id', $agent->id)
            ->where('status', 'queued')
            ->whereNotNull('agent_run_id')
            ->exists()) {
            return;
        }

        $owner = $agent->user;
        if (!$owner) {
            return;
        }

        $attempt = CallAttempt::query()->where('prospect_id', $prospect->id)
            ->where('agent_id', $agent->id)
            ->where('status', 'queued')
            ->whereNull('agent_run_id')
            ->latest('id')
            ->first()
            ?? $eligibility->prepare($prospect, $agent, $project);
        if ($attempt->status !== 'queued') {
            return;
        }

        $run = $conversations->ask(
            $agent,
            $owner,
            $project,
            "Un nouveau prospect a été créé (ID {$prospect->id}). Vérifie son éligibilité et prépare une demande d'appel si elle est autorisée. Ne lance rien sans validation humaine."
        );
        $attempt->update(['agent_run_id' => $run->id]);
    }
}
