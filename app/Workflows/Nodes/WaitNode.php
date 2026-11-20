<?php

namespace App\Workflows\Nodes;

use App\Workflows\NodeResult;
use App\Workflows\ProjectTimezone;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * "Wait". Three ways to say until when (the timezone is the one of the node,
 * else the one of the project, never the one of the server):
 *
 *  duration   { "minutes": 30, "hours": 2, "days": 1 }          (the durations add up)
 *  time       { "until_time": "09:00", "days": [1,2,3,4,5]? }    the next 09:00 (on one of
 *                                                                these ISO weekdays, if given)
 *  date       { "until_date": "2026-12-24 08:00" }              that moment; already past: no wait
 *  relative   { "relative_to": "next_event", "offset_days": 7, "direction": "before", "at": "09:00" }
 *             until 7 days before the next appointment of the prospect, at 09:00 (project time).
 *             "relative_to": next_event | last_event | created (the day the prospect was created);
 *             "direction": before | after; "at": optional (else the hour of the appointment).
 *             The moment is already past, or there is no appointment: the workflow ends here
 *             (what is sent for "7 days before" is of no use 3 days before),
 *             or goes on when "if_past": "skip". "check_only": true does not wait: it only checks
 *             that the moment is still to come ("the appointment is in 3 days or more").
 *
 * Nothing is blocked: the engine stores the execution as "waiting" with its
 * resume date, and the scheduler resumes it. Never a sleep().
 */
class WaitNode implements NodeExecutorInterface
{
    public const DURATION = 'duration';
    public const TIME = 'time';
    public const DATE = 'date';
    public const RELATIVE = 'relative';

    public const ANCHORS = ['next_event', 'last_event', 'created'];

    /**
     * Which way the node expresses its wait (null: none is valid)
     */
    public static function mode(array $config): ?string
    {
        if (!empty($config['relative_to'])) {
            return self::RELATIVE;
        }

        if (!empty($config['until_date'])) {
            return self::DATE;
        }

        if (!empty($config['until_time'])) {
            return self::TIME;
        }

        return self::seconds($config) > 0 ? self::DURATION : null;
    }

    /**
     * Duration of the wait in seconds (0 if not valid)
     */
    public static function seconds(array $config): int
    {
        return (int) ($config['minutes'] ?? 0) * 60
            + (int) ($config['hours'] ?? 0) * 3600
            + (int) ($config['days'] ?? 0) * 86400;
    }

    /**
     * The moment the wait ends, null if the configuration is invalid
     * (a "date" already past gives a date in the past: no wait)
     */
    public static function resumeAt(array $config, string $timezone, CarbonInterface $now): ?CarbonInterface
    {
        switch (self::mode($config)) {
            case self::DURATION:
                return $now->copy()->addSeconds(self::seconds($config));

            case self::DATE:
                try {
                    return Carbon::parse($config['until_date'], $timezone)->setTimezone($now->getTimezone());
                } catch (\Throwable $e) {
                    return null;
                }

            case self::TIME:
                if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', (string) $config['until_time'], $m)) {
                    return null;
                }

                $days = $config['days'] ?? [1, 2, 3, 4, 5, 6, 7];
                $local = $now->copy()->setTimezone($timezone);
                $candidate = $local->copy()->setTime((int) $m[1], (int) $m[2]);

                // The next occurrence, on an allowed day
                for ($i = 0; $i < 8; $i++) {
                    if ($candidate->gt($local) && in_array($candidate->isoWeekday(), $days)) {
                        return $candidate->setTimezone($now->getTimezone());
                    }
                    $candidate->addDay();
                }

                return null;
        }

        return null;
    }

    /**
     * The date the prospect of the execution is counted from, or null when there is none
     */
    protected function anchor(NodeContext $context, string $anchor): ?CarbonInterface
    {
        $execution = $context->execution;
        $prospectId = match ($execution->subject_type) {
            'prospect' => $execution->subject_id,
            'event' => DB::table('events')->where('id', $execution->subject_id)->value('prospect_id'),
            'order' => DB::table('orders')->where('id', $execution->subject_id)->value('prospect_id'),
            default => null,
        };

        if (!$prospectId) {
            return null;
        }

        if ($anchor === 'created') {
            $created = DB::table('prospects')->where('id', $prospectId)->value('created_at');

            return $created ? Carbon::parse($created) : null;
        }

        $events = DB::table('events')->where('prospect_id', $prospectId)->whereNull('deleted_at');
        $date = $anchor === 'next_event'
            ? $events->where('started_at', '>', now())->min('started_at')
            : $events->where('started_at', '<=', now())->max('started_at');

        return $date ? Carbon::parse($date) : null;
    }

    /**
     * "N days before / after the appointment": see the class
     */
    protected function relative(NodeContext $context, array $config): NodeResult
    {
        $timezone = $config['timezone'] ?? ProjectTimezone::for($context->execution->project_id);
        $anchor = $this->anchor($context, (string) $config['relative_to']);
        $skip = ($config['if_past'] ?? 'end') === 'skip';

        if (!$anchor) {
            return $skip
                ? NodeResult::completed(['skipped' => 'No date to count from.'])
                : NodeResult::end(['skipped' => 'No date to count from.']);
        }

        $days = (int) ($config['offset_days'] ?? 0);
        $target = $anchor->copy()->setTimezone($timezone);
        $target = ($config['direction'] ?? 'before') === 'after' ? $target->addDays($days) : $target->subDays($days);

        if (!empty($config['at']) && preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', (string) $config['at'], $m)) {
            $target->setTime((int) $m[1], (int) $m[2]);
        }

        $until = $target->setTimezone(now()->getTimezone());

        if ($until->lte(now())) {
            return $skip
                ? NodeResult::completed(['skipped' => 'The moment is already past.', 'anchor' => $anchor->toIso8601String()])
                : NodeResult::end(['skipped' => 'The moment is already past.', 'anchor' => $anchor->toIso8601String()]);
        }

        if (!empty($config['check_only'])) {
            return NodeResult::completed(['anchor' => $anchor->toIso8601String()]);
        }

        if ($until->gt(now()->addDays(config('workflows.max_wait_days')))) {
            return NodeResult::failed('The wait is too long.');
        }

        return NodeResult::waitUntil($until, ['resume_at' => $until->toIso8601String(), 'anchor' => $anchor->toIso8601String()]);
    }

    public function execute(NodeContext $context): NodeResult
    {
        $config = $context->config();

        if (self::mode($config) === self::RELATIVE) {
            return $this->relative($context, $config);
        }

        // The timezone only matters for a time or a date, not for a duration
        $timezone = self::mode($config) === self::DURATION
            ? config('app.timezone')
            : ($config['timezone'] ?? ProjectTimezone::for($context->execution->project_id));
        $until = self::resumeAt($config, $timezone, now());

        if (!$until) {
            return NodeResult::failed('Invalid wait.');
        }

        if ($until->lte(now())) {
            // "Until a date" that is already past
            return NodeResult::completed(['skipped' => 'The date is already past.']);
        }

        if ($until->gt(now()->addDays(config('workflows.max_wait_days')))) {
            return NodeResult::failed('The wait is too long.');
        }

        return NodeResult::waitUntil($until, ['resume_at' => $until->toIso8601String()]);
    }
}
