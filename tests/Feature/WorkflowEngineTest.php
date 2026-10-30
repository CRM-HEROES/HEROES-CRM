<?php

namespace Tests\Feature;

use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\WorkflowNodeExecution;
use App\Workflows\Actions\ActionRegistry;
use App\Workflows\WorkflowEngine;
use App\Workflows\WorkflowExecutionManager;
use App\Workflows\WorkflowService;
use App\Workflows\WorkflowTriggerResolver;
use App\Workflows\WorkflowValidationException;
use App\Workflows\WorkflowValidator;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\Support\RecordingHandler;
use Tests\TestCase;

class WorkflowEngineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'workflow_engine_test',
            'database.connections.workflow_engine_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            // Jobs run inline: dispatching an execution runs it
            'queue.default' => 'sync',
        ]);

        (include base_path('database/migrations/2026_10_30_000001_create_workflow_tables.php'))->up();
        (include base_path('database/migrations/2026_10_30_000002_add_workflow_filter_memberships.php'))->up();
        (include base_path('database/migrations/2026_10_30_000003_add_waiting_event_to_workflow_executions.php'))->up();

        DB::connection()->getSchemaBuilder()->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'processed_at' => null],
            ['id' => 2, 'project_id' => 1, 'processed_at' => '2026-10-01 10:00:00'],
            ['id' => 3, 'project_id' => 2, 'processed_at' => null], // another project
        ]);

        RecordingHandler::$calls = [];

        $registry = new ActionRegistry();
        $registry->register('mark-a', new RecordingHandler('a'));
        $registry->register('mark-b', new RecordingHandler('b'));
        $registry->register('mark-yes', new RecordingHandler('yes'));
        $registry->register('mark-no', new RecordingHandler('no'));
        $registry->register('boom', new RecordingHandler('boom', true));
        $this->app->instance(ActionRegistry::class, $registry);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // Helpers

    protected function node(string $id, string $type, array $config = []): array
    {
        return ['id' => $id, 'type' => $type, 'name' => $id, 'config' => $config];
    }

    protected function edge(string $from, string $to, ?string $branch = null): array
    {
        return array_filter(['from' => $from, 'to' => $to, 'branch' => $branch]);
    }

    protected function trigger(): array
    {
        return $this->node('t', 'trigger', ['trigger' => 'prospect.created']);
    }

    protected function action(string $id, string $action): array
    {
        return $this->node($id, 'action', ['action' => $action]);
    }

    /**
     * trigger -> action -> end
     */
    protected function linearGraph(string $action = 'mark-a'): array
    {
        return [
            'nodes' => [$this->trigger(), $this->action('a', $action), $this->node('e', 'end')],
            'edges' => [$this->edge('t', 'a'), $this->edge('a', 'e')],
        ];
    }

    protected function activeWorkflow(array $graph, int $projectId = 1): Workflow
    {
        $workflow = Workflow::create(['project_id' => $projectId, 'name' => 'Test']);
        $service = app(WorkflowService::class);
        $service->publish($workflow, $graph);
        $service->activate($workflow);

        return $workflow->fresh();
    }

    protected function fire(int $prospectId = 1, ?string $key = null, int $projectId = 1): int
    {
        return app(WorkflowTriggerResolver::class)
            ->fire('prospect.created', $projectId, $prospectId, $key ?? "prospect.created:$prospectId");
    }

    protected function execution(int $id = 1): WorkflowExecution
    {
        return WorkflowExecution::findOrFail($id);
    }

    protected function nodes(int $executionId = 1): array
    {
        return WorkflowNodeExecution::where('execution_id', $executionId)
            ->orderBy('id')
            ->pluck('status', 'node_id')
            ->all();
    }

    // Execution

    public function test_linear_workflow_runs_to_completion_and_is_logged(): void
    {
        $this->activeWorkflow($this->linearGraph());

        $this->assertSame(1, $this->fire());

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $this->assertNotNull($this->execution()->finished_at);
        $this->assertSame(['a:1'], RecordingHandler::$calls);
        $this->assertSame(['t' => 'completed', 'a' => 'completed', 'e' => 'completed'], $this->nodes());
    }

    public function test_condition_follows_the_yes_and_no_branches(): void
    {
        $graph = [
            'nodes' => [
                $this->trigger(),
                $this->node('c', 'condition', ['filters' => ['processed' => true]]),
                $this->action('y', 'mark-yes'),
                $this->action('n', 'mark-no'),
            ],
            'edges' => [
                $this->edge('t', 'c'),
                $this->edge('c', 'y', 'yes'),
                $this->edge('c', 'n', 'no'),
            ],
        ];
        $this->activeWorkflow($graph);

        $this->fire(1); // not processed -> NO
        $this->fire(2); // processed -> YES

        $this->assertSame(['no:1', 'yes:2'], RecordingHandler::$calls);
        $this->assertSame('no', WorkflowNodeExecution::where('execution_id', 1)->where('node_id', 'c')->value('branch'));
        $this->assertSame('yes', WorkflowNodeExecution::where('execution_id', 2)->where('node_id', 'c')->value('branch'));
    }

    public function test_any_mode_needs_a_single_matching_filter(): void
    {
        $graph = [
            'nodes' => [
                $this->trigger(),
                $this->node('c', 'condition', ['mode' => 'any', 'filters' => ['processed' => true, 'createdDaysAgo' => 1]]),
                $this->action('y', 'mark-yes'),
                $this->action('n', 'mark-no'),
            ],
            'edges' => [$this->edge('t', 'c'), $this->edge('c', 'y', 'yes'), $this->edge('c', 'n', 'no')],
        ];
        $this->activeWorkflow($graph);

        // Prospect 1 is not processed, but was created (now) within the last day
        DB::table('prospects')->update(['created_at' => now()]);
        $this->fire(1);

        $this->assertSame(['yes:1'], RecordingHandler::$calls);
    }

    public function test_wait_parks_the_execution_without_blocking_and_resumes_when_due(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');

        $graph = [
            'nodes' => [$this->trigger(), $this->node('w', 'wait', ['minutes' => 30]), $this->action('a', 'mark-a')],
            'edges' => [$this->edge('t', 'w'), $this->edge('w', 'a')],
        ];
        $this->activeWorkflow($graph);

        $this->fire();

        $execution = $this->execution();
        $this->assertSame(WorkflowExecution::WAITING, $execution->status);
        $this->assertSame('2026-10-30 10:30:00', $execution->resume_at->format('Y-m-d H:i:s'));
        $this->assertSame('a', $execution->current_node_id);
        $this->assertSame([], RecordingHandler::$calls);

        // Too early: nothing to resume
        Carbon::setTestNow('2026-10-30 10:20:00');
        $this->assertSame(0, app(WorkflowExecutionManager::class)->resumeDue(100));
        $this->assertSame(WorkflowExecution::WAITING, $this->execution()->status);

        // Due
        Carbon::setTestNow('2026-10-30 10:31:00');
        $this->assertSame(1, app(WorkflowExecutionManager::class)->resumeDue(100));

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $this->assertSame(['a:1'], RecordingHandler::$calls);
    }

    public function test_a_failing_action_fails_the_execution_and_records_the_error(): void
    {
        $this->activeWorkflow($this->linearGraph('boom'));

        $this->fire();

        $execution = $this->execution();
        $this->assertSame(WorkflowExecution::FAILED, $execution->status);
        $this->assertSame('The provider is down', $execution->error);
        $this->assertSame(['t' => 'completed', 'a' => 'failed'], $this->nodes());
        $this->assertSame('The provider is down', WorkflowNodeExecution::where('node_id', 'a')->value('error'));
    }

    public function test_one_failing_execution_does_not_stop_the_others(): void
    {
        $this->activeWorkflow($this->linearGraph('boom'));
        $this->activeWorkflow($this->linearGraph('mark-a'));

        $this->fire();

        $this->assertSame(1, WorkflowExecution::where('status', 'failed')->count());
        $this->assertSame(1, WorkflowExecution::where('status', 'completed')->count());
    }

    // Idempotency and concurrency

    public function test_the_same_trigger_occurrence_starts_a_single_execution(): void
    {
        $this->activeWorkflow($this->linearGraph());

        $this->assertSame(1, $this->fire(1, 'prospect.created:1'));
        $this->assertSame(0, $this->fire(1, 'prospect.created:1')); // event received twice
        $this->assertSame(0, $this->fire(1, 'prospect.created:1'));

        $this->assertSame(1, WorkflowExecution::count());
        $this->assertSame(['a:1'], RecordingHandler::$calls);

        // Another occurrence is another execution
        $this->assertSame(1, $this->fire(1, 'prospect.created:1:again'));
        $this->assertSame(2, WorkflowExecution::count());
    }

    public function test_a_running_execution_is_not_run_by_a_second_worker(): void
    {
        $this->activeWorkflow($this->linearGraph());
        $execution = $this->execution(app(WorkflowExecutionManager::class)->start(1, 1, 1, 'prospect', 1, 'manual')->id);
        $execution->update(['status' => WorkflowExecution::RUNNING, 'locked_at' => now()]);
        RecordingHandler::$calls = [];

        app(WorkflowEngine::class)->run($execution->id);

        $this->assertSame([], RecordingHandler::$calls);
        $this->assertSame(WorkflowExecution::RUNNING, $this->execution($execution->id)->status);
    }

    public function test_a_replayed_job_does_not_run_a_finished_step_again(): void
    {
        $this->activeWorkflow($this->linearGraph());
        $this->fire();
        RecordingHandler::$calls = [];

        // Worker died after the action but before the execution moved on
        $execution = $this->execution();
        $execution->update(['status' => WorkflowExecution::PENDING, 'current_node_id' => 'a', 'finished_at' => null]);

        app(WorkflowEngine::class)->run($execution->id);

        $this->assertSame([], RecordingHandler::$calls);
        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
    }

    public function test_an_interrupted_step_fails_instead_of_running_twice(): void
    {
        $this->activeWorkflow($this->linearGraph());
        $execution = $this->execution(app(WorkflowExecutionManager::class)->start(1, 1, 1, 'prospect', 1, 'k')->id);
        // Reset to the state after a crash in the middle of the action
        $execution->update(['status' => WorkflowExecution::PENDING, 'current_node_id' => 'a', 'finished_at' => null]);
        WorkflowNodeExecution::where('node_id', 'a')->delete();
        WorkflowNodeExecution::create([
            'execution_id' => $execution->id, 'node_id' => 'a', 'node_type' => 'action',
            'status' => 'running', 'started_at' => now(),
        ]);
        RecordingHandler::$calls = [];

        app(WorkflowEngine::class)->run($execution->id);

        $this->assertSame([], RecordingHandler::$calls);
        $this->assertSame(WorkflowExecution::FAILED, $this->execution($execution->id)->status);
        $this->assertStringContainsString('interrupted', $this->execution($execution->id)->error);
    }

    public function test_stuck_executions_are_recovered_and_resumed(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');
        $this->activeWorkflow($this->linearGraph());
        $execution = $this->execution(app(WorkflowExecutionManager::class)->start(1, 1, 1, 'prospect', 1, 'k')->id);
        // The worker was killed right after having claimed it
        $execution->update(['status' => WorkflowExecution::RUNNING, 'locked_at' => now()->subMinutes(20), 'finished_at' => null]);
        WorkflowNodeExecution::query()->delete();
        RecordingHandler::$calls = [];

        $manager = app(WorkflowExecutionManager::class);
        $this->assertSame(1, $manager->recoverStuck());
        $this->assertSame(1, $manager->resumeDue(100));

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution($execution->id)->status);
    }

    // Workflow status and versions

    public function test_draft_workflows_start_nothing(): void
    {
        $workflow = Workflow::create(['project_id' => 1, 'name' => 'Draft']);
        app(WorkflowService::class)->publish($workflow, $this->linearGraph());

        $this->assertSame(0, $this->fire());
        $this->assertSame(0, WorkflowExecution::count());
    }

    public function test_pausing_holds_the_executions_in_progress_and_activating_resumes_them(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');
        $graph = [
            'nodes' => [$this->trigger(), $this->node('w', 'wait', ['hours' => 1]), $this->action('a', 'mark-a')],
            'edges' => [$this->edge('t', 'w'), $this->edge('w', 'a')],
        ];
        $workflow = $this->activeWorkflow($graph);
        $this->fire();

        $service = app(WorkflowService::class);
        $service->pause($workflow);

        // Paused: no new execution, and the one in progress does not move
        $this->assertSame(0, $this->fire(2));
        Carbon::setTestNow('2026-10-30 12:00:00');
        $this->assertSame(0, app(WorkflowExecutionManager::class)->resumeDue(100));
        app(WorkflowEngine::class)->run(1);
        $this->assertSame(WorkflowExecution::WAITING, $this->execution()->status);
        $this->assertSame([], RecordingHandler::$calls);

        $service->activate($workflow->fresh());
        $this->assertSame(1, app(WorkflowExecutionManager::class)->resumeDue(100));
        $this->assertSame(['a:1'], RecordingHandler::$calls);
    }

    public function test_archiving_cancels_the_executions_in_progress(): void
    {
        $graph = [
            'nodes' => [$this->trigger(), $this->node('w', 'wait', ['hours' => 1]), $this->action('a', 'mark-a')],
            'edges' => [$this->edge('t', 'w'), $this->edge('w', 'a')],
        ];
        $workflow = $this->activeWorkflow($graph);
        $this->fire();

        app(WorkflowService::class)->archive($workflow);

        $this->assertSame(WorkflowExecution::CANCELLED, $this->execution()->status);
    }

    public function test_an_execution_keeps_the_version_it_started_with(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');
        $graphWith = fn (string $action) => [
            'nodes' => [$this->trigger(), $this->node('w', 'wait', ['minutes' => 10]), $this->action('a', $action)],
            'edges' => [$this->edge('t', 'w'), $this->edge('w', 'a')],
        ];
        $workflow = $this->activeWorkflow($graphWith('mark-a'));
        $this->fire(1);

        // The administrator edits the workflow while the execution waits
        app(WorkflowService::class)->publish($workflow, $graphWith('mark-b'));
        $this->fire(2);

        Carbon::setTestNow('2026-10-30 10:30:00');
        app(WorkflowExecutionManager::class)->resumeDue(100);

        $this->assertEqualsCanonicalizing(['a:1', 'b:2'], RecordingHandler::$calls);
        $this->assertSame(1, $this->execution(1)->workflow_version_id);
        $this->assertSame(2, $this->execution(2)->workflow_version_id);
    }

    // Security

    public function test_a_subject_of_another_project_is_never_touched(): void
    {
        $this->activeWorkflow($this->linearGraph());

        // Prospect 3 belongs to project 2
        $this->fire(3, null, 1);

        $this->assertSame(WorkflowExecution::CANCELLED, $this->execution()->status);
        $this->assertSame([], RecordingHandler::$calls);
    }

    public function test_workflows_of_another_project_are_not_triggered(): void
    {
        $this->activeWorkflow($this->linearGraph(), 2);

        $this->assertSame(0, $this->fire(1, null, 1));
    }

    public function test_a_deleted_subject_cancels_the_execution_without_error(): void
    {
        $this->activeWorkflow($this->linearGraph());
        DB::table('prospects')->where('id', 1)->update(['deleted_at' => now()]);

        $this->fire();

        $this->assertSame(WorkflowExecution::CANCELLED, $this->execution()->status);
    }

    // Validation

    protected function errors(array $graph): array
    {
        return app(WorkflowValidator::class)->validate($graph);
    }

    public function test_a_valid_graph_has_no_error(): void
    {
        $this->assertSame([], $this->errors($this->linearGraph()));
    }

    public function test_graph_without_trigger_or_empty_is_refused(): void
    {
        $this->assertNotEmpty($this->errors(['nodes' => [], 'edges' => []]));
        $this->assertContains(
            'Le workflow doit avoir exactement un déclencheur.',
            $this->errors(['nodes' => [$this->action('a', 'mark-a')], 'edges' => []])
        );
    }

    public function test_orphan_nodes_are_refused(): void
    {
        $graph = $this->linearGraph();
        $graph['nodes'][] = $this->action('lost', 'mark-b');

        $this->assertContains(
            'L\'étape "lost" n\'est reliée à aucune autre : connectez-la ou supprimez-la.',
            $this->errors($graph)
        );
    }

    public function test_loops_are_refused(): void
    {
        $graph = [
            'nodes' => [$this->trigger(), $this->action('a', 'mark-a'), $this->action('b', 'mark-b')],
            'edges' => [$this->edge('t', 'a'), $this->edge('a', 'b'), $this->edge('b', 'a')],
        ];

        $this->assertContains(
            'Le workflow contient une boucle : une étape ramène à une étape précédente.',
            $this->errors($graph)
        );
    }

    public function test_conditions_need_yes_no_outputs_and_known_criteria(): void
    {
        $graph = [
            'nodes' => [$this->trigger(), $this->node('c', 'condition', ['filters' => ['nope' => 1]]), $this->action('a', 'mark-a')],
            'edges' => [$this->edge('t', 'c'), $this->edge('c', 'a')],
        ];
        $this->assertContains('La condition "c" doit avoir une sortie OUI et une sortie NON.', $this->errors($graph));

        $graph['edges'][1]['branch'] = 'yes';
        $this->assertContains('La condition "c" utilise un critère inconnu ("nope").', $this->errors($graph));
    }

    public function test_actions_and_waits_must_be_configured(): void
    {
        $graph = [
            'nodes' => [$this->trigger(), $this->node('w', 'wait', []), $this->action('a', 'does-not-exist')],
            'edges' => [$this->edge('t', 'w'), $this->edge('w', 'a')],
        ];
        $errors = $this->errors($graph);

        $this->assertContains("L'attente \"w\" doit durer au moins une minute (ou aller jusqu'à une heure ou une date).", $errors);
        $this->assertContains('L\'action "a" n\'est pas disponible pour ce type de déclencheur.', $errors);
    }

    public function test_an_invalid_workflow_cannot_be_published_or_activated(): void
    {
        $workflow = Workflow::create(['project_id' => 1, 'name' => 'Broken']);
        $service = app(WorkflowService::class);

        try {
            $service->publish($workflow, ['nodes' => [$this->action('a', 'mark-a')], 'edges' => []]);
            $this->fail('Publishing an invalid graph must fail');
        } catch (WorkflowValidationException $e) {
            $this->assertNotEmpty($e->errors);
        }

        $this->expectException(WorkflowValidationException::class);
        $service->activate($workflow); // nothing published
    }

    // Performance

    public function test_finding_the_workflows_of_an_event_costs_no_query(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->activeWorkflow($this->linearGraph());
        }
        $resolver = app(WorkflowTriggerResolver::class);
        $resolver->index(); // built once, then cached

        DB::enableQueryLog();
        $resolver->fire('order.created', 1, 1, 'order.created:1'); // no workflow on this trigger
        $resolver->fire('prospect.created', 99, 1, 'prospect.created:1'); // no workflow in this project
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(0, $queries);
    }

    public function test_the_resume_sweep_is_one_query_when_nothing_is_due(): void
    {
        $this->activeWorkflow($this->linearGraph());
        $this->fire();

        DB::enableQueryLog();
        app(WorkflowExecutionManager::class)->resumeDue(100);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(1, $queries);
    }

    public function test_resume_command_runs(): void
    {
        $this->artisan('workflows:resume')->assertExitCode(0);
    }
}
