<?php

namespace Tests\Feature;

use App\Filters\EventFilters;
use App\Models\Event;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EventFiltersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'event_filters_test',
            'database.connections.event_filters_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('calendars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->softDeletes();
        });
        $schema->create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->softDeletes();
        });
        $schema->create('user_event', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('event_id');
        });

        DB::table('calendars')->insert([
            ['id' => 1, 'project_id' => 1, 'deleted_at' => null],
            ['id' => 2, 'project_id' => 2, 'deleted_at' => null],
            ['id' => 3, 'project_id' => 2, 'deleted_at' => '2026-01-01 00:00:00'],
        ]);
        DB::table('events')->insert([
            ['id' => 1, 'calendar_id' => 1, 'user_id' => 10],
            ['id' => 2, 'calendar_id' => 1, 'user_id' => null],
            ['id' => 3, 'calendar_id' => 2, 'user_id' => 11],
            ['id' => 4, 'calendar_id' => 2, 'user_id' => null],
            ['id' => 5, 'calendar_id' => 3, 'user_id' => 10],
        ]);
        DB::table('user_event')->insert([
            ['user_id' => 12, 'event_id' => 2], // attendee of an event without affected user
            ['user_id' => 12, 'event_id' => 3],
        ]);
    }

    protected function ids(string $filter, $value): array
    {
        $query = Event::query();
        (new EventFilters())->$filter($query, $value);

        return $query->orderBy('id')->pluck('id')->all();
    }

    public function test_with_users_matches_affected_user_or_attendee(): void
    {
        $this->assertSame([1, 5], $this->ids('withUsers', [10]));
        $this->assertSame([2, 3], $this->ids('withUsers', [12]));
        $this->assertSame([1, 2, 3, 5], $this->ids('withUsers', [10, 12]));
        // The reminder sends {"ids": [..]}
        $this->assertSame([1, 5], $this->ids('withUsers', ['ids' => [10]]));
        // An empty selection means nobody is selected: no event (same contract as the agenda front)
        $this->assertSame([], $this->ids('withUsers', []));
    }

    public function test_without_users_matches_events_whose_affected_user_is_not_listed(): void
    {
        $this->assertSame([2, 3, 4], $this->ids('withoutUsers', [10]));
        $this->assertSame([2, 4], $this->ids('withoutUsers', []));
    }

    public function test_projects_filters_ignore_soft_deleted_calendars(): void
    {
        $this->assertSame([1, 2], $this->ids('withProjects', [1]));
        $this->assertSame([3, 4], $this->ids('withProjects', [2]));
        $this->assertSame([3, 4, 5], $this->ids('withoutProjects', [1]));
        $this->assertSame([1, 2, 5], $this->ids('withoutProjects', [2]));
    }
}
