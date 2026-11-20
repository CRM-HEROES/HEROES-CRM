<?php

namespace App\Workflows;

use Carbon\Carbon;

/**
 * Triggers that come from the clock and not from a change:
 *
 *  event.starting_soon  "minutes_before" minutes before an appointment starts
 *                       (0: when it starts)
 *  event.ended          when an appointment ends
 *  prospect.scheduled   on the days and at the hour set (a newsletter): every prospect of the
 *                       audience (a condition) gets an execution. See scanScheduled().
 *
 * Run every minute by workflows:resume. For each active workflow, ONE range
 * query on the appointments of its project (indexed on calendar / started_at),
 * limited to the appointments that reached the trigger time during the last
 * LOOKBACK minutes - so a late or missed tick is caught up, without ever
 * scanning the agenda. An appointment that shows up in several runs starts a
 * single execution (idempotency key: appointment + its start time).
 *
 * The comparison uses the application clock, as the existing appointment
 * filters (startsInLessThan, ...) do.
 */
class TimeTriggerScanner
{
    public const LOOKBACK = 10;

    // Appointments handled per workflow and per run
    public const LIMIT = 1000;

    public function __construct(
        protected WorkflowTriggerResolver $resolver,
        protected WorkflowExecutionManager $executions,
    ) {
    }

    /**
     * @return int executions started
     */
    public function scan(?Carbon $now = null): int
    {
        $now ??= now();
        $started = $this->scanScheduled($now);

        if (!$this->resolver->hasSubject(WorkflowSubject::EVENT)) {
            return $started;
        }

        foreach (['event.starting_soon', 'event.ended'] as $trigger) {
            foreach ($this->resolver->allEntries($trigger) as $projectId => $entries) {
                foreach ($entries as $entry) {
                    $started += $this->scanWorkflow($trigger, $projectId, $entry, $now);
                }
            }
        }

        return $started;
    }

    /**
     * "prospect.scheduled": { "months": [1], "days": [8], "weekdays": [1], "at": "11:00",
     *                         "window_minutes": 60, "items": [audience] }
     *
     * On a day that matches (a month of "months" - none: every month - and a day of "days" or a
     * weekday of "weekdays"), from "at" on, the prospects of the audience get an execution, spread
     * over "window_minutes" so that a whole audience does not leave at the same second: the
     * prospect number n starts n minutes (modulo the window) after the hour. One execution per
     * prospect and per day (idempotency key). Each run only looks at the slice of this minute and
     * of the LOOKBACK ones before, so a missed tick is caught up and the audience is never read whole.
     *
     * @return int executions started
     */
    protected function scanScheduled(Carbon $now): int
    {
        if (!$this->resolver->hasSubject(WorkflowSubject::PROSPECT)) {
            return 0;
        }

        $started = 0;

        foreach ($this->resolver->allEntries('prospect.scheduled') as $projectId => $entries) {
            $local = $now->copy()->setTimezone(ProjectTimezone::for($projectId));

            foreach ($entries as $entry) {
                $started += $this->scanScheduledWorkflow($projectId, $entry, $local);
            }
        }

        return $started;
    }

    /**
     * Is the schedule on this day? (the date is the one of the project)
     */
    public static function dueOn(array $config, Carbon $local): bool
    {
        $months = $config['months'] ?? [];
        $days = $config['days'] ?? [];
        $weekdays = $config['weekdays'] ?? [];

        if ($months && !in_array($local->month, $months, true)) {
            return false;
        }

        return in_array($local->day, $days, true) || in_array($local->isoWeekday(), $weekdays, true);
    }

    protected function scanScheduledWorkflow(int $projectId, array $entry, Carbon $local): int
    {
        $config = $entry['config'] ?? [];

        if (!self::dueOn($config, $local) || !preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', (string) ($config['at'] ?? ''), $m)) {
            return 0;
        }

        $window = max(1, min(720, (int) ($config['window_minutes'] ?? 60)));
        $elapsed = (int) $local->copy()->setTime((int) $m[1], (int) $m[2], 0)->diffInMinutes($local, false);

        if ($elapsed < 0 || $elapsed >= $window + self::LOOKBACK) {
            return 0;
        }

        // The prospects whose turn it is: this minute and the ones just before
        $first = max(0, $elapsed - self::LOOKBACK);
        $last = min($elapsed, $window - 1);

        if ($first > $last) {
            return 0;
        }

        $query = WorkflowSubject::query(WorkflowSubject::PROSPECT, $projectId)
            ->whereRaw('prospects.id % ? between ? and ?', [$window, $first, $last]);

        (new ConditionEvaluator())->constrain($query, WorkflowSubject::PROSPECT, $projectId, $config);

        $day = $local->format('Y-m-d');
        $started = 0;

        foreach ($query->orderBy('prospects.id')->limit(self::LIMIT * 5)->pluck('prospects.id') as $id) {
            if ($this->executions->start(
                $projectId,
                $entry['workflow_id'],
                $entry['version_id'],
                WorkflowSubject::PROSPECT,
                $id,
                "prospect.scheduled:{$entry['workflow_id']}:$day:$id"
            )) {
                $started++;
            }
        }

        return $started;
    }

    protected function scanWorkflow(string $trigger, int $projectId, array $entry, Carbon $now): int
    {
        $query = WorkflowSubject::query(WorkflowSubject::EVENT, $projectId);

        if ($trigger === 'event.starting_soon') {
            $before = (int) ($entry['config']['minutes_before'] ?? 0);
            $format = fn ($date) => $date->format('Y-m-d H:i:s');

            // The reminder time (start - before) is in the last minutes,
            // and the appointment has not started yet (unless "when it starts")
            $query
                ->where('events.started_at', '<=', $format($now->copy()->addMinutes($before)))
                ->where('events.started_at', '>', $format($now->copy()->addMinutes($before - self::LOOKBACK)));

            if ($before > 0) {
                $query->where('events.started_at', '>', $format($now));
            }

            $column = 'started_at';
        } else {
            $query
                ->where('events.ended_at', '<=', $now->format('Y-m-d H:i:s'))
                ->where('events.ended_at', '>', $now->copy()->subMinutes(self::LOOKBACK)->format('Y-m-d H:i:s'));

            $column = 'ended_at';
        }

        $started = 0;

        foreach ($query->orderBy('events.id')->limit(self::LIMIT)->get(['events.id', "events.$column"]) as $event) {
            $at = Carbon::parse($event->{$column})->getTimestamp();

            if ($this->executions->start(
                $projectId,
                $entry['workflow_id'],
                $entry['version_id'],
                WorkflowSubject::EVENT,
                $event->id,
                "$trigger:{$event->id}:$at"
            )) {
                $started++;
            }
        }

        return $started;
    }
}
