<?php

namespace App\Calls;

use App\Models\Agent;
use App\Models\CallAttempt;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Sms;
use App\Models\Line;
use Carbon\CarbonInterface;

class CallEligibility
{
    public function __construct(protected WorkingHours $workingHours)
    {
    }

    public function prepare(Prospect $prospect, Agent $agent, Project $project, ?int $runId = null): CallAttempt
    {
        if ($runId) {
            $existing = CallAttempt::query()->where('agent_run_id', $runId)->where('prospect_id', $prospect->id)->first();
            if ($existing) {
                return $existing;
            }
        }

        $reason = $this->reason($prospect, $project);
        return CallAttempt::create([
            'prospect_id' => $prospect->id,
            'agent_id' => $agent->id,
            'project_id' => $project->id,
            'agent_run_id' => $runId,
            'provider' => CallSettings::provider($project),
            'status' => $reason ? CallAttempt::SKIPPED : CallAttempt::QUEUED,
            'skip_reason' => $reason,
        ]);
    }

    public function reason(Prospect $prospect, Project $project): ?string
    {
        if (!CallSettings::globalEnabled()) {
            return 'global_disabled';
        }
        if (!CallSettings::projectEnabled($project)) {
            return 'project_disabled';
        }

        $phone = trim((string) ($prospect->mobile_phone_number ?: $prospect->phone_number));
        $digits = preg_replace('/\D+/', '', $phone);
        if (!$phone || !preg_match('/\A\+?[0-9\s().-]+\z/', $phone) || strlen((string) $digits) < 8 || strlen((string) $digits) > 15) {
            return 'invalid_phone';
        } elseif ((bool) $prospect->do_not_call) {
            return 'do_not_call';
        } elseif (!$this->workingHours->isOpen($prospect)) {
            return 'outside_calling_hours';
        } elseif (CallAttempt::query()->where('prospect_id', $prospect->id)
            ->whereIn('status', [CallAttempt::CALLING, CallAttempt::COMPLETED, CallAttempt::FAILED, CallAttempt::TRANSFERRED, CallAttempt::REFUSED])
            ->count() >= (int) config('calls.max_attempts', 3)) {
            return 'max_attempts_reached';
        }

        $dailyLimit = (int) config('calls.max_daily_calls_per_project', 100);
        if ($dailyLimit > 0 && CallAttempt::query()
            ->where('project_id', $project->id)
            ->whereNotNull('started_at')
            ->whereDate('started_at', now()->toDateString())
            ->count() >= $dailyLimit) {
            return 'project_daily_limit_reached';
        }

        $timezone = $this->workingHours->timezone($prospect);
        $localNow = now($timezone);
        $startedAttempts = CallAttempt::query()->where('prospect_id', $prospect->id)
            ->whereNotNull('started_at')
            ->get(['started_at']);
        if ($startedAttempts->contains(fn (CallAttempt $attempt) => $attempt->started_at->copy()->timezone($timezone)->toDateString() === $localNow->toDateString())) {
            return 'already_called_today';
        }

        $last = CallAttempt::query()->where('prospect_id', $prospect->id)->whereNotNull('started_at')->latest('started_at')->first();
        $lastStartedAt = $last?->started_at?->copy()->timezone($timezone);
        if ($lastStartedAt && $lastStartedAt->gt($localNow->subHours((int) config('calls.callback_delay_hours', 48)))) {
            return 'solicited_recently';
        }

        return null;
    }

    public function nextOpening(Prospect $prospect, ?CarbonInterface $at = null)
    {
        return $this->workingHours->nextOpening($prospect, $at);
    }

    public function callMessage(string $reason, Prospect $prospect): string
    {
        return match ($reason) {
            'global_disabled' => 'Le coupe-circuit global suspend les appels et messages.',
            'project_disabled' => 'Les appels ne sont pas activés pour ce projet.',
            'invalid_phone' => 'Le numéro du prospect est absent ou invalide.',
            'no_consent' => 'Le prospect n’a pas donné son consentement explicite.',
            'consent_declined' => 'Le prospect a explicitement refusé les communications.',
            'do_not_call' => 'Le prospect a demandé à ne plus être appelé.',
            'outside_calling_hours' => $this->outsideHoursCallMessage($prospect),
            'max_attempts_reached' => 'La limite de tentatives autorisée est atteinte.',
            'project_daily_limit_reached' => 'La limite quotidienne d’appels du projet est atteinte.',
            'already_called_today' => 'Un appel a déjà été passé à ce prospect aujourd’hui.',
            'solicited_recently' => 'Ce prospect a été sollicité au cours des 48 dernières heures.',
            default => 'L’appel ne peut pas être lancé : ' . $reason,
        };
    }

    protected function outsideHoursCallMessage(Prospect $prospect): string
    {
        $settings = $prospect->project ? CallSettings::forProject($prospect->project) : [];
        $hours = array_merge((array) config('calls.work_hours', []), (array) ($settings['work_hours'] ?? []));

        return 'Le prospect est hors des horaires d’appel autorisés ('
            . ($hours['start'] ?? '08:00') . '–' . ($hours['end'] ?? '17:00')
            . '). Prochaine ouverture : '
            . $this->workingHours->nextOpening($prospect)->format('d/m/Y à H:i')
            . ' (' . $this->workingHours->timezone($prospect) . ').';
    }

    public function checkSms(
        Prospect $prospect,
        Project $project,
        ?CarbonInterface $at = null,
        ?int $userId = null,
        ?string $source = null
    ): ?string
    {
        foreach ($this->smsChecks($prospect, $project, $at, $userId, $source) as $check) {
            if (!$check['passed']) {
                return $check['reason'];
            }
        }

        return null;
    }

    public function smsChecks(
        Prospect $prospect,
        Project $project,
        ?CarbonInterface $at = null,
        ?int $userId = null,
        ?string $source = null
    ): array {
        $phone = trim((string) ($source === 'cloudtalk'
            ? $prospect->mobile_phone_number
            : ($prospect->mobile_phone_number ?: $prospect->phone_number)));
        $digits = preg_replace('/\D+/', '', $phone);
        $isValidPhone = $phone
            && preg_match('/\A\+?[0-9\s().-]+\z/', $phone)
            && strlen((string) $digits) >= 8
            && strlen((string) $digits) <= 15;
        $cloudTalk = $source === 'cloudtalk';
        $line = $cloudTalk
            ? Line::query()
                ->where('project_id', $project->id)
                ->where('operator', 'cloudtalk')
                ->when($userId !== null, fn ($query) => $query->where('user_id', $userId))
                ->whereNotNull('config')
                ->get()
                ->first(fn (Line $line) => (string) $line->numero !== '')
            : null;
        $provider = CallSettings::provider($project);
        $providerConfigured = !$cloudTalk || $provider === 'cloudtalk';
        if ($cloudTalk && $line) {
            $providerConfigured = $providerConfigured
                && filled(data_get($line->config, 'api_key_id'))
                && filled(data_get($line->config, 'api_key_secret'));
        }
        $sender = preg_replace('/\D+/', '', (string) ($line?->numero ?? ''));
        $recipient = preg_replace('/\D+/', '', $phone);
        $senderEqualsRecipient = $cloudTalk && $sender !== '' && $sender === $recipient;

        $lastSms = Sms::query()
            ->where('prospect_id', $prospect->id)
            ->where('from_user', true)
            ->latest('created_at')
            ->first();
        $timezone = $this->workingHours->timezone($prospect);
        $now = $at
            ? \Carbon\CarbonImmutable::instance($at)->setTimezone($timezone)
            : \Carbon\CarbonImmutable::now($timezone);
        if ($lastSms && $lastSms->created_at->gt($now->copy()->subHours((int) config('calls.sms_frequency_hours', 48)))) {
            $frequencyPassed = false;
        } else {
            $frequencyPassed = true;
        }

        $open = $this->workingHours->isOpen($prospect, $at);

        return [
            $this->check('global', CallSettings::globalEnabled(), 'global_disabled', 'Le coupe-circuit global suspend les appels et messages.'),
            $this->check('project', CallSettings::projectEnabled($project), 'project_disabled', 'Les appels et messages ne sont pas activés pour ce projet.'),
            $this->check('provider', $providerConfigured, 'provider_unconfigured', 'Le fournisseur CloudTalk n’est pas sélectionné ou ses identifiants sont incomplets.'),
            $this->check('cloudtalk_line', !$cloudTalk || $line !== null, 'cloudtalk_line_missing', 'Aucune ligne CloudTalk SMS n’est configurée pour cet utilisateur dans le projet.'),
            $this->check('contact_refusal', !(bool) $prospect->do_not_call, 'do_not_call', 'Le prospect a demandé à ne plus être contacté.'),
            $this->check('hours', $open, 'outside_calling_hours', $this->smsMessage('outside_calling_hours', $prospect)),
            $this->check('frequency', $frequencyPassed, 'sms_frequency_limit', 'Un SMS a déjà été envoyé à ce prospect au cours des 48 dernières heures.'),
            $this->check('phone', (bool) $isValidPhone, 'invalid_phone', 'Le numéro du prospect est absent ou invalide.'),
            $this->check('sender_equals_recipient', !$senderEqualsRecipient, 'sender_equals_recipient', 'Le numéro expéditeur de la ligne CloudTalk est identique au mobile du prospect.'),
        ];
    }

    protected function check(string $key, bool $passed, string $reason, string $message): array
    {
        return [
            'key' => $key,
            'passed' => $passed,
            'reason' => $passed ? null : $reason,
            'message' => $passed ? 'Contrôle réussi.' : $message,
        ];
    }

    public function smsMessage(string $reason, Prospect $prospect): string
    {
        return match ($reason) {
            'global_disabled' => 'Les appels et messages sont suspendus par le coupe-circuit global.',
            'project_disabled' => 'Les appels et messages ne sont pas activés pour ce projet.',
            'provider_unconfigured' => 'Le fournisseur SMS n’est pas configuré pour ce projet.',
            'cloudtalk_line_missing' => 'Aucune ligne CloudTalk SMS n’est configurée pour cet utilisateur dans le projet.',
            'invalid_phone' => 'Le numéro du prospect est absent ou invalide.',
            'no_consent' => 'Le prospect n’a pas donné son consentement explicite aux communications.',
            'consent_declined' => 'Le prospect a explicitement refusé les communications.',
            'do_not_call' => 'Le prospect a demandé à ne plus être contacté.',
            'sender_equals_recipient' => 'Le SMS ne peut pas être envoyé : le numéro expéditeur de la ligne CloudTalk est identique au mobile du prospect.',
            'outside_calling_hours' => 'Le prospect est hors des heures de travail. Prochaine ouverture : '
                . $this->workingHours->nextOpening($prospect)->format('d/m/Y à H:i')
                . ' (' . $this->workingHours->timezone($prospect) . ').',
            'sms_frequency_limit' => 'Un SMS a déjà été envoyé à ce prospect au cours des 48 dernières heures.',
            default => 'Le SMS ne peut pas être envoyé : ' . $reason,
        };
    }
}
