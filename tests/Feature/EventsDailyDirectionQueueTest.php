<?php

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ProjectUserAccessCheck;
use App\Http\Middleware\UserLastProjectCheck;
use App\Http\Middleware\UserTrackActivity;
use App\Jobs\UserGetEventsDailyDirection;
use App\Models\Calendar;
use App\Models\Event as Appointment;
use App\Models\User;
use App\Observers\EventObserver;
use App\Services\EventsDailyDirections;
use App\Services\Google\Map;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class EventsDailyDirectionQueueTest extends TestCase
{
    private const DATE = '2026-10-15';
    private const CONNECTION = 'events_daily_direction_queue_test';

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([Authenticated::class]);
        config([
            'database.default' => self::CONNECTION,
            'database.connections.'.self::CONNECTION => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            // Keep the application's default synchronous to catch accidental use of it.
            'queue.default' => 'sync',
            'queue.connections.database.connection' => self::CONNECTION,
            'queue.events_daily_directions.connection' => 'database',
            'queue.events_daily_directions.queue' => 'google_map',
            'queue.events_daily_directions.cache_store' => 'array',
            'queue.events_daily_directions.state_ttl' => 600,
            'queue.events_daily_directions.mutex_ttl' => 10,
            'queue.events_daily_directions.running_lock_ttl' => 120,
            'cache.default' => 'array',
        ]);
        Cache::store('array')->flush();
        $this->withoutMiddleware([
            Authenticate::class, ProjectUserAccessCheck::class,
            UserLastProjectCheck::class, UserTrackActivity::class,
        ]);
        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role')->default('user');
        });
        $schema->create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->softDeletes();
        });
        $schema->create('user_project', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
        });
        $schema->create('calendars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('type');
            $table->softDeletes();
        });
        $schema->create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->string('location')->default('');
            $table->softDeletes();
        });
        $schema->create('user_events_daily_directions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->date('direction_at');
            $table->boolean('searching')->default(false);
            $table->json('direction')->nullable();
            $table->json('events')->nullable();
            $table->unique(['user_id', 'direction_at']);
        });
        $schema->create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
        DB::table('users')->insert([['id' => 1], ['id' => 2], ['id' => 3]]);
        DB::table('projects')->insert([['id' => 1, 'slug' => 'test']]);
        DB::table('user_project')->insert([
            ['project_id' => 1, 'user_id' => 1],
            ['project_id' => 1, 'user_id' => 2],
            ['project_id' => 1, 'user_id' => 3],
        ]);
        DB::table('calendars')->insert([
            ['id' => 1, 'project_id' => 1, 'type' => 'physical'],
            ['id' => 2, 'project_id' => 1, 'type' => 'phone'],
        ]);
        $this->login();
        $this->mock(Map::class, function ($mock) {
            $mock->shouldNotReceive('find');
            $mock->shouldNotReceive('direction');
        });
    }

    protected function tearDown(): void
    {
        DB::purge(self::CONNECTION);
        parent::tearDown();
    }

    public function test_missing_details_return_pending_and_share_one_real_queued_job_across_both_routes(): void
    {
        $this->getJson($this->endpoint())->assertStatus(202)->assertJsonPath('status', 'pending');
        $this->getJson($this->endpoint(true))->assertStatus(202)->assertJsonPath('status', 'pending');
        $this->getJson($this->endpoint().'/status')->assertOk()->assertJsonPath('status', 'pending');

        $this->assertSame('sync', config('queue.default'));
        $this->assertSame(1, DB::table('jobs')->count());
        $this->assertSame('google_map', DB::table('jobs')->value('queue'));
        $this->assertTrue((bool) $this->stored()->searching);
    }

    /** @dataProvider storedStatuses */
    public function test_reading_status_never_queues_work_or_returns_the_google_payload(string $expected, bool $exists, bool $searching): void
    {
        if ($exists) {
            $this->store(['status' => 'OK', 'routes' => [['overview_polyline' => ['points' => 'heavy geometry']]]], [10, 11]);
            DB::table('user_events_daily_directions')->update(['searching' => $searching]);
        }

        $this->getJson($this->endpoint().'/status')->assertOk()
            ->assertJsonPath('status', $expected)
            ->assertJsonPath('user_id', 2)
            ->assertJsonPath('direction_at', self::DATE)
            ->assertJsonPath('searching', $searching)
            ->assertJsonPath('retry_after', 2)
            ->assertJsonMissingPath('direction')->assertJsonMissingPath('events');

        $this->assertSame(0, DB::table('jobs')->count());
        $this->assertSame($exists ? 1 : 0, DB::table('user_events_daily_directions')->count());
    }

    public static function storedStatuses(): array
    {
        return [
            'missing is empty' => ['empty', false, false],
            'queued is pending' => ['pending', true, true],
            'completed is ready' => ['ready', true, false],
        ];
    }

    public function test_ready_details_keep_the_cached_google_data_and_do_not_enqueue_work(): void
    {
        $direction = ['status' => 'OK', 'candidates' => [['geometry' => ['location' => ['lat' => 48.8, 'lng' => 2.3]]]]];
        $this->store($direction, [10]);

        $response = $this->getJson($this->endpoint())->assertOk()->assertJsonPath('status', 'ready');
        $actual = $response->json('direction');
        $this->assertSame($direction, is_string($actual) ? json_decode($actual, true) : $actual);
        $this->assertSame(0, DB::table('jobs')->count());
    }

    public function test_empty_details_are_terminal_without_repeated_jobs(): void
    {
        $this->store(null, []);

        $this->getJson($this->endpoint())->assertOk()->assertJsonPath('status', 'empty');
        $this->getJson($this->endpoint(true))->assertOk()->assertJsonPath('status', 'empty');
        $this->assertSame(0, DB::table('jobs')->count());
    }

    public function test_only_the_worker_calls_google_and_publishes_the_ready_result(): void
    {
        $this->appointment(10, 'Paris');
        $direction = ['status' => 'OK', 'candidates' => [['geometry' => ['location' => ['lat' => 48.8, 'lng' => 2.3]]]]];
        $this->mock(Map::class, function ($mock) use ($direction) {
            $mock->shouldReceive('find')->once()->with('Paris')->andReturn($direction);
            $mock->shouldNotReceive('direction');
        });

        $this->getJson($this->endpoint())->assertStatus(202)->assertJsonPath('status', 'pending');
        $this->runNextJob();

        $this->getJson($this->endpoint())->assertOk()->assertJsonPath('status', 'ready');
        $this->assertSame([10], json_decode($this->stored()->events, true));
        $this->assertSame($direction, json_decode($this->stored()->direction, true));
        $this->assertFalse((bool) $this->stored()->searching);
        $this->assertSame(0, DB::table('jobs')->count());
    }

    public function test_recalculating_a_day_without_appointments_clears_the_old_route(): void
    {
        $this->store(['status' => 'OK', 'routes' => [['legs' => []]]], [10, 11]);
        app(EventsDailyDirections::class)->request(2, self::DATE, true);

        $this->runNextJob();

        $this->getJson($this->endpoint())->assertOk()->assertJsonPath('status', 'empty');
        $this->assertSame([], json_decode($this->stored()->events, true));
        $this->assertNull($this->stored()->direction);
        $this->assertFalse((bool) $this->stored()->searching);
    }

    public function test_multiple_appointments_keep_their_chronological_route_order(): void
    {
        $this->appointment(12, 'Lyon', '14:00:00');
        $this->appointment(10, 'Paris', '09:00:00');
        $this->appointment(11, 'Orleans', '11:00:00');
        $this->mock(Map::class, function ($mock) {
            $mock->shouldNotReceive('find');
            $mock->shouldReceive('direction')->once()->with('Paris', 'Lyon', ['Orleans'])
                ->andReturn(['status' => 'OK', 'routes' => [['legs' => []]]]);
        });

        $this->getJson($this->endpoint())->assertStatus(202);
        $this->runNextJob();

        $this->getJson($this->endpoint())->assertOk()->assertJsonPath('status', 'ready');
        $this->assertSame([10, 11, 12], json_decode($this->stored()->events, true));
    }

    public function test_no_google_result_is_a_terminal_empty_response(): void
    {
        $this->appointment(10, 'Unknown address');
        $this->mock(Map::class, function ($mock) {
            $mock->shouldReceive('find')->once()->andReturn(['status' => 'ZERO_RESULTS', 'candidates' => []]);
        });

        $this->getJson($this->endpoint())->assertStatus(202);
        $this->runNextJob();

        $this->getJson($this->endpoint())->assertOk()->assertJsonPath('status', 'empty');
        $this->assertFalse((bool) $this->stored()->searching);
        $this->assertSame(0, DB::table('jobs')->count());
    }

    /** @dataProvider providerFailures */
    public function test_provider_failures_retry_and_eventually_clear_pending_without_repeated_http_restarts(bool $exception): void
    {
        $this->appointment(10, 'Paris');
        $this->mock(Map::class, function ($mock) use ($exception) {
            $expectation = $mock->shouldReceive('find')->times(3);
            if ($exception) {
                $expectation->andThrow(new \RuntimeException('Google unavailable'));
            } else {
                $expectation->andReturn(['status' => 'OVER_QUERY_LIMIT']);
            }
        });
        $this->getJson($this->endpoint())->assertStatus(202);

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $job = Queue::connection('database')->pop('google_map');
            $this->assertNotNull($job);
            $this->assertSame($attempt, $job->attempts());
            try {
                $job->fire();
                $this->fail('The worker must expose provider failures to the queue retry mechanism.');
            } catch (\RuntimeException $failure) {
                if ($attempt < 3) {
                    $this->assertTrue((bool) $this->stored()->searching);
                    $this->getJson($this->endpoint())->assertStatus(202)->assertJsonPath('status', 'pending');
                    $job->release(0);
                } else {
                    $job->fail($failure);
                }
            }
        }

        $this->assertFalse((bool) $this->stored()->searching);
        $this->getJson($this->endpoint())->assertOk()->assertJsonPath('status', 'empty');
        $this->assertSame(0, DB::table('jobs')->count());
        app(EventsDailyDirections::class)->invalidate(2, self::DATE);
        $this->assertSame(1, DB::table('jobs')->count());
    }

    public static function providerFailures(): array
    {
        return ['provider exception' => [true], 'Google quota status' => [false]];
    }

    public function test_a_change_during_calculation_discards_the_stale_result_and_schedules_the_new_version(): void
    {
        $this->appointment(10, 'Old address');
        $directions = app(EventsDailyDirections::class);
        $this->mock(Map::class, function ($mock) use ($directions) {
            $mock->shouldReceive('find')->once()->with('Old address')->andReturnUsing(function () use ($directions) {
                DB::table('events')->where('id', 10)->update(['location' => 'New address']);
                $directions->invalidate(2, self::DATE);
                return ['status' => 'OK', 'candidates' => [['name' => 'stale result']]];
            });
        });
        $this->getJson($this->endpoint())->assertStatus(202);
        $this->runNextJob();

        $this->assertNotSame('stale result', data_get(json_decode($this->stored()->direction ?? 'null', true), 'candidates.0.name'));
        $this->getJson($this->endpoint())->assertStatus(202)->assertJsonPath('status', 'pending');
        $this->assertSame(1, DB::table('jobs')->count());
        $this->mock(Map::class, function ($mock) {
            $mock->shouldReceive('find')->once()->with('New address')
                ->andReturn(['status' => 'OK', 'candidates' => [['name' => 'fresh result']]]);
        });
        $this->runNextJob();

        $this->getJson($this->endpoint())->assertOk()->assertJsonPath('status', 'ready');
        $this->assertSame('fresh result', data_get(json_decode($this->stored()->direction, true), 'candidates.0.name'));
        $this->assertSame(0, DB::table('jobs')->count());
    }

    public function test_a_duplicate_running_job_cannot_call_google_twice_or_clear_the_active_result_on_failure(): void
    {
        $this->appointment(10, 'Paris');
        $this->getJson($this->endpoint())->assertStatus(202);
        $payload = json_decode(DB::table('jobs')->value('payload'), true);
        $duplicate = unserialize($payload['data']['command']);
        $this->assertInstanceOf(UserGetEventsDailyDirection::class, $duplicate);
        $this->mock(Map::class, function ($mock) use ($duplicate) {
            $mock->shouldReceive('find')->once()->with('Paris')->andReturnUsing(function () use ($duplicate, $mock) {
                $duplicate->handle($mock, app(EventsDailyDirections::class));
                $duplicate->failed(new \RuntimeException('A duplicate delivery exhausted its attempts.'));
                $this->assertTrue((bool) $this->stored()->searching);
                return ['status' => 'OK', 'candidates' => []];
            });
        });

        $this->runNextJob();

        $this->getJson($this->endpoint())->assertOk()->assertJsonPath('status', 'ready');
        $this->assertFalse((bool) $this->stored()->searching);
    }

    public function test_the_grouped_endpoint_reads_pending_results_without_enqueuing_missing_pairs(): void
    {
        $this->getJson($this->endpoint())->assertStatus(202);

        $this->postJson('/api/project/test/events-daily-directions', ['pairs' => [
            ['user_id' => 2, 'direction_at' => self::DATE],
            ['user_id' => 3, 'direction_at' => self::DATE],
        ]])->assertOk()->assertJsonPath('data.0.status', 'pending')->assertJsonPath('data.1.status', 'missing');

        $this->assertSame(1, DB::table('jobs')->count());
    }

    public function test_event_changes_enqueue_immediately_instead_of_after_the_http_response(): void
    {
        $appointment = new Appointment([
            'user_id' => 2, 'calendar_id' => 1,
            'started_at' => self::DATE.' 09:00:00', 'ended_at' => self::DATE.' 10:00:00',
            'location' => 'Paris',
        ]);
        $appointment->setRelation('calendar', Calendar::find(1));
        $appointment->setRelation('user', User::find(2));
        $method = new \ReflectionMethod(EventObserver::class, 'getUserEventsDailyDirection');
        $method->invoke(new EventObserver(), $appointment);

        $this->assertSame(1, DB::table('jobs')->count());
        $this->getJson($this->endpoint())->assertStatus(202)->assertJsonPath('status', 'pending');
        $this->assertSame(1, DB::table('jobs')->count());
    }

    public function test_event_invalidation_waits_for_commit_and_does_not_escape_a_rollback(): void
    {
        $directions = app(EventsDailyDirections::class);
        DB::beginTransaction();
        $directions->invalidate(2, self::DATE);
        $this->assertSame(0, DB::table('jobs')->count());
        DB::commit();
        $this->assertSame(1, DB::table('jobs')->count());

        DB::beginTransaction();
        $directions->invalidate(3, self::DATE);
        $this->assertSame(1, DB::table('jobs')->count());
        DB::rollBack();
        $this->assertSame(1, DB::table('jobs')->count());
        $this->assertFalse(DB::table('user_events_daily_directions')->where('user_id', 3)->exists());
    }

    public function test_moving_an_appointment_refreshes_both_the_previous_and_new_day(): void
    {
        $appointment = new Appointment([
            'user_id' => 2, 'calendar_id' => 1,
            'started_at' => self::DATE.' 09:00:00', 'ended_at' => self::DATE.' 10:00:00',
            'location' => 'Paris',
        ]);
        $appointment->syncOriginal();
        $appointment->forceFill([
            'user_id' => 3, 'started_at' => '2026-10-16 09:00:00', 'ended_at' => '2026-10-16 10:00:00',
        ]);
        $appointment->setRelation('calendar', Calendar::find(1));
        $appointment->setRelation('user', User::find(3));
        $method = new \ReflectionMethod(EventObserver::class, 'getUpdateUserEventsDailyDirection');
        $method->invoke(new EventObserver(), $appointment);

        $this->assertSame(2, DB::table('jobs')->count());
        $pairs = DB::table('user_events_daily_directions')->orderBy('user_id')->get(['user_id', 'direction_at']);
        $this->assertSame([[2, self::DATE], [3, '2026-10-16']], $pairs->map(function ($pair) {
            return [$pair->user_id, $pair->direction_at];
        })->all());
    }

    public function test_invalid_dates_are_rejected_before_any_job_is_queued(): void
    {
        $this->getJson('/api/user/2/events-daily-direction/2026-02-30')->assertUnprocessable();
        $this->getJson('/api/user/2/events-daily-direction/2026-02-30/status')->assertUnprocessable();
        $this->getJson('/api/project/test/user/2/events-daily-direction/not-a-date')->assertUnprocessable();
        $this->assertSame(0, DB::table('jobs')->count());
    }

    private function login(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 1, 'role' => 'user']);
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('getAllPermissions')->andReturn(collect());
        $this->actingAs($user);
    }

    private function endpoint(bool $project = false): string
    {
        return ($project ? '/api/project/test' : '/api').'/user/2/events-daily-direction/'.self::DATE;
    }

    private function store(?array $direction, array $events): void
    {
        DB::table('user_events_daily_directions')->insert([
            'user_id' => 2, 'direction_at' => self::DATE, 'searching' => false,
            'direction' => $direction === null ? null : json_encode($direction), 'events' => json_encode($events),
        ]);
    }

    private function stored(): object
    {
        return DB::table('user_events_daily_directions')->where('user_id', 2)->where('direction_at', self::DATE)->first();
    }

    private function appointment(int $id, string $location, string $time = '09:00:00'): void
    {
        DB::table('events')->insert([
            'id' => $id, 'user_id' => 2, 'calendar_id' => 1,
            'started_at' => self::DATE.' '.$time, 'ended_at' => self::DATE.' '.$time,
            'location' => $location,
        ]);
    }

    private function runNextJob(): void
    {
        $job = Queue::connection('database')->pop('google_map');
        $this->assertNotNull($job, 'A calculation must be available in the real database queue.');
        $job->fire();
    }
}
