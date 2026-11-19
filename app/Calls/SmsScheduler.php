<?php

namespace App\Calls;

use App\Models\Project;
use App\Models\Prospect;
use App\Jobs\Agent\SendScheduledSms;
use App\Models\Sms;
use Illuminate\Support\Facades\Cache;
class SmsScheduler
{
    public function __construct(protected CallEligibility $eligibility)
    {
    }

    public function sendOrSchedule(Prospect $prospect, Project $project, ?int $userId, string $source, string $message, ?callable $create = null): array
    {
        $reason = $this->eligibility->checkSms($prospect, $project, null, $userId, $source);
        if ($reason === 'outside_calling_hours' && $this->canDelay()) {
            return $this->schedule($prospect, $project, $userId, $source, $message, $reason);
        }
        if ($reason) {
            throw new SmsEligibilityException($reason, $this->eligibility->smsMessage($reason, $prospect));
        }

        return ['sms' => $create ? $create() : $this->sendNow($prospect, $project, $userId, $source, $message)];
    }

    /**
     * Outside the working hours of the prospect the message is not lost: it is sent at the next opening.
     * Every other refusal (kill switch, do not call, invalid number, 48 h rule...) stays an error.
     */
    protected function schedule(Prospect $prospect, Project $project, ?int $userId, string $source, string $message, string $reason): array
    {
        $opening = $this->eligibility->nextOpening($prospect);

        // The same message for the same prospect is not scheduled twice (a retried workflow step)
        $key = 'agent-sms:' . $prospect->id . ':' . $source . ':' . md5($message);
        if (Cache::add($key, true, $opening->copy()->addDay())) {
            SendScheduledSms::dispatch($prospect->id, $project->id, $userId, $source, $message)->delay($opening);
        }

        return [
            'scheduled_at' => $opening->toIso8601String(),
            'reason' => $reason,
            'message' => $this->eligibility->smsMessage($reason, $prospect)
                . ' Le SMS est programmé à cette heure et sera revérifié avant l’envoi.',
        ];
    }

    /**
     * With the "sync" queue a delay would be ignored: the SMS would leave right now, outside the hours.
     */
    protected function canDelay(): bool
    {
        return config('queue.default') !== 'sync';
    }

    public function sendNow(Prospect $prospect, Project $project, ?int $userId, string $source, string $message): Sms
    {
        $reason = $this->eligibility->checkSms($prospect, $project, null, $userId, $source);
        if ($reason) {
            throw new SmsEligibilityException($reason, $this->eligibility->smsMessage($reason, $prospect));
        }

        return $prospect->sms()->create([
            'message' => $message,
            'source' => $source,
            'from_user' => true,
            'creator_id' => $userId,
        ]);
    }

}
