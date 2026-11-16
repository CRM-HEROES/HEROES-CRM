<?php

namespace App\Jobs\Agent;

use App\Calls\CallEligibility;
use App\Calls\SmsScheduler;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendScheduledSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(
        public int $prospectId,
        public int $projectId,
        public ?int $userId,
        public string $source,
        public string $message,
    ) {
        $this->onQueue('agents');
    }

    public function handle(CallEligibility $eligibility, SmsScheduler $scheduler): void
    {
        $prospect = Prospect::withoutGlobalScopes()->where('project_id', $this->projectId)->find($this->prospectId);
        $project = Project::withoutGlobalScopes()->find($this->projectId);
        if (!$prospect || !$project) {
            Log::warning('SMS différé annulé : prospect ou projet introuvable.', [
                'project_id' => $this->projectId,
                'prospect_id' => $this->prospectId,
            ]);

            return;
        }

        $reason = $eligibility->checkSms($prospect, $project, null, $this->userId, $this->source);
        if ($reason) {
            Log::warning('SMS différé non envoyé après contrôle d’éligibilité.', [
                'project_id' => $this->projectId,
                'prospect_id' => $this->prospectId,
                'reason' => $reason,
            ]);

            return;
        }

        try {
            $scheduler->sendNow($prospect, $project, $this->userId, $this->source, $this->message);
        } catch (RuntimeException $exception) {
            Log::warning('SMS différé non envoyé.', [
                'project_id' => $this->projectId,
                'prospect_id' => $this->prospectId,
                'reason' => $exception->getMessage(),
            ]);
        }
    }
}
