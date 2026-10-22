<?php

namespace Tests\Feature;

use App\Console\Commands\UserTracker;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The aggregation uses MySQL functions (TIME_TO_SEC, SUBSTRING_INDEX), so this test
 * needs a scratch MySQL/MariaDB schema (DB_TEST_DATABASE, default "test"). It is
 * skipped when that schema is unreachable or already contains one of the tables.
 */
class UserTrackerCommandTest extends TestCase
{
    protected array $tables = ['tracker_stats', 'tracker_logs', 'tracker_route_paths', 'user_project', 'projects', 'users'];

    // Only the tables created by this test may be dropped by it
    protected bool $createdTables = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Opt-in: this test creates and drops tables, so it only runs when a scratch schema is named explicitly.
        if (!env('DB_TEST_DATABASE')) {
            $this->markTestSkipped('Set DB_TEST_DATABASE to a scratch MySQL schema to run this test.');
        }

        config(['database.connections.mysql.database' => env('DB_TEST_DATABASE')]);
        DB::purge('mysql');
        config(['database.default' => 'mysql']);

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('No scratch MySQL schema available: ' . $e->getMessage());
        }

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                $this->markTestSkipped("Table {$table} already exists in the scratch schema.");
            }
        }

        $this->createdTables = true;
        Schema::create('users', fn (Blueprint $t) => $t->id());
        Schema::create('projects', function (Blueprint $t) {
            $t->id();
            $t->string('slug');
            $t->timestamp('deleted_at')->nullable();
        });
        Schema::create('user_project', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('project_id');
        });
        Schema::create('tracker_route_paths', function (Blueprint $t) {
            $t->id();
            $t->string('path');
        });
        Schema::create('tracker_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('route_path_id')->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('tracker_stats', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedBigInteger('project_id')->nullable();
            $t->unsignedBigInteger('user_id');
            $t->date('date');
            $t->integer('hits');
            $t->text('hits_per_time');
            $t->timestamps();
        });
    }

    protected function tearDown(): void
    {
        if ($this->createdTables) {
            foreach ($this->tables as $table) {
                Schema::dropIfExists($table);
            }
        }

        parent::tearDown();
    }

    protected function seedLogs(): void
    {
        DB::table('users')->insert([['id' => 1], ['id' => 2]]);
        DB::table('projects')->insert([
            ['id' => 10, 'slug' => 'alpha', 'deleted_at' => null],
            ['id' => 11, 'slug' => 'alpha-two', 'deleted_at' => null],
            ['id' => 12, 'slug' => 'gone', 'deleted_at' => '2026-01-01 00:00:00'],
        ]);
        DB::table('user_project')->insert([
            ['user_id' => 1, 'project_id' => 10],
            ['user_id' => 1, 'project_id' => 12],
            ['user_id' => 2, 'project_id' => 11],
        ]);
        DB::table('tracker_route_paths')->insert([
            ['id' => 1, 'path' => 'api/project/alpha'],
            ['id' => 2, 'path' => 'api/project/alpha/prospect/5/label'],
            ['id' => 3, 'path' => 'api/project/alpha-two/menu'],
            ['id' => 4, 'path' => 'api/project/gone/menu'],
            ['id' => 5, 'path' => 'api/event'],
        ]);
        DB::table('tracker_logs')->insert([
            ['user_id' => 1, 'route_path_id' => 1, 'created_at' => '2026-10-20 00:00:00'], // slot 0
            ['user_id' => 1, 'route_path_id' => 2, 'created_at' => '2026-10-20 00:14:59'], // slot 0
            ['user_id' => 1, 'route_path_id' => 2, 'created_at' => '2026-10-20 10:15:00'], // slot 41
            ['user_id' => 1, 'route_path_id' => 3, 'created_at' => '2026-10-20 10:20:00'], // not a project of user 1
            ['user_id' => 1, 'route_path_id' => 4, 'created_at' => '2026-10-20 10:20:00'], // soft-deleted project
            ['user_id' => 1, 'route_path_id' => 5, 'created_at' => '2026-10-20 23:59:59'], // slot 95, no project
            ['user_id' => 1, 'route_path_id' => 5, 'created_at' => '2026-10-21 00:00:00'], // next day
            ['user_id' => 1, 'route_path_id' => null, 'created_at' => '2026-10-20 12:00:00'], // no route path: ignored
            ['user_id' => null, 'route_path_id' => 5, 'created_at' => '2026-10-20 12:00:00'], // anonymous: ignored
            ['user_id' => 2, 'route_path_id' => 3, 'created_at' => '2026-10-20 08:00:00'], // slot 32
        ]);
    }

    public function test_compute_day_matches_the_slot_and_project_rules(): void
    {
        $this->seedLogs();

        $rows = collect((new UserTracker())->computeDay(
            Carbon::parse('2026-10-20')->startOfDay(),
            Carbon::parse('2026-10-20')->endOfDay()
        ))->keyBy(fn ($row) => $row['user_id'] . ':' . ($row['project_id'] ?? 'null'));

        $this->assertEqualsCanonicalizing(['1:null', '1:10', '2:null', '2:11'], $rows->keys()->all());

        $this->assertSame(6, $rows['1:null']['hits']);
        $this->assertCount(96, $rows['1:null']['hits_per_time']);
        $this->assertSame(2, $rows['1:null']['hits_per_time'][0]);
        $this->assertSame(3, $rows['1:null']['hits_per_time'][41]);
        $this->assertSame(1, $rows['1:null']['hits_per_time'][95]);

        $this->assertSame(3, $rows['1:10']['hits']);
        $this->assertSame(1, $rows['2:11']['hits']);
        $this->assertSame(1, $rows['2:11']['hits_per_time'][32]);
    }

    public function test_command_inserts_then_updates_only_changed_rows(): void
    {
        $this->seedLogs();

        $this->artisan('app:user-tracker', ['--date' => '2026-10-20'])->assertSuccessful();
        $this->assertSame(4, DB::table('tracker_stats')->count());
        $this->assertSame(6, (int) DB::table('tracker_stats')->where('user_id', 1)->whereNull('project_id')->value('hits'));

        // Idempotent re-run, then a new log for the same day updates the existing row (no duplicate)
        $this->artisan('app:user-tracker', ['--date' => '2026-10-20'])->assertSuccessful();
        DB::table('tracker_logs')->insert(['user_id' => 1, 'route_path_id' => 5, 'created_at' => '2026-10-20 15:00:00']);
        $this->artisan('app:user-tracker', ['--date' => '2026-10-20'])->assertSuccessful();

        $this->assertSame(4, DB::table('tracker_stats')->count());
        $this->assertSame(7, (int) DB::table('tracker_stats')->where('user_id', 1)->whereNull('project_id')->value('hits'));
    }

    public function test_dry_run_writes_nothing(): void
    {
        $this->seedLogs();

        $this->artisan('app:user-tracker', ['--date' => '2026-10-20', '--dry-run' => true])->assertSuccessful();

        $this->assertSame(0, DB::table('tracker_stats')->count());
    }
}
