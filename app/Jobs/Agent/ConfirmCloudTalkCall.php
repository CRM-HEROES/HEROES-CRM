<?php

namespace App\Jobs\Agent;

use App\Calls\CloudTalkCallCompletionListener;
use App\Models\Agent;
use App\Models\CallAttempt;
use App\Models\Line;
use App\Models\Project;
use App\Services\CloudTalk;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * A CloudTalk "call.ended" delivery that is not signed proves nothing: before an agent call is closed, the call is
 * read again from the CloudTalk API with the credentials of the line that placed it, and only that answer is used.
 */
class ConfirmCloudTalkCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 4;

    public function __construct(public int $projectId, public string $callId)
    {
        $this->onQueue('agents');
    }

    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public function handle(CloudTalk $cloudTalk, CloudTalkCallCompletionListener $listener): void
    {
        $project = Project::withoutGlobalScopes()->find($this->projectId);
        $attempt = CallAttempt::query()
            ->where('project_id', $this->projectId)
            ->where('external_id', $this->callId)
            ->where('status', CallAttempt::CALLING)
            ->first();
        if (!$project || !$attempt) {
            return;
        }

        $agent = Agent::query()->find($attempt->agent_id);
        $line = $agent ? Line::query()
            ->where('project_id', $project->id)
            ->where('operator', 'cloudtalk')
            ->where('user_id', $agent->user_id)
            ->first() : null;
        if (!$line) {
            Log::warning('Appel CloudTalk non confirmé : ligne introuvable.', ['call_attempt_id' => $attempt->id]);

            return;
        }

        try {
            $history = $cloudTalk->callHistory($line, ['call_id' => $this->callId, 'limit' => 1, 'page' => 1]);
        } catch (RuntimeException $exception) {
            // CloudTalk cannot be reached: try again later, calls:expire-stale closes it if it never works
            $this->release(120);

            return;
        }

        $call = collect($history)->first(fn ($item) => (string) ($item['id'] ?? '') === $this->callId);
        if (!$call || empty($call['ended_at'])) {
            // Not available (or not finished) yet on the CloudTalk side
            $this->release(60);

            return;
        }

        $listener->handle([
            'data' => [
                'call_id' => $this->callId,
                'talking_time' => (int) ($call['talking_time'] ?? 0),
                'is_voicemail' => (bool) ($call['has_voicemail'] ?? false),
            ],
        ], $project);
    }
}
