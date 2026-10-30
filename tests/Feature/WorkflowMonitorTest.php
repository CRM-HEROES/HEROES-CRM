<?php

namespace Tests\Feature;

use App\Http\Controllers\API\Project\WorkflowExecutionController;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\WorkflowNodeExecution;
use App\Models\WorkflowVersion;
use App\Workflows\Actions\ActionRegistry;
use App\Workflows\WorkflowMonitor;
use App\Workflows\WorkflowService;
use App\Workflows\WorkflowTriggerResolver;
use Carbon\Carbon;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\RecordingHandler;
use Tests\TestCase;

/**
 * Follow-up of the executions: lists, details, figures, history of a
 * prospect, cancellation and purge.
 *
 * The service is tested without a logged in user (the CRM's own access rules
 * on prospects are not under test); the controller, with the real permissions.
 */
class WorkflowMonitorTest extends TestCase
{
    protected WorkflowMonitor $monitor;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'workflow_monitor_test',
            'database.connections.workflow_monitor_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            'queue.default' => 'sync',
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
            ['id' => 2, 'project_id' => 1, 'first_name' => null, 'last_name' => null, 'company_name' => 'Acme'],
            ['id' => 3, 'project_id' => 2, 'first_name' => 'Other', 'last_name' => 'Project', 'company_name' => null],
        ]);

        RecordingHandler::$calls = [];
        $registry = app(ActionRegistry::class);
        $registry->register('mark-a', new RecordingHandler('a'));
        $registry->register('boom', new RecordingHandler('boom', true));

        $this->monitor = app(WorkflowMonitor::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // Helpers

    protected function graph(): array
    {
        return [
            'nodes' => [
                ['id' => 't', 'type' => 'trigger', 'name' => 'Nouveau lead', 'config' => ['trigger' => 'prospect.created']],
                ['id' => 'a', 'type' => 'action', 'name' => 'Envoyer le SMS', 'config' => ['action' => 'send_sms', 'provider' => 'sms:smsbox']],
                ['id' => 'w', 'type' => 'wait', 'name' => null, 'config' => ['hours' => 2]],
            ],
            'edges' => [['from' => 't', 'to' => 'a'], ['from' => 'a', 'to' => 'w']],
        ];
    }

    protected function workflow(string $name = 'Lead', int $projectId = 1): Workflow
    {
        $workflow = Workflow::create(['project_id' => $projectId, 'name' => $name, 'status' => 'active']);
        $version = WorkflowVersion::create([
            'workflow_id' => $workflow->id, 'version' => 1, 'graph' => $this->graph(),
            'trigger_type' => 'prospect.created', 'baselined_at' => now(),
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

    protected function step(WorkflowExecution $execution, string $node, string $type, string $status, array $extra = []): WorkflowNodeExecution
    {
        return WorkflowNodeExecution::create($extra + [
            'execution_id' => $execution->id, 'node_id' => $node, 'node_type' => $type, 'status' => $status,
            'started_at' => now()->subMinutes(10), 'finished_at' => now()->subMinutes(9),
        ]);
    }

    // List

    public function test_the_list_is_filtered_and_most_recent_first(): void
    {
        $a = $this->workflow('A');
        $b = $this->workflow('B');
        $this->execution($a, ['status' => 'completed']);
        $this->execution($a, ['status' => 'failed', 'error' => 'Boom']);
        $this->execution($b, ['status' => 'waiting', 'resume_at' => now()->addHour()]);
        $this->execution($b, ['status' => 'completed', 'subject_id' => 2]);

        $all = $this->monitor->executions(1, [], 25)['data'];
        $this->assertSame([4, 3, 2, 1], array_column($all, 'id'));

        $this->assertSame([2], array_column($this->monitor->executions(1, ['status' => 'failed'], 25)['data'], 'id'));
        $this->assertSame([3, 2], array_column($this->monitor->executions(1, ['status' => 'failed,waiting'], 25)['data'], 'id'));
        $this->assertSame([4, 3], array_column($this->monitor->executions(1, ['workflow' => $b->id], 25)['data'], 'id'));
        $this->assertSame([4], array_column($this->monitor->executions(1, ['subject_type' => 'prospect', 'subject_id' => 2], 25)['data'], 'id'));
    }

    public function test_the_list_never_shows_another_project(): void
    {
        $this->execution($this->workflow('Mine'));
        $this->execution($this->workflow('Other', 2), ['project_id' => 2, 'subject_id' => 3]);

        $list = $this->monitor->executions(1, [], 25)['data'];

        $this->assertCount(1, $list);
        $this->assertSame('Mine', $list[0]['workflow']['name']);
        $this->assertSame([], $this->monitor->executions(1, ['workflow' => Workflow::where('name', 'Other')->value('id')], 25)['data']);
    }

    public function test_the_list_is_paginated_without_counting_everything(): void
    {
        $workflow = $this->workflow();
        foreach (range(1, 7) as $i) {
            $this->execution($workflow);
        }

        $first = $this->monitor->executions(1, [], 5);
        $this->assertCount(5, $first['data']);
        $this->assertTrue($first['has_more']);
        $this->assertArrayNotHasKey('total', $first);

        DB::enableQueryLog();
        $this->monitor->executions(1, [], 5);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // The executions, the workflows, the prospects: whatever the number of rows
        $this->assertCount(3, $queries);
    }

    public function test_the_list_names_the_prospects_and_the_workflows_even_deleted(): void
    {
        $workflow = $this->workflow('Relance');
        $this->execution($workflow, ['subject_id' => 1]);
        $this->execution($workflow, ['subject_id' => 2]);          // no name: the company
        $this->execution($workflow, ['subject_type' => 'order', 'subject_id' => 99]);
        $workflow->delete();

        $list = collect($this->monitor->executions(1, [], 25)['data'])->keyBy('id');

        $this->assertSame('Relance', $list[1]['workflow']['name']);
        $this->assertSame('Marie Curie', $list[1]['subject']['name']);
        $this->assertSame('Acme', $list[2]['subject']['name']);
        $this->assertNull($list[3]['subject']['name']);
        $this->assertSame(99, $list[3]['subject']['id']);
    }

    // Detail

    public function test_an_execution_shows_its_steps_with_the_names_of_the_graph(): void
    {
        $workflow = $this->workflow();
        $execution = $this->execution($workflow, ['status' => 'waiting', 'current_node_id' => 'w', 'resume_at' => now()->addHours(2), 'finished_at' => null]);
        $this->step($execution, 't', 'trigger', 'completed', ['result' => ['trigger' => 'prospect.created']]);
        $this->step($execution, 'a', 'action', 'completed', ['result' => ['provider' => 'sms:smsbox', 'sms_id' => 12]]);

        $detail = $this->monitor->detail($execution);

        $this->assertSame(['t', 'a'], array_column($detail['steps'], 'node_id'));
        $this->assertSame('Nouveau lead', $detail['steps'][0]['name']);
        $this->assertSame('send_sms', $detail['steps'][1]['action']);
        $this->assertSame(12, $detail['steps'][1]['result']['sms_id']);
        $this->assertSame('prospect.created', $detail['steps'][0]['trigger']);
        $this->assertSame('w', $detail['waiting_for']['node_id']);
        $this->assertSame('Marie Curie', $detail['subject']['name']);
    }

    public function test_a_failed_step_shows_its_error(): void
    {
        $workflow = $this->workflow();
        $execution = $this->execution($workflow, ['status' => 'failed', 'error' => 'Le prospect n\'a pas de numéro de mobile.']);
        $this->step($execution, 'a', 'action', 'failed', ['error' => 'Le prospect n\'a pas de numéro de mobile.']);

        $detail = $this->monitor->detail($execution);

        $this->assertSame('failed', $detail['steps'][0]['status']);
        $this->assertSame('Le prospect n\'a pas de numéro de mobile.', $detail['steps'][0]['error']);
        $this->assertNull($detail['waiting_for']);
    }

    // History of a prospect

    public function test_the_history_of_a_prospect_has_all_its_workflows_and_steps_in_few_queries(): void
    {
        $a = $this->workflow('A');
        $b = $this->workflow('B');
        foreach ([$a, $b, $a] as $workflow) {
            $execution = $this->execution($workflow, ['subject_id' => 1]);
            $this->step($execution, 't', 'trigger', 'completed');
            $this->step($execution, 'a', 'action', 'completed');
        }
        $this->execution($a, ['subject_id' => 2]);                                  // another prospect
        $this->execution($this->workflow('Other', 2), ['project_id' => 2]);         // another project, same prospect id

        DB::enableQueryLog();
        $history = $this->monitor->prospectHistory(1, 1);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(3, $history);
        $this->assertSame([3, 2, 1], array_column($history, 'id'));
        $this->assertCount(2, $history[0]['steps']);
        $this->assertSame('A', $history[0]['workflow']['name']);
        // executions, workflows, prospects, versions, steps
        $this->assertCount(5, $queries);
    }

    public function test_the_history_is_limited(): void
    {
        $workflow = $this->workflow();
        foreach (range(1, WorkflowMonitor::HISTORY + 5) as $i) {
            $this->execution($workflow);
        }

        $this->assertCount(WorkflowMonitor::HISTORY, $this->monitor->prospectHistory(1, 1));
    }

    // Figures

    public function test_the_figures_separate_what_is_going_on_from_what_happened(): void
    {
        Carbon::setTestNow('2026-10-30 12:00:00');
        $workflow = $this->workflow('Lead');
        $this->execution($workflow, ['status' => 'completed', 'started_at' => now()->subHours(2), 'finished_at' => now()->subHours(2)->addSeconds(60)]);
        $this->execution($workflow, ['status' => 'completed', 'started_at' => now()->subHours(1), 'finished_at' => now()->subHours(1)->addSeconds(120)]);
        $this->execution($workflow, ['status' => 'failed', 'error' => 'Opérateur indisponible', 'finished_at' => now()->subHour()]);
        $this->execution($workflow, ['status' => 'failed', 'error' => 'Opérateur indisponible', 'finished_at' => now()->subHour()]);
        $this->execution($workflow, ['status' => 'failed', 'error' => 'Autre erreur', 'finished_at' => now()->subHour()]);
        $this->execution($workflow, ['status' => 'completed', 'finished_at' => now()->subDays(30)]);   // out of the period
        $this->execution($workflow, ['status' => 'waiting', 'resume_at' => now()->addHours(3), 'finished_at' => null]);
        $this->execution($workflow, ['status' => 'waiting', 'resume_at' => now()->addHour(), 'finished_at' => null, 'started_at' => now()->subMinute()]);
        $this->execution($this->workflow('Other', 2), ['project_id' => 2, 'status' => 'failed', 'error' => 'Ailleurs']);

        $stats = $this->monitor->stats(1, 7);
        $row = $stats['workflows'][0];

        $this->assertSame(7, $stats['days']);
        $this->assertCount(1, $stats['workflows']);                         // only the workflows of the project
        $this->assertSame(['waiting' => 2, 'running' => 0, 'pending' => 0], $row['current']);
        $this->assertSame(['completed' => 2, 'failed' => 3, 'cancelled' => 0], $row['period']);
        $this->assertSame(90, $row['average_seconds']);                     // (60 + 120) / 2
        $this->assertSame('2026-10-30 13:00:00', Carbon::parse($row['next_resume_at'])->format('Y-m-d H:i:s'));
        $this->assertNotNull($row['last_started_at']);
        $this->assertSame([['message' => 'Opérateur indisponible', 'count' => 2], ['message' => 'Autre erreur', 'count' => 1]], $stats['errors']);
    }

    public function test_the_figures_count_the_steps_and_the_operators_used(): void
    {
        $workflow = $this->workflow();
        $execution = $this->execution($workflow);
        $this->step($execution, 't', 'trigger', 'completed');
        $this->step($execution, 'a', 'action', 'completed', ['result' => ['provider' => 'sms:smsbox']]);
        $second = $this->execution($workflow);
        $this->step($second, 'a', 'action', 'completed', ['result' => ['provider' => 'sms:smsbox']]);
        $third = $this->execution($workflow);
        $this->step($third, 'a', 'action', 'failed', ['result' => ['provider' => 'line:10'], 'error' => 'Extension indisponible']);
        $this->step($third, 'x', 'action', 'completed', ['result' => ['sms_id' => 3]]);               // no operator recorded

        $stats = $this->monitor->stats(1, 7);

        $this->assertEquals(['completed' => 3, 'failed' => 1], $stats['steps']['action']->all());
        $this->assertEquals(['completed' => 1], $stats['steps']['trigger']->all());

        $providers = collect($stats['providers'])->keyBy('provider');
        $this->assertSame(['provider' => 'sms:smsbox', 'sent' => 2, 'failed' => 0], $providers['sms:smsbox']);
        $this->assertSame(['provider' => 'line:10', 'sent' => 0, 'failed' => 1], $providers['line:10']);
    }

    public function test_the_figures_cost_a_fixed_number_of_queries(): void
    {
        foreach (range(1, 4) as $i) {
            $workflow = $this->workflow("W$i");
            $this->step($this->execution($workflow), 'a', 'action', 'completed');
        }

        DB::enableQueryLog();
        $this->monitor->stats(1, 7);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(8, $queries); // not one per workflow
    }

    public function test_a_failed_operator_is_counted_by_the_real_engine(): void
    {
        $workflow = Workflow::create(['project_id' => 1, 'name' => 'Real', 'status' => 'draft']);
        $service = app(WorkflowService::class);
        $graph = [
            'nodes' => [
                ['id' => 't', 'type' => 'trigger', 'config' => ['trigger' => 'prospect.created']],
                ['id' => 'a', 'type' => 'action', 'config' => ['action' => 'boom', 'provider' => 'sms:smsbox']],
            ],
            'edges' => [['from' => 't', 'to' => 'a']],
        ];
        $service->publish($workflow, $graph);
        $service->activate($workflow);

        app(WorkflowTriggerResolver::class)->fire('prospect.created', 1, 1, 'prospect.created:1');

        $stats = $this->monitor->stats(1, 7);

        $this->assertSame([['provider' => 'sms:smsbox', 'sent' => 0, 'failed' => 1]], $stats['providers']);
        $this->assertSame('The provider is down', $stats['errors'][0]['message']);
    }

    // Purge

    public function test_the_purge_deletes_old_finished_executions_and_their_steps_only(): void
    {
        Carbon::setTestNow('2026-10-30 12:00:00');
        config(['workflows.retention_days' => 90]);
        $workflow = $this->workflow();

        $oldDone = $this->execution($workflow, ['status' => 'completed', 'finished_at' => now()->subDays(100)]);
        $oldCancelled = $this->execution($workflow, ['status' => 'cancelled', 'finished_at' => now()->subDays(100)]);
        $recent = $this->execution($workflow, ['status' => 'completed', 'finished_at' => now()->subDays(10)]);
        $oldFailed = $this->execution($workflow, ['status' => 'failed', 'finished_at' => now()->subDays(100)]);       // kept twice as long
        $veryOldFailed = $this->execution($workflow, ['status' => 'failed', 'finished_at' => now()->subDays(200)]);
        $waiting = $this->execution($workflow, ['status' => 'waiting', 'finished_at' => null, 'started_at' => now()->subDays(300)]); // never touched

        foreach ([$oldDone, $recent, $waiting] as $execution) {
            $this->step($execution, 't', 'trigger', 'completed');
        }

        $this->artisan('workflows:prune')->assertExitCode(0);

        $this->assertEqualsCanonicalizing(
            [$recent->id, $oldFailed->id, $waiting->id],
            WorkflowExecution::pluck('id')->all()
        );
        $this->assertEqualsCanonicalizing([$recent->id, $waiting->id], WorkflowNodeExecution::pluck('execution_id')->all());
    }

    public function test_the_purge_works_in_batches(): void
    {
        Carbon::setTestNow('2026-10-30 12:00:00');
        $workflow = $this->workflow();
        $rows = [];
        foreach (range(1, 1500) as $i) {
            $rows[] = [
                'project_id' => 1, 'workflow_id' => $workflow->id, 'workflow_version_id' => $workflow->active_version_id,
                'subject_type' => 'prospect', 'subject_id' => 1, 'status' => 'completed', 'trigger_key' => "bulk$i",
                'finished_at' => now()->subDays(200)->toDateTimeString(),
            ];
        }
        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('workflow_executions')->insert($chunk);
        }

        $this->artisan('workflows:prune', ['--days' => 90])->assertExitCode(0);

        $this->assertSame(0, WorkflowExecution::count());
    }

    // Controller: permissions and cancellation

    protected function actAs(array $permissions): WorkflowExecutionController
    {
        require_once base_path('database/migrations/2023_08_14_123427_create_permission_tables.php');

        if (!DB::connection()->getSchemaBuilder()->hasTable('permissions')) {
            (new \CreatePermissionTables())->up();
            DB::table('users')->insert(['id' => 7, 'name' => 'Tonny']);
        }

        $user = new User();
        $user->forceFill(['id' => 7, 'name' => 'Tonny']);
        $user->exists = true;
        Event::fake([Authenticated::class]);
        $this->actingAs($user);
        setPermissionsTeamId(1);

        DB::table('model_has_permissions')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach ($permissions as $name) {
            $user->givePermissionTo(Permission::findOrCreate($name));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return app(WorkflowExecutionController::class);
    }

    protected function project(int $id = 1): Project
    {
        DB::table('projects')->updateOrInsert(['id' => $id], ['name' => "P$id", 'slug' => "p$id"]);

        return Project::withoutGlobalScopes()->find($id);
    }

    protected function createProjectsTable(): void
    {
        $schema = DB::connection()->getSchemaBuilder();

        if (!$schema->hasTable('projects')) {
            $schema->create('projects', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
            });
            $schema->create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
            });
        }
    }

    protected function assertNotFound(callable $call): void
    {
        try {
            $call();
            $this->fail('A 404 was expected');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    public function test_the_follow_up_needs_its_permission(): void
    {
        $this->createProjectsTable();
        $project = $this->project();
        $execution = $this->execution($this->workflow(), ['subject_type' => 'order', 'subject_id' => 5]);
        $controller = $this->actAs(['all.project.workflow.view', 'all.project.workflow.update']); // not the executions

        $this->assertNotFound(fn () => $controller->index(Request::create('/'), $project));
        $this->assertNotFound(fn () => $controller->show($project, $execution));
        $this->assertNotFound(fn () => $controller->stats(Request::create('/'), $project));
        $this->assertNotFound(fn () => $controller->prospect($project, Prospect::withoutGlobalScopes()->find(1)));

        // The permission to see is not the permission to cancel
        $controller = $this->actAs(['all.project.workflow.execution.view']);
        $this->assertCount(1, $controller->index(Request::create('/'), $project)['data']);
        $this->assertSame($execution->id, $controller->show($project, $execution)['id']);
        $this->assertNotFound(fn () => $controller->cancel($project, $execution));
    }

    public function test_the_execution_parent_permissions_are_enough(): void
    {
        $this->createProjectsTable();
        $project = $this->project();
        $this->execution($this->workflow(), ['subject_type' => 'order', 'subject_id' => 5]);

        foreach (['all.project.workflow', 'all.project', 'all'] as $permission) {
            $controller = $this->actAs([$permission]);

            $this->assertCount(1, $controller->index(Request::create('/'), $project)['data'], $permission);
        }
    }

    public function test_an_execution_of_another_project_is_a_404(): void
    {
        $this->createProjectsTable();
        $project = $this->project();
        $foreign = $this->execution($this->workflow('Other', 2), ['project_id' => 2, 'subject_type' => 'order', 'subject_id' => 5]);
        $controller = $this->actAs(['all']);

        $this->assertNotFound(fn () => $controller->show($project, $foreign));
        $this->assertNotFound(fn () => $controller->cancel($project, $foreign));
        $this->assertSame('completed', $foreign->fresh()->status); // not touched by the refused cancellation
    }

    public function test_an_execution_in_progress_can_be_cancelled_but_not_a_finished_or_running_one(): void
    {
        $this->createProjectsTable();
        $project = $this->project();
        $workflow = $this->workflow();
        $controller = $this->actAs(['all.project.workflow.execute']);

        foreach (['waiting', 'pending'] as $status) {
            $execution = $this->execution($workflow, ['status' => $status, 'finished_at' => null, 'subject_type' => 'order', 'subject_id' => 5, 'waiting_event' => 'sms_received']);

            $result = $controller->cancel($project, $execution);

            $this->assertSame('cancelled', $result['status'], $status);
            $this->assertNull($execution->fresh()->waiting_event);
            $this->assertNull($execution->fresh()->resume_at);
        }

        foreach (['running', 'completed', 'failed'] as $status) {
            $execution = $this->execution($workflow, ['status' => $status, 'subject_type' => 'order', 'subject_id' => 5]);

            try {
                $controller->cancel($project, $execution);
                $this->fail("A $status execution must not be cancellable");
            } catch (HttpException $e) {
                $this->assertSame(422, $e->getStatusCode());
            }

            $this->assertSame($status, $execution->fresh()->status);
        }
    }
}
