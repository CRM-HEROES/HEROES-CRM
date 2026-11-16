<?php

namespace App\Calls;

use App\Models\Project;
use App\Models\Prospect;
use App\Models\Sms;
class SmsScheduler
{
    public function __construct(protected CallEligibility $eligibility)
    {
    }

    public function sendOrSchedule(Prospect $prospect, Project $project, ?int $userId, string $source, string $message, ?callable $create = null): array
    {
        $reason = $this->eligibility->checkSms($prospect, $project, null, $userId, $source);
        if ($reason) {
            throw new SmsEligibilityException($reason, $this->eligibility->smsMessage($reason, $prospect));
        }

        return ['sms' => $create ? $create() : $this->sendNow($prospect, $project, $userId, $source, $message)];
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
