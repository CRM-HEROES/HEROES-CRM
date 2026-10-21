<?php

namespace Tests\Feature;

use App\Filters\EventFilters;
use App\Filters\EventRequestFilters;
use App\Models\Event;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EventUsersFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'event_users_filter_test',
            'database.connections.event_users_filter_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->id();
        });
        $schema->create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->softDeletes();
        });
        $schema->create('user_event', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
        });

        DB::table('users')->insert([['id' => 1], ['id' => 2], ['id' => 3]]);
        DB::table('events')->insert([
            ['id' => 1, 'user_id' => 1],
            ['id' => 2, 'user_id' => 2],
            ['id' => 3, 'user_id' => 2],
            ['id' => 4, 'user_id' => null],
            ['id' => 5, 'user_id' => null],
            ['id' => 6, 'user_id' => 3],
        ]);
        DB::table('user_event')->insert([
            ['event_id' => 2, 'user_id' => 1],
            ['event_id' => 3, 'user_id' => 3],
            ['event_id' => 4, 'user_id' => 1],
            ['event_id' => 6, 'user_id' => 2],
        ]);
    }

    protected function tearDown(): void
    {
        DB::purge('event_users_filter_test');
        parent::tearDown();
    }

    public function test_deselecting_all_users_returns_no_events(): void
    {
        $this->assertSame([], $this->ids(['withUsers' => []]));
    }

    public function test_selected_users_include_assigned_and_participant_events(): void
    {
        $this->assertSame([1, 2, 4], $this->ids(['withUsers' => [1]]));
        $this->assertSame([2, 3, 6], $this->ids(['withUsers' => [2]]));
        $this->assertSame([1, 2, 3, 4, 6], $this->ids(['withUsers' => [1, 2]]));
    }

    public function test_no_user_filter_keeps_unassigned_events(): void
    {
        $this->assertSame([1, 2, 3, 4, 5, 6], $this->ids([]));
    }

    public function test_selected_participants_cannot_escape_other_filters(): void
    {
        $this->assertSame([1], $this->ids(['ids' => [1, 3], 'withUsers' => [1]]));
        $this->assertSame([], $this->ids(['ids' => [1, 3], 'withUsers' => []]));
    }

    public function test_unknown_users_return_no_events(): void
    {
        $this->assertSame([], $this->ids(['withUsers' => [999]]));
    }

    public function test_the_http_wrapper_preserves_an_explicit_empty_selection(): void
    {
        $this->app->instance('request', Request::create('/api/event', 'GET', [
            'filters' => json_encode(['withUsers' => []]),
        ]));

        $this->assertSame([], Event::query()->filter(new EventRequestFilters())
            ->orderBy('id')->pluck('id')->all());
    }

    protected function ids(array $values): array
    {
        $filters = new EventFilters();
        $filters->addFilters($values);

        return Event::query()->filter($filters)->orderBy('id')->pluck('id')->all();
    }
}
