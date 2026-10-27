<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Services\CloudTalkCallAttribution;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CloudTalkCallAttributionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep attribution tests isolated from the configured application database.
        config([
            'database.default' => 'cloudtalk_attribution_test',
            'database.connections.cloudtalk_attribution_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        Http::preventStrayRequests();

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
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
        $schema->create('lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->string('operator');
            $table->json('config');
        });
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->softDeletes();
        });
        $schema->create('interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->string('source');
            $table->string('status');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        DB::table('users')->insert([['id' => 1], ['id' => 2], ['id' => 3]]);
        DB::table('projects')->insert([
            ['id' => 1, 'slug' => 'attribution-test'],
            ['id' => 2, 'slug' => 'other-project'],
        ]);
        DB::table('user_project')->insert([
            ['project_id' => 1, 'user_id' => 1],
            ['project_id' => 1, 'user_id' => 2],
            ['project_id' => 2, 'user_id' => 3],
        ]);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1],
            ['id' => 2, 'project_id' => 2],
        ]);
    }

    protected function tearDown(): void
    {
        DB::purge('cloudtalk_attribution_test');
        parent::tearDown();
    }

    public function test_agent_mapping_normalizes_ids_and_only_uses_cloudtalk_lines_for_project_members(): void
    {
        $this->line(1, 1, 42);
        $this->line(2, 3, '42');
        $this->line(1, 3, '77'); // This user belongs only to the other project.
        $this->line(1, 1, '88', 'twilio');

        $service = $this->service();

        $this->assertSame(1, $service->userId('42'));
        $this->assertNull($service->userId('77'));
        $this->assertNull($service->userId('88'));
        $this->assertNull($service->userId('999'));
        $this->assertNull($service->userId(null));
        $this->assertNull($service->userId(''));
    }

    public function test_an_agent_mapped_to_multiple_users_is_not_attributed_arbitrarily(): void
    {
        $this->line(1, 1, '42');
        $this->line(1, 2, 42);
        $id = $this->interaction('42');

        $service = $this->service();
        $this->assertNull($service->userId('42'));

        $result = $service->backfill();

        $this->assertSame(0, $result['matched']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(1, $result['unmatched']['42']);
        $this->assertNull(DB::table('interactions')->where('id', $id)->value('creator_id'));
    }

    public function test_backfill_preserves_existing_creators_and_all_other_call_attributes(): void
    {
        $this->line(1, 1, '42');
        $matched = $this->interaction('42');
        $existing = $this->interaction('42', ['creator_id' => 2]);
        $otherProject = $this->interaction('42', ['prospect_id' => 2]);
        $otherSource = $this->interaction('42', ['source' => 'twilio']);
        $unmapped = $this->interaction('77');
        $missing = $this->interaction(null);
        $before = (array) DB::table('interactions')->where('id', $matched)->first();

        $result = $this->service()->backfill();

        $this->assertSame(1, $result['matched']);
        $this->assertSame(1, $result['updated']);
        $this->assertSame(1, $result['unmatched']['77']);
        $this->assertSame(1, $result['unmatched']['inconnu']);
        $this->assertCount(2, $result['unmatched']);
        $before['creator_id'] = 1;
        $this->assertSame($before, (array) DB::table('interactions')->where('id', $matched)->first());
        $this->assertSame(2, DB::table('interactions')->where('id', $existing)->value('creator_id'));
        foreach ([$otherProject, $otherSource, $unmapped, $missing] as $id) {
            $this->assertNull(DB::table('interactions')->where('id', $id)->value('creator_id'));
        }
        Http::assertNothingSent();
    }

    public function test_dry_run_does_not_write_and_applied_backfill_is_idempotent(): void
    {
        $this->line(1, 1, '42');
        $id = $this->interaction('42');
        $before = (array) DB::table('interactions')->where('id', $id)->first();

        $preview = $this->service()->backfill(true);

        $this->assertSame(1, $preview['matched']);
        $this->assertSame(0, $preview['updated']);
        $this->assertSame($before, (array) DB::table('interactions')->where('id', $id)->first());

        $this->assertSame(1, $this->service()->backfill()['updated']);
        $again = $this->service()->backfill();
        $this->assertSame(0, $again['matched']);
        $this->assertSame(0, $again['updated']);
        $this->assertSame([], $again['unmatched']);
    }

    public function test_backfill_recovers_agents_from_normalized_and_raw_call_history(): void
    {
        $this->line(1, 1, '42');
        $normalized = $this->interaction(null, [
            'data' => json_encode(['cloudtalk_call' => ['agent' => ['id' => 42]]]),
        ]);
        $raw = $this->interaction(null, [
            'data' => json_encode(['cloudtalk_history' => ['Agent' => ['id' => '42']]]),
        ]);

        $result = $this->service()->backfill();

        $this->assertSame(2, $result['updated']);
        $this->assertSame([], $result['unmatched']);
        foreach ([$normalized, $raw] as $id) {
            $this->assertSame(1, DB::table('interactions')->where('id', $id)->value('creator_id'));
        }
    }

    public function test_completed_event_agent_takes_precedence_over_older_history_metadata(): void
    {
        $this->line(1, 1, '42');
        $this->line(1, 2, '77');
        $id = $this->interaction('42', [
            'data' => json_encode([
                'cloudtalk_ended' => ['data' => ['user' => ['id' => '42']]],
                'cloudtalk_call' => ['agent' => ['id' => '77']],
                'cloudtalk_history' => ['Agent' => ['id' => '77']],
            ]),
        ]);

        $result = $this->service()->backfill();

        $this->assertSame(1, $result['updated']);
        $this->assertSame(1, DB::table('interactions')->where('id', $id)->value('creator_id'));
    }

    public function test_backfill_processes_more_than_one_chunk_without_skipping_rows(): void
    {
        $this->line(1, 1, '42');
        $ids = [];
        for ($i = 0; $i < 503; $i++) {
            $ids[] = $this->interaction('42');
        }

        $result = $this->service()->backfill();

        $this->assertSame(503, $result['matched']);
        $this->assertSame(503, $result['updated']);
        $this->assertSame(503, DB::table('interactions')->whereIn('id', $ids)->where('creator_id', 1)->count());
    }

    public function test_artisan_command_supports_preview_and_application_and_rejects_unknown_project(): void
    {
        $this->line(1, 1, '42');
        $id = $this->interaction('42');

        $this->assertSame(0, Artisan::call('cloudtalk:attribute-calls', [
            'project' => 'attribution-test', '--dry-run' => true,
        ]));
        $this->assertNull(DB::table('interactions')->where('id', $id)->value('creator_id'));
        $this->assertNotSame('', trim(Artisan::output()));

        $this->assertSame(0, Artisan::call('cloudtalk:attribute-calls', ['project' => 'attribution-test']));
        $this->assertSame(1, DB::table('interactions')->where('id', $id)->value('creator_id'));

        $this->assertSame(1, Artisan::call('cloudtalk:attribute-calls', ['project' => 'missing-project']));
    }

    protected function service(): CloudTalkCallAttribution
    {
        return new CloudTalkCallAttribution(Project::findOrFail(1));
    }

    protected function line(int $projectId, int $userId, $agentId, string $operator = 'cloudtalk'): void
    {
        DB::table('lines')->insert([
            'project_id' => $projectId,
            'user_id' => $userId,
            'operator' => $operator,
            'config' => json_encode(['agent_id' => $agentId]),
        ]);
    }

    protected function interaction(?string $agentId, array $overrides = []): int
    {
        $data = ['call_id' => 'fixture-call', 'cloudtalk_ended' => ['data' => ['duration' => 43]]];
        if ($agentId !== null) {
            $data['cloudtalk_ended']['data']['user']['id'] = $agentId;
        }

        return DB::table('interactions')->insertGetId(array_merge([
            'prospect_id' => 1,
            'creator_id' => null,
            'source' => 'cloudtalk',
            'status' => 'answered',
            'started_at' => '2026-10-20 08:04:18',
            'ended_at' => '2026-10-20 08:05:01',
            'data' => json_encode($data),
            'created_at' => '2026-10-20 08:05:08',
            'updated_at' => '2026-10-20 08:05:08',
        ], $overrides));
    }
}
