<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\ImportRunTool;
use App\Agents\Tools\ImportStatusTool;
use App\Agents\Tools\ToolResult;
use App\Agents\Writes\InternalApi;
use App\Agents\Writes\InternalResponse;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

/**
 * Follow-up and launch of the imports of prospects for the AI agents.
 */
class ImportToolsTest extends TestCase
{
    protected AgentExecutionContext $context;
    protected array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'import_tools_test',
            'database.connections.import_tools_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);

        DB::connection()->getSchemaBuilder()->create('imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->string('name');
            $table->string('source')->default('file');
            $table->string('path')->nullable();
            $table->json('headers')->nullable();
            $table->json('mapping')->nullable();
            $table->json('duplicates_fields')->nullable();
            $table->json('selected_sheets')->nullable();
            $table->unsignedInteger('rows_count')->nullable();
            $table->unsignedInteger('cols_count')->nullable();
            $table->boolean('is_processing')->default(false);
            $table->boolean('sync_enabled')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        DB::connection()->getSchemaBuilder()->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('import_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        DB::table('prospects')->insert([
            ['project_id' => 1, 'import_id' => 1],
            ['project_id' => 1, 'import_id' => 1],
        ]);

        $headers = json_encode(['Prénom', 'Nom', 'Colonne libre', '']);
        $row = ['source' => 'file', 'headers' => $headers, 'duplicates_fields' => json_encode(['email']), 'rows_count' => 120, 'cols_count' => 4];
        $first = json_encode(['first_name']);
        $rows = [
            [1, 1, 'Salon avril', json_encode(['first_name', 'last_name', null, null]), false, null],
            [2, 1, 'Déjà fait', $first, false, now()],
            [3, 1, 'En cours', $first, true, null],
            [4, 1, 'Sans mapping', null, false, null],
            [5, 2, 'Autre projet', $first, false, null],
        ];
        DB::table('imports')->insert(array_map(fn ($r) => [
            'id' => $r[0], 'project_id' => $r[1], 'name' => $r[2], 'mapping' => $r[3], 'is_processing' => $r[4], 'processed_at' => $r[5],
        ] + $row, $rows));

        $project = new Project();
        $project->id = 1;
        $this->context = new AgentExecutionContext(new User(), $project);
    }

    protected function runTool(): ImportRunTool
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturnUsing(function ($context, $method, $path, $data) {
            $this->sent[] = [$method, $path, $data];

            return new InternalResponse(200, ['message' => 'ok'], '{}');
        });

        return new ImportRunTool($api);
    }

    public function test_the_list_gives_the_state_of_each_import_of_the_project_only(): void
    {
        $result = (new ImportStatusTool())->handle($this->context, ['view' => 'list']);

        $this->assertFalse($result->isError);
        $this->assertSame(4, $result->data['total']);
        $states = array_column($result->data['imports'], 'state', 'id');
        $this->assertSame(['to_launch', 'done', 'processing', 'to_launch'], array_values(array_reverse($states, true)));
        $this->assertStringNotContainsString('Autre projet', json_encode($result->data));
    }

    public function test_the_list_can_keep_one_state(): void
    {
        $result = (new ImportStatusTool())->handle($this->context, ['view' => 'list', 'state' => 'processing']);

        $this->assertSame([3], array_column($result->data['imports'], 'id'));
    }

    public function test_the_detail_tells_which_columns_are_not_mapped(): void
    {
        $result = (new ImportStatusTool())->handle($this->context, ['view' => 'detail', 'import_id' => 1]);

        $this->assertSame(['Prénom', 'Nom'], $result->data['columns_mapped']);
        $this->assertSame(['Colonne libre'], $result->data['columns_not_mapped']);
        $this->assertSame(['email'], $result->data['duplicates_fields']);
        $this->assertNull($result->data['processed_at']);
        $this->assertSame(2, $result->data['prospects_created']);
    }

    public function test_the_date_of_a_finished_import_is_given(): void
    {
        $result = (new ImportStatusTool())->handle($this->context, ['view' => 'detail', 'import_id' => 2]);

        $this->assertSame('done', $result->data['state']);
        $this->assertNotNull($result->data['processed_at']);
        $this->assertStringContainsString('T', $result->data['processed_at']);
    }

    public function test_an_import_of_another_project_or_unknown_is_not_found(): void
    {
        foreach ([5, 999, 0] as $id) {
            $result = (new ImportStatusTool())->handle($this->context, ['view' => 'detail', 'import_id' => $id]);

            $this->assertSame(ToolResult::NOT_FOUND, $result->code, (string) $id);
        }
    }

    public function test_the_tools_need_the_import_right_and_the_run_is_a_write(): void
    {
        $this->assertSame('prospectImport', (new ImportStatusTool())->ability());
        $this->assertSame(ImportStatusTool::READ, (new ImportStatusTool())->risk());
        $this->assertSame('prospectImport', $this->runTool()->ability());
        $this->assertSame(ImportRunTool::WRITE, $this->runTool()->risk());
    }

    public function test_a_mapped_import_is_launched_through_the_route_of_the_crm(): void
    {
        $result = $this->runTool()->handle($this->context, ['import_id' => 1]);

        $this->assertFalse($result->isError);
        $this->assertSame([['PUT', 'import/1', ['is_processing' => true]]], $this->sent);
    }

    public function test_an_import_in_progress_without_mapping_or_of_another_project_is_not_launched(): void
    {
        $expected = [3 => ToolResult::INVALID_INPUT, 4 => ToolResult::INVALID_INPUT, 5 => ToolResult::NOT_FOUND];

        foreach ($expected as $id => $code) {
            $result = $this->runTool()->handle($this->context, ['import_id' => $id]);

            $this->assertTrue($result->isError, (string) $id);
            $this->assertSame($code, $result->code, (string) $id);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_a_launch_that_will_be_refused_is_refused_before_the_human_is_asked(): void
    {
        $tool = $this->runTool();

        $this->assertNull($tool->precheck($this->context, ['import_id' => 1]));
        foreach ([3 => ToolResult::INVALID_INPUT, 4 => ToolResult::INVALID_INPUT, 5 => ToolResult::NOT_FOUND] as $id => $code) {
            $this->assertSame($code, $tool->precheck($this->context, ['import_id' => $id])->code, (string) $id);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_the_number_of_rows_drives_the_confirmation(): void
    {
        $this->assertSame(120, $this->runTool()->affectedCount($this->context, ['import_id' => 1]));
        $this->assertSame(0, $this->runTool()->affectedCount($this->context, ['import_id' => 5]));
    }
}
