<?php

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ProjectUserAccessCheck;
use App\Http\Middleware\UserLastProjectCheck;
use App\Http\Middleware\UserTrackActivity;
use App\Http\Controllers\API\Project\EventsDailyDirectionsController;
use App\Models\User;
use App\Services\Google\Map;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class EventsDailyDirectionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([Authenticated::class]);
        Bus::fake();
        $this->mock(Map::class, function ($mock) {
            $mock->shouldNotReceive('find');
            $mock->shouldNotReceive('direction');
        });
        config([
            'database.default' => 'events_daily_directions_test',
            'database.connections.events_daily_directions_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
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
        $schema->create('user_events_daily_directions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->date('direction_at');
            $table->boolean('searching')->default(false);
            $table->json('direction')->nullable();
            $table->json('events')->nullable();
            $table->unique(['user_id', 'direction_at']);
        });
        DB::table('users')->insert([['id' => 1], ['id' => 2], ['id' => 3], ['id' => 4]]);
        DB::table('projects')->insert([['id' => 1, 'slug' => 'test'], ['id' => 2, 'slug' => 'other']]);
        DB::table('user_project')->insert([
            ['project_id' => 1, 'user_id' => 1],
            ['project_id' => 1, 'user_id' => 2],
            ['project_id' => 1, 'user_id' => 3],
            ['project_id' => 2, 'user_id' => 4],
        ]);
    }

    protected function tearDown(): void
    {
        DB::purge('events_daily_directions_test');
        parent::tearDown();
    }

    public function test_summaries_are_deduplicated_and_omit_the_google_maps_details(): void
    {
        $this->login(true);
        $this->store(2, '2026-10-15', ['status' => 'OK', 'routes' => [[
            'overview_polyline' => ['points' => 'heavy geometry'],
            'legs' => [[
                'distance' => ['text' => '12 km', 'value' => 12000, 'extra' => 'ignored'],
                'duration' => ['text' => '20 minutes', 'value' => 1200],
                'start_address' => 'Private address',
                'steps' => [['polyline' => ['points' => 'heavy geometry']]],
            ]],
        ]]], [10, 11]);

        $this->postJson($this->endpoint(), ['pairs' => [
            $this->pair(2), $this->pair(2), $this->pair(3),
        ]])->assertOk()->assertExactJson(['data' => [
            array_merge($this->pair(2), ['status' => 'ready', 'events' => [10, 11], 'legs' => [[
                'distance' => ['text' => '12 km', 'value' => 12000],
                'duration' => ['text' => '20 minutes', 'value' => 1200],
            ]]]),
            array_merge($this->pair(3), ['status' => 'missing', 'events' => [], 'legs' => []]),
        ]]);
        Bus::assertNothingDispatched();
    }

    public function test_all_statuses_are_reported_without_calculating_missing_routes(): void
    {
        $this->login(true);
        $this->store(2, '2026-10-15', ['status' => 'OK', 'candidates' => [['geometry' => []]]], [10]);
        $this->store(2, '2026-10-16', ['status' => 'ZERO_RESULTS'], [11, 12]);
        $this->store(2, '2026-10-17', null, [], true);
        $this->store(2, '2026-10-18', null, [13]);

        $response = $this->postJson($this->endpoint(), ['pairs' => [
            $this->pair(2), $this->pair(2, '2026-10-16'), $this->pair(2, '2026-10-17'),
            $this->pair(2, '2026-10-18'), $this->pair(2, '2026-10-19'),
        ]])->assertOk();
        $this->assertSame(['ready', 'empty', 'pending', 'empty', 'missing'], array_column($response->json('data'), 'status'));
        foreach ($response->json('data') as $summary) {
            $this->assertSame([], $summary['legs']);
        }
        Bus::assertNothingDispatched();
    }

    public function test_a_user_can_read_their_own_summary_and_specifically_permitted_agendas(): void
    {
        $this->login(false, ['all.project.user.agenda.2', 'all.project.user.agenda.invalid']);
        $this->postJson($this->endpoint(), ['pairs' => [$this->pair(1), $this->pair(2)]])
            ->assertOk()->assertJsonCount(2, 'data');
        $this->postJson($this->endpoint(), ['pairs' => [$this->pair(3)]])->assertForbidden();
    }

    public function test_even_a_global_agenda_permission_cannot_read_a_user_outside_the_project(): void
    {
        $this->login(true);
        $this->store(4, '2026-10-15', ['status' => 'OK'], [40]);
        $this->postJson($this->endpoint(), ['pairs' => [$this->pair(4)]])->assertNotFound();
    }

    public function test_authentication_is_required(): void
    {
        $this->postJson($this->endpoint(), ['pairs' => [$this->pair(1)]])->assertUnauthorized();
    }

    public function test_the_maximum_batch_of_five_hundred_pairs_is_supported(): void
    {
        $this->login(true);
        $pairs = [];
        foreach ([1, 2] as $userId) {
            for ($day = 0; $day < 250; $day++) {
                $pairs[] = $this->pair($userId, (new \DateTimeImmutable('2026-01-01'))->modify("+{$day} days")->format('Y-m-d'));
            }
        }

        $this->postJson($this->endpoint(), ['pairs' => $pairs])->assertOk()->assertJsonCount(500, 'data');
        Bus::assertNothingDispatched();
    }

    /** @dataProvider invalidRequests */
    public function test_invalid_or_excessive_requests_are_rejected(array $body): void
    {
        $this->login(true);
        $this->postJson($this->endpoint(), $body)->assertUnprocessable();
        Bus::assertNothingDispatched();
    }

    public static function invalidRequests(): array
    {
        return [
            'missing pairs' => [[]],
            'empty pairs' => [['pairs' => []]],
            'invalid user' => [['pairs' => [['user_id' => 0, 'direction_at' => '2026-10-15']]]],
            'non-integer user' => [['pairs' => [['user_id' => 1.5, 'direction_at' => '2026-10-15']]]],
            'invalid date' => [['pairs' => [['user_id' => 1, 'direction_at' => '2026-02-30']]]],
            'datetime instead of date' => [['pairs' => [['user_id' => 1, 'direction_at' => '2026-10-15T00:00:00']]]],
            'unbounded pairs' => [['pairs' => array_fill(0, 501, ['user_id' => 1, 'direction_at' => '2026-10-15'])]],
        ];
    }

    public function test_requested_pairs_use_one_direction_query_without_a_cartesian_product(): void
    {
        $this->login(true);
        $this->store(2, '2026-10-15', ['status' => 'OK'], [10]);
        $this->store(3, '2026-10-16', ['status' => 'OK'], [20]);
        $this->store(2, '2026-10-16', ['status' => 'OK'], [30]);
        $this->store(3, '2026-10-15', ['status' => 'OK'], [40]);
        DB::connection()->enableQueryLog();

        $this->postJson($this->endpoint(), ['pairs' => [$this->pair(2), $this->pair(3, '2026-10-16')]])
            ->assertOk()->assertJsonPath('data.0.events', [10])->assertJsonPath('data.1.events', [20]);

        $queries = collect(DB::connection()->getQueryLog())->filter(function ($query) {
            return str_contains($query['query'], 'user_events_daily_directions');
        })->values();
        $this->assertCount(1, $queries);
        $this->assertSame([2, '2026-10-15', 3, '2026-10-16'], $queries[0]['bindings']);
        Bus::assertNothingDispatched();
    }

    public function test_malformed_cached_json_returns_empty_without_triggering_a_calculation(): void
    {
        $this->login(true);
        $this->store(2, '2026-10-15', null, [10, 11]);
        DB::table('user_events_daily_directions')->update(['direction' => '{invalid JSON']);

        $this->postJson($this->endpoint(), ['pairs' => [$this->pair(2)]])
            ->assertOk()->assertExactJson(['data' => [array_merge($this->pair(2), [
                'status' => 'empty', 'events' => [10, 11], 'legs' => [],
            ])]]);
        Bus::assertNothingDispatched();
    }

    public function test_mysql_projects_metrics_in_sql_without_selecting_the_full_direction(): void
    {
        // Compile the production driver's SQL without opening any MySQL database.
        $query = (new MySqlConnection(null, '', '', ['driver' => 'mysql']))->table('user_events_daily_directions');
        $method = new \ReflectionMethod(EventsDailyDirectionsController::class, 'selectDirectionSummary');
        $sql = $method->invoke(new EventsDailyDirectionsController(), $query)->toSql();

        $this->assertStringNotContainsString('`direction`', $sql);
        $this->assertStringNotContainsString('select *', $sql);
        $this->assertStringContainsString("JSON_UNQUOTE(JSON_EXTRACT(direction, '$.status'))", $sql);
        $this->assertStringContainsString("JSON_EXTRACT(direction, '$.routes[0].legs[*].distance')", $sql);
        $this->assertStringContainsString("JSON_EXTRACT(direction, '$.routes[0].legs[*].duration')", $sql);
        $this->assertStringContainsString("JSON_LENGTH(JSON_EXTRACT(direction, '$.routes[0].legs'))", $sql);
        $this->assertSame(4, substr_count($sql, 'CASE WHEN searching = 0 AND JSON_VALID(direction)'));
    }

    public function test_mysql_projected_metrics_keep_the_original_leg_order_and_empty_results(): void
    {
        $controller = new EventsDailyDirectionsController();
        $method = new \ReflectionMethod(EventsDailyDirectionsController::class, 'directionSummary');
        $firstDistance = ['text' => '12 km', 'value' => 12000];
        $secondDistance = ['text' => '8 km', 'value' => 8000];
        $firstDuration = ['text' => '20 minutes', 'value' => 1200];
        $secondDuration = ['text' => '15 minutes', 'value' => 900];
        $summary = $method->invoke($controller, (object) [
            'direction_status' => 'OK',
            'leg_count' => 2,
            'distances' => json_encode([$firstDistance, $secondDistance]),
            'durations' => json_encode([$firstDuration, $secondDuration]),
        ]);
        $this->assertSame(['status' => 'OK', 'legs' => [
            ['distance' => $firstDistance, 'duration' => $firstDuration],
            ['distance' => $secondDistance, 'duration' => $secondDuration],
        ]], $summary);
        $this->assertSame(['status' => null, 'legs' => []], $method->invoke($controller, (object) [
            'direction_status' => null, 'leg_count' => null, 'distances' => null, 'durations' => null,
        ]));
        $this->assertSame(['status' => null, 'legs' => []], $method->invoke($controller, (object) [
            'direction_status' => 'OK', 'leg_count' => 2,
            'distances' => json_encode([$secondDistance]), 'durations' => json_encode([$secondDuration]),
        ]));
        $this->assertSame(['status' => 'OK', 'legs' => [[], [
            'distance' => $secondDistance, 'duration' => $secondDuration,
        ]]], $method->invoke($controller, (object) [
            'direction' => json_encode(['status' => 'OK', 'routes' => [['legs' => [null, [
                'distance' => $secondDistance, 'duration' => $secondDuration,
            ]]]]]),
        ]));
    }

    public function test_numeric_string_event_ids_keep_their_leg_positions(): void
    {
        $this->login(true);
        $this->store(2, '2026-10-15', ['status' => 'OK', 'routes' => [['legs' => [[
            'distance' => ['text' => '12 km', 'value' => 12000],
            'duration' => ['text' => '20 minutes', 'value' => 1200],
        ]]]]], ['10', '11']);

        $this->postJson($this->endpoint(), ['pairs' => [$this->pair(2)]])
            ->assertOk()->assertJsonPath('data.0.status', 'ready')
            ->assertJsonPath('data.0.events', [10, 11])
            ->assertJsonPath('data.0.legs.0.distance.text', '12 km');
    }

    public function test_invalid_event_ids_cannot_shift_metrics_to_another_appointment(): void
    {
        $this->login(true);
        $this->store(2, '2026-10-15', ['status' => 'OK', 'routes' => [['legs' => [[], []]]]], [10, 'invalid', 12]);

        $this->postJson($this->endpoint(), ['pairs' => [$this->pair(2)]])
            ->assertOk()->assertJsonPath('data.0.status', 'empty')
            ->assertJsonPath('data.0.events', [])->assertJsonPath('data.0.legs', []);
    }

    protected function login(bool $canViewOthers, array $permissions = []): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 1, 'role' => 'user']);
        $user->shouldReceive('can')->with('projectUserAgenda', Mockery::any())->andReturn($canViewOthers);
        $user->shouldReceive('getAllPermissions')->andReturn(collect(array_map(function ($permission) {
            return (object) ['name' => $permission];
        }, $permissions)));
        $this->actingAs($user);
    }

    protected function store(int $userId, string $date, ?array $direction, array $events, bool $searching = false): void
    {
        DB::table('user_events_daily_directions')->insert([
            'user_id' => $userId, 'direction_at' => $date, 'searching' => $searching,
            'direction' => $direction === null ? null : json_encode($direction), 'events' => json_encode($events),
        ]);
    }

    protected function pair(int $userId, string $date = '2026-10-15'): array
    {
        return ['user_id' => $userId, 'direction_at' => $date];
    }

    protected function endpoint(): string
    {
        return '/api/project/test/events-daily-directions';
    }
}
