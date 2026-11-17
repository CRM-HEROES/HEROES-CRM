<?php

namespace App\Jobs\Agent;

use App\Calls\CallCompletion;
use App\Calls\CallEligibility;
use App\Calls\Voice\VoiceProvider;
use App\Models\Agent;
use App\Models\CallAttempt;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class StartCallAttempt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(public int $attemptId)
    {
        $this->onQueue('agents');
    }

    public function handle(CallEligibility $eligibility, VoiceProvider $provider, CallCompletion $completion): void
    {
        $attempt = CallAttempt::query()->find($this->attemptId);
        if (!$attempt || $attempt->status !== CallAttempt::QUEUED) {
            return;
        }

        $prospect = Prospect::withoutGlobalScopes()->find($attempt->prospect_id);
        $project = Project::withoutGlobalScopes()->find($attempt->project_id);
        $agent = Agent::query()->find($attempt->agent_id);
        $owner = $agent ? User::query()->find($agent->user_id) : null;
        if (!$prospect || !$project || !$agent || !$owner) {
            $attempt->update([
                'status' => CallAttempt::FAILED,
                'skip_reason' => 'Impossible de retrouver le prospect, le projet ou le propriétaire de l’agent.',
                'ended_at' => now(),
            ]);

            return;
        }

        $reason = $eligibility->reason($prospect, $project);
        if ($reason) {
            $attempt->update(['status' => CallAttempt::SKIPPED, 'skip_reason' => $reason, 'scheduled_at' => null]);

            return;
        }

        $attempt->update(['status' => CallAttempt::CALLING, 'started_at' => now(), 'skip_reason' => null]);
        try {
            $briefing = json_decode((string) $attempt->summary, true) ?: [];
            $result = $provider->start($attempt, $prospect, $briefing);
        } catch (RuntimeException $exception) {
            $attempt->update([
                'status' => CallAttempt::FAILED,
                'skip_reason' => $exception->getMessage(),
                'ended_at' => now(),
            ]);

            return;
        }

        if (($result['outcome'] ?? null) === 'pending') {
            $attempt->update([
                'external_id' => $result['external_id'] ?? null,
                'summary' => $result['summary'] ?? null,
            ]);

            return;
        }

        $completion->complete($attempt, $prospect, $project, $owner, $result);
    }
}
