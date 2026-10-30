<?php

namespace App\Workflows;

use Carbon\Carbon;

/**
 * Triggers that come from the clock and not from a change:
 *
 *  event.starting_soon  "minutes_before" minutes before an appointment starts
 *                       (0: when it starts)
 *  event.ended          when an appointment ends
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

        if (!$this->resolver->hasSubject(WorkflowSubject::EVENT)) {
            return 0;
        }

        $started = 0;

        foreach (['event.starting_soon', 'event.ended'] as $trigger) {
            foreach ($this->resolver->allEntries($trigger) as $projectId => $entries) {
                foreach ($entries as $entry) {
                    $started += $this->scanWorkflow($trigger, $projectId, $entry, $now);
                }
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
