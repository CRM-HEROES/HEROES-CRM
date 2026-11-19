<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\ToolResult;
use App\Agents\Tools\WorkflowCancelExecutionTool;
use App\Agents\Writes\InternalApi;
use App\Agents\Writes\InternalResponse;
use App\Models\Project;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\WorkflowVersion;
use Mockery;
use Tests\TestCase;

/**
 * Cancelling an execution of an automation that waits, for the AI agents.
 */
class WorkflowCancelExecutionToolTest extends TestCase
{
    protected AgentExecutionContext $context;
    protected array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'workflow_cancel_tool_test',
            'database.connections.workflow_cancel_tool_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);

        foreach ([
            '2026_10_30_000001_create_workflow_tables',
            '2026_10_30_000002_add_workflow_filter_memberships',
            '2026_10_30_000003_add_waiting_event_to_workflow_executions',
            '2026_10_30_000004_add_draft_graph_to_workflows',
        ] as $migration) {
            (include base_path("database/migrations/$migration.php"))->up();
        }

        $project = new Project();
        $project->id = 1;
        $this->context = new AgentExecutionContext(new User(), $project);
    }

    protected function execution(string $status, int $projectId = 1): WorkflowExecution
    {
        static $n = 0;
        $workflow = Workflow::create(['project_id' => $projectId, 'name' => 'Relance', 'status' => 'active']);
        $version = WorkflowVersion::create([
            'workflow_id' => $workflow->id, 'version' => 1, 'trigger_type' => 'prospect.created', 'baselined_at' => now(),
            'graph' => ['nodes' => [], 'edges' => []],
        ]);

        return WorkflowExecution::create([
            'project_id' => $projectId, 'workflow_id' => $workflow->id, 'workflow_version_id' => $version->id,
            'subject_type' => 'prospect', 'subject_id' => 1, 'status' => $status, 'trigger_key' => 'k' . (++$n),
        ]);
    }

    protected function tool(int $status = 200): WorkflowCancelExecutionTool
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturnUsing(function ($context, $method, $path) use ($status) {
            $this->sent[] = [$method, $path];

            return new InternalResponse($status, $status === 200 ? ['status' => 'cancelled'] : ['message' => 'Cette exécution ne peut plus être annulée'], '{}');
        });

        return new WorkflowCancelExecutionTool($api);
    }

    public function test_a_waiting_or_pending_execution_is_cancelled_through_the_route(): void
    {
        foreach ([WorkflowExecution::WAITING, WorkflowExecution::PENDING] as $status) {
            $this->sent = [];
            $execution = $this->execution($status);

            $result = $this->tool()->handle($this->context, ['execution_id' => $execution->id]);

            $this->assertFalse($result->isError, $status);
            $this->assertSame([['POST', "workflow-execution/{$execution->id}/cancel"]], $this->sent);
            $this->assertSame('cancelled', $result->data['status']);
        }
    }

    public function test_an_execution_that_is_running_or_over_is_not_cancelled(): void
    {
        foreach (['running', 'completed', 'failed', 'cancelled'] as $status) {
            $execution = $this->execution($status);

            $result = $this->tool()->handle($this->context, ['execution_id' => $execution->id]);

            $this->assertSame(ToolResult::INVALID_INPUT, $result->code, $status);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_an_execution_of_another_project_or_unknown_is_not_found(): void
    {
        $other = $this->execution(WorkflowExecution::WAITING, 2);

        foreach ([$other->id, 999, 0] as $id) {
            $this->assertSame(ToolResult::NOT_FOUND, $this->tool()->handle($this->context, ['execution_id' => $id])->code, (string) $id);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_a_refusal_of_the_crm_is_given_back(): void
    {
        // A worker took it between the check and the call
        $execution = $this->execution(WorkflowExecution::WAITING);

        $result = $this->tool(422)->handle($this->context, ['execution_id' => $execution->id]);

        $this->assertTrue($result->isError);
        $this->assertSame(ToolResult::VALIDATION_FAILED, $result->code);
    }

    public function test_the_right_is_the_one_that_executes_workflows_and_the_preview_shows_the_state(): void
    {
        $execution = $this->execution(WorkflowExecution::WAITING);

        $this->assertSame('projectWorkflowExecute', $this->tool()->ability());
        $this->assertSame(WorkflowCancelExecutionTool::WRITE, $this->tool()->risk());
        $this->assertSame('waiting', $this->tool()->preview($this->context, ['execution_id' => $execution->id])['status']);
    }
}
