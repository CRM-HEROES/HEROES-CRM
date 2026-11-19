<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Agents\Tools\ToolResult;
use App\Agents\Tools\WorkflowExecutionsTool;
use App\Models\Project;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\WorkflowNodeExecution;
use App\Models\WorkflowVersion;
use App\Workflows\WorkflowMonitor;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

/**
 * The follow-up of the automations for the AI agents: the monitor of the "Suivi" tab, bounded to the project of the run.
 */
class WorkflowExecutionsToolTest extends TestCase
{
    protected WorkflowExecutionsTool $tool;
    protected AgentExecutionContext $context;
    protected bool $prospectVisible = true;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'workflow_executions_tool_test',
            'database.connections.workflow_executions_tool_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);

        foreach ([
            '2026_10_30_000001_create_workflow_tables',
            '2026_10_30_000002_add_workflow_filter_memberships',
            '2026_10_30_000003_add_waiting_event_to_workflow_executions',
            '2026_10_30_000004_add_draft_graph_to_workflows',
        ] as $migration) {
            (include base_path("database/migrations/$migration.php"))->up();
        }

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        $schema->create('project_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->string('key');
            $table->text('value')->nullable();
        });
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'first_name' => 'Marie', 'last_name' => 'Curie', 'company_name' => 'Radium'],
            ['id' => 3, 'project_id' => 2, 'first_name' => 'Autre', 'last_name' => 'Projet', 'company_name' => null],
        ]);

        // Which prospects the user may see is the business of the gateway: here the answer is the test's choice
        $reader = Mockery::mock(ReadGateway::class);
        $reader->shouldReceive('visibleProspectIds')->andReturnUsing(
            fn ($context, array $ids) => ['ids' => $this->prospectVisible ? $ids : []]
        );
        $this->tool = new WorkflowExecutionsTool(app(WorkflowMonitor::class), $reader);

        $project = new Project();
        $project->id = 1;
        $this->context = new AgentExecutionContext(new User(), $project);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    protected function workflow(string $name, int $projectId = 1): Workflow
    {
        $workflow = Workflow::create(['project_id' => $projectId, 'name' => $name, 'status' => 'active']);
        $version = WorkflowVersion::create([
            'workflow_id' => $workflow->id, 'version' => 1, 'trigger_type' => 'prospect.created', 'baselined_at' => now(),
            'graph' => [
                'nodes' => [
                    ['id' => 't', 'type' => 'trigger', 'name' => 'Nouveau lead', 'config' => ['trigger' => 'prospect.created']],
                    ['id' => 'a', 'type' => 'action', 'name' => 'Envoyer le SMS', 'config' => ['action' => 'send_sms', 'provider' => 'sms:smsbox']],
                ],
                'edges' => [['from' => 't', 'to' => 'a']],
            ],
        ]);
        $workflow->update(['active_version_id' => $version->id]);

        return $workflow->fresh();
    }

    protected function execution(Workflow $workflow, array $attributes = []): WorkflowExecution
    {
        static $n = 0;

        return WorkflowExecution::create($attributes + [
            'project_id' => $workflow->project_id,
            'workflow_id' => $workflow->id,
            'workflow_version_id' => $workflow->active_version_id,
            'subject_type' => 'prospect',
            'subject_id' => 1,
            'status' => 'completed',
            'trigger_key' => 'k' . (++$n),
            'started_at' => now()->subMinutes(10),
            'finished_at' => now()->subMinutes(9),
        ]);
    }

    protected function step(WorkflowExecution $execution, string $node, string $status, array $extra = []): void
    {
        WorkflowNodeExecution::create($extra + [
            'execution_id' => $execution->id, 'node_id' => $node, 'node_type' => 'action', 'status' => $status,
            'started_at' => now()->subMinutes(10), 'finished_at' => now()->subMinutes(9),
        ]);
    }

    public function test_the_tool_reads_only_and_needs_the_right_of_the_follow_up_tab(): void
    {
        $this->assertSame('workflow_executions', $this->tool->key());
        $this->assertSame(WorkflowExecutionsTool::READ, $this->tool->risk());
        $this->assertSame('projectWorkflowExecutionView', $this->tool->ability());
        $this->assertSame(['view'], $this->tool->inputSchema()['required']);
        $this->assertSame(WorkflowExecutionsTool::VIEWS, $this->tool->inputSchema()['properties']['view']['enum']);
    }

    public function test_the_list_gives_the_latest_executions_and_can_keep_only_the_failures(): void
    {
        $workflow = $this->workflow('Relance');
        $ok = $this->execution($workflow);
        $failed = $this->execution($workflow, ['status' => 'failed', 'error' => '<b>Le prospect n\'a pas de mobile</b>']);

        $all = $this->tool->handle($this->context, ['view' => 'list']);
        $this->assertFalse($all->isError);
        $this->assertSame([$failed->id, $ok->id], array_column($all->data['executions'], 'id'));
        $this->assertSame('Relance', $all->data['executions'][0]['workflow']['name']);
        $this->assertSame('Marie Curie', $all->data['executions'][0]['subject']['name']);
        // The error is plain text
        $this->assertSame("Le prospect n'a pas de mobile", $all->data['executions'][0]['error']);

        $onlyFailed = $this->tool->handle($this->context, ['view' => 'list', 'status' => 'failed']);
        $this->assertSame([$failed->id], array_column($onlyFailed->data['executions'], 'id'));
    }

    public function test_the_list_never_shows_the_executions_of_another_project(): void
    {
        $mine = $this->execution($this->workflow('Mon projet'));
        $this->execution($this->workflow('Autre projet', 2), ['subject_id' => 3]);

        $list = $this->tool->handle($this->context, ['view' => 'list']);

        $this->assertSame([$mine->id], array_column($list->data['executions'], 'id'));
        $this->assertStringNotContainsString('Autre', json_encode($list->data));
    }

    public function test_a_detail_gives_the_steps_with_their_error_and_keeps_the_result_technical(): void
    {
        $workflow = $this->workflow('Relance');
        $execution = $this->execution($workflow, ['status' => 'failed', 'error' => 'Step failed']);
        $this->step($execution, 'a', 'failed', [
            'error' => str_repeat('Opérateur indisponible. ', 40),
            'result' => ['provider' => 'sms:smsbox', 'sms_id' => 12, 'to' => '+33612345678', 'message' => ['body' => 'texte long']],
        ]);

        $result = $this->tool->handle($this->context, ['view' => 'detail', 'execution_id' => $execution->id]);

        $this->assertFalse($result->isError);
        $step = $result->data['steps'][0];
        $this->assertSame('Envoyer le SMS', $step['name']);
        $this->assertSame('send_sms', $step['action']);
        $this->assertSame('failed', $step['status']);
        $this->assertLessThanOrEqual(300, mb_strlen($step['error']));
        // A phone number or a text never comes out of the result of a step
        $this->assertSame(['provider' => 'sms:smsbox', 'sms_id' => 12], $step['result']);
        $this->assertStringNotContainsString('33612345678', json_encode($result->data));
    }

    public function test_an_execution_of_another_project_or_unknown_is_not_found(): void
    {
        $other = $this->execution($this->workflow('Autre projet', 2), ['subject_id' => 3]);

        foreach ([$other->id, 9999, 0] as $id) {
            $result = $this->tool->handle($this->context, ['view' => 'detail', 'execution_id' => $id]);

            $this->assertTrue($result->isError, (string) $id);
            $this->assertSame(ToolResult::NOT_FOUND, $result->code);
        }
    }

    public function test_the_overview_counts_the_executions_and_cuts_the_error_messages(): void
    {
        $workflow = $this->workflow('Relance');
        $this->execution($workflow);
        $failed = $this->execution($workflow, ['status' => 'failed', 'error' => 'x']);
        $this->step($failed, 'a', 'failed', ['error' => str_repeat('Erreur longue ', 60)]);

        $result = $this->tool->handle($this->context, ['view' => 'overview', 'days' => 7]);

        $this->assertFalse($result->isError);
        $this->assertSame(7, $result->data['days']);
        $row = collect($result->data['workflows'])->firstWhere('name', 'Relance');
        $this->assertSame(1, $row['period']['completed']);
        $this->assertSame(1, $row['period']['failed']);
        foreach ($result->data['errors'] as $error) {
            $this->assertLessThanOrEqual(300, mb_strlen($error['message']));
        }
    }

    public function test_the_history_of_a_prospect_needs_a_prospect_the_user_may_see(): void
    {
        $workflow = $this->workflow('Relance');
        $execution = $this->execution($workflow);
        $this->step($execution, 'a', 'completed');

        $seen = $this->tool->handle($this->context, ['view' => 'prospect', 'prospect_id' => 1]);
        $this->assertFalse($seen->isError);
        $this->assertSame([$execution->id], array_column($seen->data['executions'], 'id'));
        $this->assertSame('completed', $seen->data['executions'][0]['steps'][0]['status']);

        $this->prospectVisible = false;
        $hidden = $this->tool->handle($this->context, ['view' => 'prospect', 'prospect_id' => 1]);
        $this->assertTrue($hidden->isError);
        $this->assertSame(ToolResult::NOT_FOUND, $hidden->code);

        // Nor through the list
        $hiddenList = $this->tool->handle($this->context, ['view' => 'list', 'prospect_id' => 1]);
        $this->assertSame(ToolResult::NOT_FOUND, $hiddenList->code);
    }
}
