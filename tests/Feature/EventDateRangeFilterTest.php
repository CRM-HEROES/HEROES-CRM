<?php

namespace Tests\Feature;

use App\Filters\EventFilters;
use App\Filters\EventRequestFilters;
use App\Models\Event;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EventDateRangeFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'event_date_range_test',
            'database.connections.event_date_range_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        DB::connection()->getSchemaBuilder()->create('events', function (Blueprint $table) {
            $table->id();
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->softDeletes();
        });

        $events = [
            [1, '2026-10-14 08:00:00', '2026-10-14 23:59:59'],
            [2, '2026-10-14 23:00:00', '2026-10-15 01:00:00'],
            [3, '2026-10-15 09:00:00', '2026-10-15 10:00:00'],
            [4, '2026-10-15 23:00:00', '2026-10-16 01:00:00'],
            [5, '2026-10-12 09:00:00', '2026-10-20 10:00:00'],
            [6, '2026-10-14 23:00:00', '2026-10-15 00:00:00'],
            [7, '2026-10-15 23:59:59', '2026-10-16 01:00:00'],
            [8, '2026-10-16 00:00:00', '2026-10-16 09:00:00'],
        ];
        DB::table('events')->insert(array_map(fn ($event) => [
            'id' => $event[0], 'started_at' => $event[1], 'ended_at' => $event[2],
        ], $events));
        DB::table('events')->insert([
            'id' => 9, 'started_at' => '2026-10-15 09:00:00',
            'ended_at' => '2026-10-15 10:00:00', 'deleted_at' => '2026-10-15 12:00:00',
        ]);
    }

    protected function tearDown(): void
    {
        DB::purge('event_date_range_test');
        parent::tearDown();
    }

    public function test_a_single_day_includes_overnight_enclosing_and_boundary_events(): void
    {
        $this->assertSame([2, 3, 4, 5, 6, 7], $this->ids([
            'overlapping' => ['startedAt' => '2026-10-15', 'endedAt' => '2026-10-15'],
        ]));
    }

    public function test_adjacent_incremental_ranges_keep_events_crossing_their_boundary(): void
    {
        $firstDay = $this->ids([
            'overlapping' => ['startedAt' => '2026-10-15', 'endedAt' => '2026-10-15'],
        ]);
        $nextDay = $this->ids([
            'overlapping' => ['startedAt' => '2026-10-16', 'endedAt' => '2026-10-16'],
        ]);
        $combined = array_values(array_unique(array_merge($firstDay, $nextDay)));
        sort($combined);

        $this->assertSame([4, 5, 7, 8], $nextDay);
        $this->assertSame($this->ids([
            'overlapping' => ['startedAt' => '2026-10-15', 'endedAt' => '2026-10-16'],
        ]), $combined);
    }

    public function test_legacy_date_filters_keep_their_containment_semantics(): void
    {
        $this->assertSame([3], $this->ids([
            'startedAt' => '2026-10-15', 'endedAt' => '2026-10-15',
        ]));
    }

    public function test_overlap_combines_with_other_event_filters(): void
    {
        $this->assertSame([2, 4], $this->ids([
            'overlapping' => ['startedAt' => '2026-10-15', 'endedAt' => '2026-10-15'],
            'ids' => [1, 2, 4, 8],
        ]));
    }

    public function test_the_http_filter_wrapper_accepts_the_new_range_filter(): void
    {
        $this->app->instance('request', Request::create('/api/event', 'GET', [
            'filters' => json_encode([
                'overlapping' => ['startedAt' => '2026-10-15', 'endedAt' => '2026-10-15'],
            ]),
        ]));

        $this->assertSame([2, 3, 4, 5, 6, 7], Event::query()
            ->filter(new EventRequestFilters())->orderBy('id')->pluck('id')->all());
    }

    /** @dataProvider invalidRanges */
    public function test_invalid_ranges_are_rejected_before_querying($range): void
    {
        DB::connection()->enableQueryLog();
        try {
            $this->ids(['overlapping' => $range]);
            $this->fail('An invalid agenda range must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertNotEmpty($exception->errors());
            $this->assertSame([], DB::connection()->getQueryLog());
        }
    }

    public static function invalidRanges(): array
    {
        return [
            'null' => [null],
            'string' => ['2026-10-15'],
            'empty' => [[]],
            'missing start' => [['endedAt' => '2026-10-15']],
            'missing end' => [['startedAt' => '2026-10-15']],
            'invalid calendar date' => [['startedAt' => '2026-02-30', 'endedAt' => '2026-03-01']],
            'datetime start' => [['startedAt' => '2026-10-15T00:00:00', 'endedAt' => '2026-10-16']],
            'datetime end' => [['startedAt' => '2026-10-15', 'endedAt' => '2026-10-16 23:59:59']],
            'reversed range' => [['startedAt' => '2026-10-16', 'endedAt' => '2026-10-15']],
            'array start' => [['startedAt' => [], 'endedAt' => '2026-10-16']],
            'array end' => [['startedAt' => '2026-10-15', 'endedAt' => []]],
            'numeric date' => [['startedAt' => 20261015, 'endedAt' => '2026-10-16']],
            'non-padded date' => [['startedAt' => '2026-1-15', 'endedAt' => '2026-10-16']],
            'extra keys' => [['startedAt' => '2026-10-15', 'endedAt' => '2026-10-16', 'ignored' => true]],
        ];
    }

    protected function ids(array $values): array
    {
        $filters = new EventFilters();
        $filters->addFilters($values);

        return Event::query()->filter($filters)->orderBy('id')->pluck('id')->all();
    }
}
