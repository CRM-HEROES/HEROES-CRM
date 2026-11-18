<?php

namespace App\Calls;

use App\Models\Project;
use App\Models\Prospect;

class GuardedMessaging
{
    public function __construct(
        protected CallEligibility $eligibility,
        protected WorkingHours $workingHours,
    ) {
    }

    public function smsReason(Prospect $prospect, Project $project, ?int $userId, string $source): ?string
    {
        return $this->eligibility->checkSms($prospect, $project, null, $userId, $source);
    }

    public function emailReason(Prospect $prospect, Project $project): ?string
    {
        if (!CallSettings::globalEnabled()) {
            return 'global_disabled';
        }
        if (!CallSettings::projectEnabled($project)) {
            return 'project_disabled';
        }
        if ((bool) $prospect->do_not_call) {
            return 'do_not_call';
        }
        if (!$this->workingHours->isOpen($prospect)) {
            return 'outside_calling_hours';
        }

        return null;
    }

    public function message(string $reason, Prospect $prospect, string $channel): string
    {
        if ($channel === 'sms') {
            return $this->eligibility->smsMessage($reason, $prospect);
        }

        return match ($reason) {
            'global_disabled' => 'Les appels et messages sont suspendus par le coupe-circuit global.',
            'project_disabled' => 'Les appels et messages ne sont pas activés pour ce projet.',
            'no_consent' => 'Le prospect n’a pas donné son consentement explicite aux communications.',
            'consent_declined' => 'Le prospect a explicitement refusé les communications.',
            'do_not_call' => 'Le prospect a demandé à ne plus être contacté.',
            'outside_calling_hours' => 'Le prospect est hors des heures de travail. Prochaine ouverture : '
                . $this->workingHours->nextOpening($prospect)->format('d/m/Y à H:i')
                . ' (' . $this->workingHours->timezone($prospect) . ').',
            default => 'Le message ne peut pas être envoyé : ' . $reason,
        };
    }
}
