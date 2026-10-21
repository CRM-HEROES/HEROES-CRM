<?php

namespace App\Jobs;

use App\Services\EventsDailyDirections;
use App\Services\Google\Map;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\TimeoutExceededException;
use Illuminate\Support\Facades\DB;

class UserGetEventsDailyDirection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 90;
    public $failOnTimeout = true;
    public $backoff = [10, 30];
    public $userId;
    public $date;
    public $token;
    public $version;

    public function __construct(int $userId, string $date, string $token, int $version = 1)
    {
        $this->userId = $userId;
        $this->date = $date;
        $this->token = $token;
        $this->version = $version;
    }

    public function handle(Map $map, EventsDailyDirections $directions): void
    {
        $lock = $directions->executionLock($this->userId, $this->date);
        if (!$lock->get()) {
            // This can happen on queue redelivery; never run two Google calls
            // for the same user/date, even when their queue tokens differ.
            $this->release(10);
            return;
        }

        try {
            $version = $directions->begin($this->userId, $this->date, $this->token);
            if ($version === null) {
                return;
            }

            $events = $this->getUserEvents();
            if (!$events) {
                $directions->complete($this->userId, $this->date, $this->token, $version, [], null);
                return;
            }

            try {
                if (count($events) === 1) {
                    $result = $map->find($events[0]->location);
                } else {
                    $locations = array_map(function ($event) {
                        return $event->location;
                    }, $events);
                    $origin = array_shift($locations);
                    $destination = array_pop($locations);
                    $result = $map->direction($origin, $destination, $locations);
                }

                if (!is_array($result) || in_array($result['status'] ?? null, ['UNKNOWN_ERROR', 'OVER_QUERY_LIMIT'], true)) {
                    throw new \RuntimeException('The Google Maps itinerary request did not complete.');
                }
            } catch (\Throwable $exception) {
                // Queue exceptions retry normally. On the last attempt, publish
                // empty (or enqueue the edited version) before the job is failed.
                if ($this->attempts() >= $this->tries) {
                    $directions->complete($this->userId, $this->date, $this->token, $version, [], null);
                }
                throw $exception;
            }

            $eventIds = array_map(function ($event) {
                return (int) $event->id;
            }, $events);
            $directions->complete($this->userId, $this->date, $this->token, $version, $eventIds, $result);
        } finally {
            $lock->release();
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Also runs when a worker exhausts retries after a timeout/crash.
        app(EventsDailyDirections::class)->fail(
            $this->userId, $this->date, $this->token, $this->version, $exception instanceof TimeoutExceededException
        );
    }

    protected function getUserEvents(): array
    {
        return DB::table('events')
            ->select('events.id', 'events.location')
            ->join('calendars', 'events.calendar_id', '=', 'calendars.id')
            ->where('events.user_id', $this->userId)
            ->where('calendars.type', 'physical')
            ->where('events.location', '!=', '')
            ->where('events.started_at', '>=', $this->date . ' 00:00:00')
            ->where('events.ended_at', '<=', $this->date . ' 23:59:59')
            ->whereNull('events.deleted_at')
            ->orderBy('events.started_at')->orderBy('events.id')
            ->limit(24)->get()->all();
    }
}
