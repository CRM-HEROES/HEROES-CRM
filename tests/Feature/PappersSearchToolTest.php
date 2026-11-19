<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\PappersSearchTool;
use App\Agents\Tools\ToolResult;
use App\Agents\Writes\InternalApi;
use App\Agents\Writes\InternalResponse;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

/**
 * Pappers for the AI agents: a paid service that adds prospects. Nothing is spent unless the call is narrow and within budget.
 */
class PappersSearchToolTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected AgentExecutionContext $context;
    protected array $sent = [];
    protected int $adds = 2;
    protected int $status = 200;
    protected int $pid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->timestamps();
            $table->softDeletes();
        });

        // The actions are inserted directly, without the runs and agents they point to
        DB::statement('PRAGMA foreign_keys = OFF');

        putenv('PAPPERS_TOKEN=test-token');
        $_ENV['PAPPERS_TOKEN'] = $_SERVER['PAPPERS_TOKEN'] = 'test-token';

        $owner = new User();
        $owner->id = $this->makeUser('Alice');
        $this->pid = $this->makeProject('Alpha', $owner->id);
        $project = new Project();
        $project->id = $this->pid;
        $this->context = new AgentExecutionContext($owner, $project);
    }

    protected function tearDown(): void
    {
        putenv('PAPPERS_TOKEN');
        unset($_ENV['PAPPERS_TOKEN'], $_SERVER['PAPPERS_TOKEN']);

        parent::tearDown();
    }

    protected function tool(): PappersSearchTool
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturnUsing(function ($context, $method, $path, $data = []) {
            $this->sent[] = [$method, $path, $data];
            // What the command does: it inserts the prospects it found, and answers nothing
            for ($i = 0; $i < $this->adds && $this->status === 200; $i++) {
                DB::table('prospects')->insert(['project_id' => $this->pid, 'created_at' => now(), 'updated_at' => now()]);
            }

            return new InternalResponse($this->status, null, '');
        });

        return new PappersSearchTool($api);
    }

    protected function action(int $run, int $project, string $executedAt = 'now'): void
    {
        static $n = 0;
        DB::table('agent_actions')->insert([
            'run_id' => $run, 'agent_id' => 1, 'user_id' => 1, 'project_id' => $project, 'tool_key' => 'pappers_search',
            'tool_use_id' => 'tu' . (++$n), 'risk' => 'communication', 'payload' => '{}', 'status' => 'executed',
            'executed_at' => $executedAt === 'now' ? now() : $executedAt, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_a_call_without_a_place_spends_nothing(): void
    {
        $cases = [
            'aucun filtre' => [],
            'pas de lieu' => ['code_naf_proprietaire' => '6820A', 'prix_vente_min' => '100000'],
            'point sans distance' => ['latitude' => '48.85', 'longitude' => '2.35'],
            'valeurs vides' => ['code_postal' => '  '],
        ];

        foreach ($cases as $name => $filters) {
            $result = $this->tool()->handle($this->context, ['filters' => $filters]);

            $this->assertSame(ToolResult::VALIDATION_FAILED, $result->code, $name);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_a_place_by_postal_code_or_by_point_and_distance_is_accepted(): void
    {
        $this->assertFalse($this->tool()->handle($this->context, ['filters' => ['code_postal' => '69003']])->isError);
        $this->assertFalse($this->tool()->handle($this->context, ['filters' => ['latitude' => '45.76', 'longitude' => '4.84', 'distance' => '500']])->isError);
        $this->assertCount(2, $this->sent);
    }

    public function test_only_known_filters_are_sent_with_a_small_default_page(): void
    {
        $this->tool()->handle($this->context, ['filters' => ['code_postal' => '69003', 'inconnu' => 'x', 'code_naf_proprietaire' => '6820A', 'region' => '']]);

        $this->assertSame([['POST', 'api/pappers', ['code_postal' => '69003', 'code_naf_proprietaire' => '6820A', 'page' => 1, 'par_page' => 10]]], $this->sent);
    }

    public function test_the_page_size_is_capped(): void
    {
        $tooBig = $this->tool()->handle($this->context, ['filters' => ['code_postal' => '69003'], 'par_page' => 26]);
        $this->assertSame(ToolResult::LIMIT_EXCEEDED, $tooBig->code);
        $this->assertSame([], $this->sent);

        $this->tool()->handle($this->context, ['filters' => ['code_postal' => '69003'], 'par_page' => 25, 'page' => 3]);
        $this->assertSame(25, $this->sent[0][2]['par_page']);
        $this->assertSame(3, $this->sent[0][2]['page']);
    }

    public function test_the_agent_learns_only_how_many_prospects_were_added(): void
    {
        $this->adds = 3;
        DB::table('prospects')->insert(['project_id' => $this->pid, 'created_at' => now(), 'updated_at' => now()]);

        $result = $this->tool()->handle($this->context, ['filters' => ['departement' => '69']]);

        $this->assertFalse($result->isError);
        $this->assertSame(3, $result->data['prospects_added']);
        $this->assertSame(['prospects_added', 'page', 'per_page', 'note'], array_keys($result->data));
    }

    public function test_nothing_added_is_not_an_error_but_says_why_it_may_be(): void
    {
        $this->adds = 0;

        $result = $this->tool()->handle($this->context, ['filters' => ['departement' => '69']]);

        $this->assertFalse($result->isError);
        $this->assertSame(0, $result->data['prospects_added']);
        $this->assertStringContainsString('SIREN', $result->data['note']);
    }

    public function test_an_error_of_the_crm_is_given_back_and_a_missing_token_stops_before_the_call(): void
    {
        $this->status = 422;
        $this->assertTrue($this->tool()->handle($this->context, ['filters' => ['departement' => '69']])->isError);

        $this->sent = [];
        putenv('PAPPERS_TOKEN');
        unset($_ENV['PAPPERS_TOKEN'], $_SERVER['PAPPERS_TOKEN']);
        $result = $this->tool()->handle($this->context, ['filters' => ['departement' => '69']]);
        $this->assertSame(ToolResult::UNAVAILABLE, $result->code);
        $this->assertSame([], $this->sent);
    }

    public function test_the_daily_budget_of_the_project_is_respected(): void
    {
        config(['agents.pappers_daily_calls' => 2]);

        // This call is already recorded as executed when it runs: it counts itself
        $this->action(1, $this->pid);
        $this->action(2, $this->pid);
        $this->assertFalse($this->tool()->handle($this->context, ['filters' => ['departement' => '69']])->isError);

        $this->action(3, $this->pid);
        $this->assertSame(ToolResult::LIMIT_EXCEEDED, $this->tool()->handle($this->context, ['filters' => ['departement' => '69']])->code);

        // Another project and the day before do not count
        $this->action(4, 999);
        $this->action(5, $this->pid, now()->subDays(2)->toDateTimeString());
        $this->assertCount(1, $this->sent);
    }

    public function test_one_call_per_turn_of_the_agent(): void
    {
        $this->context->runId = 7;
        $this->action(7, $this->pid);
        $this->assertFalse($this->tool()->handle($this->context, ['filters' => ['departement' => '69']])->isError);

        $this->action(7, $this->pid);
        $this->assertSame(ToolResult::LIMIT_EXCEEDED, $this->tool()->handle($this->context, ['filters' => ['departement' => '69']])->code);
    }

    public function test_it_is_always_decided_by_a_human_and_needs_the_import_right(): void
    {
        $this->assertSame(PappersSearchTool::COMMUNICATION, $this->tool()->risk());
        $this->assertSame('prospectImport', $this->tool()->ability());
    }

    public function test_the_preview_shows_the_filters_the_page_and_what_is_left_of_the_budget(): void
    {
        config(['agents.pappers_daily_calls' => 5]);
        $this->action(1, $this->pid);

        $preview = $this->tool()->preview($this->context, ['filters' => ['code_postal' => '69003', 'inconnu' => 'x'], 'par_page' => 20]);

        $this->assertSame(['code_postal' => '69003'], $preview['filtres']);
        $this->assertSame(20, $preview['par_page']);
        $this->assertSame(4, $preview['appels_restants_aujourd_hui']);
    }

    public function test_the_filters_are_the_ones_the_route_of_the_crm_accepts(): void
    {
        $source = file_get_contents(base_path('app/Http/Controllers/API/Project/API/PappersController.php'));
        preg_match('/\$filters = \[(.*?)\];/s', $source, $block);
        preg_match_all('/"([a-z_]+)"/', $block[1], $names);

        $route = array_values(array_diff($names[1], ['bases', 'page', 'par_page']));

        $this->assertEqualsCanonicalizing($route, PappersSearchTool::FILTERS, 'Les filtres de pappers_search doivent suivre ceux de la route');
    }
}
