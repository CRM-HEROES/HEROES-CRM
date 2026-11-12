<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Runs a test on a throw-away SQLite database in memory.
 *
 * phpunit.xml does not force a test database, so without this a test would run
 * on the database of the .env. The guard below stops the test if the
 * connection is not the in-memory one, before anything is written.
 *
 * Only what the agents need is created: users, projects and their link, the
 * permission tables and the agent tables. Rows are inserted with the query
 * builder, which does not fire the observers of the CRM (creating a project
 * would provision roles, calendars, labels...).
 */
trait UsesInMemoryDatabase
{
    protected function setUpInMemoryDatabase(): void
    {
        // Opt-in, on purpose: AGENTS_TEST_DB=mysql runs the same tests on the
        // MySQL database of the .env, inside a transaction that is rolled back
        if (env('AGENTS_TEST_DB') === 'mysql') {
            $this->setUpOnRealDatabase();

            return;
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        $this->assertSame('sqlite', DB::connection()->getDriverName(), 'The test must not run on the real database.');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName(), 'The test must not run on the real database.');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->default('x');
            $table->string('role')->nullable();
            $table->text('claude_api_key')->nullable();
            $table->unsignedBigInteger('last_project_id')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->unsignedInteger('failed_login_attempts')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('user_project', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
            $table->unique(['user_id', 'project_id']);
        });

        require_once database_path('migrations/2023_08_14_123427_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        (require database_path('migrations/2026_11_02_000001_create_agent_tables.php'))->up();
    }

    /**
     * The real MySQL database, with these protections:
     *  - everything the test writes is in ONE transaction, rolled back at the
     *    end (no table is created or altered: a DDL statement would commit it);
     *  - the tests assert on whole tables, so they stop (skipped) when a table
     *    of the agents already holds rows: they never run next to real agents;
     *  - the rows are inserted with the query builder, which fires no observer
     *    (no Google, Pipedrive or workflow side effect), and the queue is
     *    faked or not used by these tests.
     */
    protected function setUpOnRealDatabase(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());

        foreach (['agents', 'agent_versions', 'agent_conversations', 'agent_messages', 'agent_runs', 'agent_run_steps', 'agent_actions', 'agent_memories', 'agent_reports', 'agent_schedules', 'agent_usage_daily'] as $table) {
            // AGENTS_TEST_ALLOW_DATA=1: for the tests that never count agent rows (they work on their own rows, rolled back)
            if (!env('AGENTS_TEST_ALLOW_DATA') && DB::table($table)->exists()) {
                $this->markTestSkipped("La table $table contient des données : ce test ne tourne que sur des tables d'agents vides.");
            }
        }

        DB::beginTransaction();

        // Before the application is destroyed: nothing the test wrote stays
        $this->beforeApplicationDestroyed(function () {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        });
    }

    /**
     * @return int the id of the user
     */
    protected function makeUser(string $name = 'Alice', ?string $apiKey = 'sk-ant-test', ?string $role = null): int
    {
        return DB::table('users')->insertGetId([
            'name' => $name,
            'email' => strtolower($name) . uniqid() . '@example.test',
            'password' => 'x',
            'role' => $role,
            // Stored like the cast "encrypted" of the model does
            'claude_api_key' => $apiKey ? Crypt::encryptString($apiKey) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @return int the id of the project
     */
    protected function makeProject(string $name = 'Alpha', ?int $memberId = null): int
    {
        $id = DB::table('projects')->insertGetId([
            'name' => $name,
            'slug' => strtolower($name) . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($memberId) {
            DB::table('user_project')->insert(['user_id' => $memberId, 'project_id' => $id]);
        }

        return $id;
    }
}
