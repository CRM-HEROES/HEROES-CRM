<?php

namespace App\Calls;

use App\Models\Project;
use App\Models\Prospect;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class WorkingHours
{
    public function isOpen(Prospect $prospect, ?CarbonInterface $at = null): bool
    {
        $local = $this->localTime($prospect, $at);
        $settings = $this->settings($prospect);
        $date = $local->toDateString();
        $days = array_map('intval', $settings['work_days']);
        $holidays = array_map('strval', $settings['holidays']);

        return in_array($local->isoWeekday(), $days, true)
            && !in_array($date, $holidays, true)
            && $local->format('H:i') >= $settings['work_hours']['start']
            && $local->format('H:i') < $settings['work_hours']['end'];
    }

    public function nextOpening(Prospect $prospect, ?CarbonInterface $at = null): CarbonImmutable
    {
        $local = $this->localTime($prospect, $at);
        $settings = $this->settings($prospect);
        $days = array_map('intval', $settings['work_days']);
        $holidays = array_map('strval', $settings['holidays']);

        for ($offset = 0; $offset < 15; $offset++) {
            $candidate = $local->addDays($offset)->setTimeFromTimeString($settings['work_hours']['start']);
            if ($candidate->gt($local)
                && in_array($candidate->isoWeekday(), $days, true)
                && !in_array($candidate->toDateString(), $holidays, true)) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Aucun prochain créneau ouvré n’est configuré dans les 14 jours.');
    }

    public function timezone(Prospect $prospect): string
    {
        $project = $prospect->project;
        $projectSettings = $project ? $this->projectSettings($project) : [];

        return $prospect->call_timezone
            ?: ($projectSettings['timezone'] ?? $project?->timezone ?? config('app.timezone'));
    }

    protected function localTime(Prospect $prospect, ?CarbonInterface $at): CarbonImmutable
    {
        $timezone = $this->timezone($prospect);

        return $at
            ? CarbonImmutable::instance($at)->setTimezone($timezone)
            : CarbonImmutable::now($timezone);
    }

    protected function settings(Prospect $prospect): array
    {
        $project = $prospect->project;
        $overrides = $project ? $this->projectSettings($project) : [];
        $hours = array_merge((array) config('calls.work_hours', []), (array) ($overrides['work_hours'] ?? []));

        return [
            'work_hours' => [
                'start' => $hours['start'] ?? '08:00',
                'end' => $hours['end'] ?? '17:00',
            ],
            'work_days' => $overrides['work_days'] ?? config('calls.work_days', [1, 2, 3, 4, 5]),
            'holidays' => $overrides['holidays'] ?? config('calls.holidays', []),
        ];
    }

    protected function projectSettings(Project $project): array
    {
        return CallSettings::forProject($project);
    }
}
