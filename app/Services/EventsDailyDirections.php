<?php

namespace App\Services;

use App\Jobs\UserGetEventsDailyDirection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventsDailyDirections
{
    // Shared across projects: an itinerary includes every physical event of the user.
    public function request(int $userId, string $date, bool $refresh = false): array
    {
        return $this->synchronized($userId, $date, function () use ($userId, $date, $refresh) {
            $row = $this->row($userId, $date);
            if (!$refresh && $row && !$row->searching) {
                return $this->response($userId, $date, $row);
            }

            $state = $this->state($userId, $date);
            if ($state) {
                if ($refresh) {
                    $state['version']++;
                    $this->saveState($userId, $date, $state);
                    $this->pending($userId, $date);
                }

                return $this->response($userId, $date, $this->row($userId, $date));
            }

            $state = ['token' => (string) Str::uuid(), 'version' => 1];
            $this->saveState($userId, $date, $state);
            $this->pending($userId, $date);
            try {
                $this->enqueue($userId, $date, $state['token'], $state['version']);
            } catch (\Throwable $exception) {
                $this->store()->forget($this->key($userId, $date));
                $this->write($userId, $date, false, [], null);
                throw $exception;
            }

            return $this->response($userId, $date, $this->row($userId, $date));
        });
    }

    public function invalidate(int $userId, string $date): void
    {
        $invalidate = function () use ($userId, $date) {
            $this->request($userId, $date, true);
        };

        // Do not expose uncommitted event changes to a worker, or enqueue after rollback.
        if (DB::connection()->transactionLevel() > 0) {
            DB::connection()->afterCommit($invalidate);
        } else {
            $invalidate();
        }
    }

    public function status(int $userId, string $date): array
    {
        // Polling is a plain database read. It neither acquires a cache lock
        // nor schedules a job when a row or a queue lease is missing.
        $query = DB::table('user_events_daily_directions')
            ->where('user_id', $userId)->where('direction_at', $date)
            ->select('user_id', 'direction_at', 'searching');
        if ($query->getConnection()->getDriverName() === 'mysql') {
            // Do not transfer the full geometry from MySQL on every poll.
            $query->selectRaw("CASE WHEN searching = 0 AND JSON_VALID(direction) THEN JSON_UNQUOTE(JSON_EXTRACT(direction, '$.status')) END AS direction_status");
        } else {
            $query->addSelect('direction');
        }
        $row = $query->first();
        $direction = $row && property_exists($row, 'direction') ? json_decode($row->direction ?? '', true) : null;
        $ready = $row && ($row->direction_status ?? ($direction['status'] ?? null)) === 'OK';
        $pending = $row && (bool) $row->searching;

        return [
            'user_id' => $userId,
            'direction_at' => $date,
            'status' => $pending ? 'pending' : ($ready ? 'ready' : 'empty'),
            'searching' => $pending,
            'retry_after' => 2,
        ];
    }

    public function begin(int $userId, string $date, string $token): ?int
    {
        return $this->synchronized($userId, $date, function () use ($userId, $date, $token) {
            $state = $this->state($userId, $date);
            if (!$state) {
                // A job can wait longer than the lease. Recover it unless a newer
                // request has already supplied another token or finished the row.
                $row = $this->row($userId, $date);
                if (!$row || !$row->searching) {
                    return null;
                }
                $state = ['token' => $token, 'version' => 1];
            }
            if ($state['token'] !== $token) {
                return null;
            }

            $this->saveState($userId, $date, $state);
            $this->pending($userId, $date);

            return $state['version'];
        });
    }

    public function complete(int $userId, string $date, string $token, int $version, array $events, ?array $direction): void
    {
        $this->synchronized($userId, $date, function () use ($userId, $date, $token, $version, $events, $direction) {
            $state = $this->state($userId, $date);
            if (!$state || $state['token'] !== $token) {
                return;
            }

            if ($state['version'] !== $version) {
                // One successor covers every edit made while Google was calculating.
                $this->successor($userId, $date, $state);
                return;
            }

            $valid = $direction && ($direction['status'] ?? null) === 'OK';
            $this->write($userId, $date, false, $valid ? $events : [], $valid ? $direction : null);
            $this->store()->forget($this->key($userId, $date));
        });
    }

    public function fail(int $userId, string $date, string $token, ?int $version = null, bool $timedOut = false): void
    {
        $this->synchronized($userId, $date, function () use ($userId, $date, $token, $version, $timedOut) {
            $state = $this->state($userId, $date);
            if (!$state || $state['token'] !== $token) {
                return;
            }

            $lock = $this->executionLock($userId, $date);
            if ($timedOut) {
                // Laravel invokes failed() inside the timed-out worker before
                // exiting; its interrupted handle() cannot release this lock.
                $lock->forceRelease();
            }
            if (!$lock->get()) {
                // A duplicate exhausted its releases while another worker is
                // still calculating. That worker owns the eventual result.
                return;
            }
            try {
                if ($version !== null && $state['version'] !== $version) {
                    $this->successor($userId, $date, $state);
                    return;
                }

                $this->write($userId, $date, false, [], null);
                $this->store()->forget($this->key($userId, $date));
            } finally {
                $lock->release();
            }
        });
    }

    public function executionLock(int $userId, string $date)
    {
        // Longer than the job timeout; a retried/duplicate job cannot call Google concurrently.
        return $this->store()->lock($this->key($userId, $date) . ':running', config('queue.events_daily_directions.running_lock_ttl', 120));
    }

    protected function successor(int $userId, string $date, array $state): void
    {
        $next = array_merge($state, ['token' => (string) Str::uuid()]);
        $this->saveState($userId, $date, $next);
        try {
            $this->enqueue($userId, $date, $next['token'], $next['version']);
        } catch (\Throwable $exception) {
            $this->saveState($userId, $date, $state);
            throw $exception;
        }
    }

    protected function enqueue(int $userId, string $date, string $token, int $version): void
    {
        $connection = config('queue.events_daily_directions.connection', 'redis');
        $driver = config("queue.connections.{$connection}.driver");
        if (!in_array($driver, ['database', 'redis', 'beanstalkd', 'sqs'], true)) {
            throw new \LogicException('Daily directions require an asynchronous queue connection.');
        }

        $job = (new UserGetEventsDailyDirection($userId, $date, $token, $version))
            ->onConnection($connection)
            ->onQueue(config('queue.events_daily_directions.queue', 'google_map'));
        Bus::dispatch($job);
    }

    protected function row(int $userId, string $date): ?object
    {
        return DB::table('user_events_daily_directions')
            ->where('user_id', $userId)->where('direction_at', $date)->first();
    }

    protected function response(int $userId, string $date, ?object $row): array
    {
        $direction = $row ? json_decode($row->direction ?? '', true) : null;
        $pending = !$row || (bool) $row->searching;

        return [
            'user_id' => $userId,
            'direction_at' => $date,
            'status' => $pending ? 'pending' : (($direction['status'] ?? null) === 'OK' ? 'ready' : 'empty'),
            'searching' => $pending,
            // Keep the legacy JSON strings consumed by the detailed map view.
            'events' => $pending ? '[]' : ($row->events ?? '[]'),
            'direction' => $pending ? null : $row->direction,
        ];
    }

    protected function pending(int $userId, string $date): void
    {
        $this->write($userId, $date, true, [], null);
    }

    protected function write(int $userId, string $date, bool $searching, array $events, ?array $direction): void
    {
        DB::table('user_events_daily_directions')->updateOrInsert([
            'user_id' => $userId, 'direction_at' => $date,
        ], [
            'searching' => $searching,
            'events' => json_encode($events),
            'direction' => $direction === null ? null : json_encode($direction),
        ]);
    }

    protected function state(int $userId, string $date): ?array
    {
        return $this->store()->get($this->key($userId, $date));
    }

    protected function saveState(int $userId, string $date, array $state): void
    {
        $this->store()->put($this->key($userId, $date), $state, config('queue.events_daily_directions.state_ttl', 600));
    }

    protected function synchronized(int $userId, string $date, callable $callback)
    {
        return $this->store()->lock($this->key($userId, $date) . ':state', config('queue.events_daily_directions.mutex_ttl', 10))->block(3, $callback);
    }

    protected function store()
    {
        return Cache::store(config('queue.events_daily_directions.cache_store', 'redis'));
    }

    protected function key(int $userId, string $date): string
    {
        return "events-daily-direction:{$userId}:{$date}";
    }
}
