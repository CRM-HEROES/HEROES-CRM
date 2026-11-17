<?php

namespace App\Calls;

use App\Models\Agent;
use App\Models\CallAttempt;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;

class CloudTalkCallCompletionListener
{
    public function __construct(protected CallCompletion $completion)
    {
    }

    public function handle(array $payload, Project $project): bool
    {
        $callId = data_get($payload, 'data.call_id');
        if (!is_scalar($callId) || (string) $callId === '') {
            return false;
        }

        $attempt = CallAttempt::query()
            ->where('project_id', $project->id)
            ->where('external_id', (string) $callId)
            ->where('status', CallAttempt::CALLING)
            ->first();

        if (!$attempt) {
            return false;
        }

        $agent = Agent::query()->find($attempt->agent_id);
        $owner = $agent ? User::query()->find($agent->user_id) : null;
        $prospect = Prospect::withoutGlobalScopes()->find($attempt->prospect_id);

        if (!$owner || !$prospect) {
            $attempt->update([
                'status' => CallAttempt::FAILED,
                'skip_reason' => 'Impossible de rattacher l’appel CloudTalk à son utilisateur ou prospect.',
                'ended_at' => now(),
            ]);

            return false;
        }

        $duration = max(0, (int) data_get($payload, 'data.talking_time', 0));
        $outcome = $this->outcome($payload, $duration);
        $summary = data_get($payload, 'data.summary') ?: data_get($payload, 'data.note');
        $result = [
            'external_id' => (string) $callId,
            'outcome' => $outcome,
            'summary' => is_string($summary) ? mb_substr($summary, 0, 4000) : 'Issue reçue depuis le webhook CloudTalk.',
            'duration_seconds' => $duration,
        ];

        $this->completion->complete($attempt, $prospect, $project, $owner, $result);

        return true;
    }

    protected function outcome(array $payload, int $duration): string
    {
        $outcome = strtolower((string) (data_get($payload, 'data.outcome') ?: data_get($payload, 'data.status')));
        if (in_array($outcome, ['interested', 'not_interested', 'callback', 'refused', 'no_answer', 'transferred'], true)) {
            return $outcome;
        }

        if (filter_var(data_get($payload, 'data.is_voicemail', false), FILTER_VALIDATE_BOOLEAN) || $duration === 0) {
            return 'no_answer';
        }

        return 'answered';
    }
}
