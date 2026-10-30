<?php

namespace Tests\Feature;

use App\Filters\ProspectFilters;
use App\Http\Controllers\API\Project\WorkflowController;
use App\Models\Project;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Workflows\Actions\ActionRegistry;
use App\Workflows\WorkflowCatalog;
use App\Workflows\WorkflowGraphSanitizer;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\RecordingHandler;
use Tests\TestCase;

/**
 * API of the workflows: permissions, isolation by project, draft, publication,
 * and the catalog that the builder is built from.
 *
 * The controller is called directly: routing, authentication and project
 * binding belong to the framework; what is tested is what it does with them.
 */
class WorkflowApiTest extends TestCase
{
    protected WorkflowController $controller;
    protected Project $project;
    protected Project $other;

    protected User $user;

    protected const ABILITIES = [
        'projectWorkflowView', 'projectWorkflowAdd', 'projectWorkflowUpdate',
        'projectWorkflowDelete', 'projectWorkflowExecute',
    ];

    /**
     * Give the user exactly these abilities, as the role permissions of the
     * CRM do: "projectWorkflowAdd" is the permission "all.project.workflow.add".
     *
     * @param  string[]  $abilities  ability names, or permission names (containing a dot)
     */
    protected function grant(array $abilities): void
    {
        DB::table('model_has_permissions')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($abilities as $ability) {
            $name = str_starts_with($ability, 'all')
                ? $ability
                : 'all.project.workflow.' . strtolower(substr($ability, strlen('projectWorkflow')));

            $this->user->givePermissionTo(Permission::findOrCreate($name));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'workflow_api_test',
            'database.connections.workflow_api_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            'queue.default' => 'sync',
        ]);

        foreach ([
            '2026_10_30_000001_create_workflow_tables',
            '2026_10_30_000002_add_workflow_filter_memberships',
            '2026_10_30_000003_add_waiting_event_to_workflow_executions',
            '2026_10_30_000004_add_draft_graph_to_workflows',
            '2026_10_30_000005_add_legacy_campaign_to_workflows',
        ] as $migration) {
            (include base_path("database/migrations/$migration.php"))->up();
        }

        // A named class (not an anonymous one): loaded once per process
        require_once base_path('database/migrations/2023_08_14_123427_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
        });
        DB::table('users')->insert(['id' => 7, 'name' => 'Tonny']);
        $schema->create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
        });
        $schema->create('project_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->string('key');
            $table->text('value')->nullable();
        });
        $schema->create('lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->string('operator');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('config')->nullable();
            $table->timestamps();
        });

        DB::table('projects')->insert([['id' => 1, 'name' => 'Heroes', 'slug' => 'heroes'], ['id' => 2, 'name' => 'Other', 'slug' => 'other']]);
        $this->project = Project::withoutGlobalScopes()->find(1);
        $this->other = Project::withoutGlobalScopes()->find(2);

        DB::table('project_settings')->insert([
            ['project_id' => 1, 'key' => 'smsbox', 'value' => json_encode(['api_key' => 'SECRET-SMSBOX'])],
            ['project_id' => 2, 'key' => 'mtarget', 'value' => json_encode(['token' => 'OTHER'])],
        ]);
        DB::table('lines')->insert([
            'id' => 10, 'project_id' => 1, 'name' => 'Tonny', 'operator' => 'kavkom', 'user_id' => 7,
            'config' => json_encode(['api_token' => 'SECRET-KAVKOM', 'domain_uuid' => 'dom-1']),
        ]);

        // The user and what the permissions let them do
        $user = new User();
        $user->forceFill(['id' => 7, 'name' => 'Tonny']);
        $user->exists = true;
        // Logging in triggers the CRM's device tracking: not under test
        Event::fake([Authenticated::class]);
        $this->actingAs($user);

        // The real permission system (spatie, one team per project)
        $this->user = $user;
        setPermissionsTeamId(1);
        $this->grant(self::ABILITIES);

        $this->controller = app(WorkflowController::class);

        RecordingHandler::$calls = [];
        app(ActionRegistry::class)->register('mark-a', new RecordingHandler('a'));
    }

    // Helpers

    protected function request(array $data = [], string $method = 'POST'): Request
    {
        return Request::create('/', $method, $data);
    }

    /**
     * A workflow with the given graph as its draft
     */
    protected function workflow(?array $graph = null, int $projectId = 1, string $status = 'draft'): Workflow
    {
        return Workflow::create([
            'project_id' => $projectId,
            'name' => 'Test',
            'status' => $status,
            'draft_graph' => $graph ?? WorkflowGraphSanitizer::blank(),
        ]);
    }

    /**
     * trigger -> action, complete and valid
     */
    protected function validGraph(): array
    {
        return [
            'nodes' => [
                ['id' => 't', 'type' => 'trigger', 'config' => ['trigger' => 'prospect.created'], 'position' => ['x' => 0, 'y' => 0]],
                ['id' => 'a', 'type' => 'action', 'config' => ['action' => 'mark-a'], 'position' => ['x' => 0, 'y' => 100]],
            ],
            'edges' => [['from' => 't', 'to' => 'a']],
        ];
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

    // Permissions

    public function test_every_action_needs_its_permission(): void
    {
        $workflow = $this->workflow();
        $catalog = app(WorkflowCatalog::class);
        $calls = [
            'projectWorkflowView' => [
                fn () => $this->controller->index($this->project),
                fn () => $this->controller->show($this->project, $workflow),
                fn () => $this->controller->catalog($this->project, $catalog),
            ],
            'projectWorkflowAdd' => [fn () => $this->controller->store($this->request(['name' => 'X']), $this->project)],
            'projectWorkflowUpdate' => [
                fn () => $this->controller->update($this->request(['name' => 'X'], 'PUT'), $this->project, $workflow, app(\App\Workflows\WorkflowValidator::class)),
                fn () => $this->controller->publish($this->request(), $this->project, $workflow, app(\App\Workflows\WorkflowService::class)),
            ],
            'projectWorkflowDelete' => [fn () => $this->controller->destroy($this->project, $workflow, app(\App\Workflows\WorkflowExecutionManager::class))],
            'projectWorkflowExecute' => [
                fn () => $this->controller->activate($this->project, $workflow, app(\App\Workflows\WorkflowService::class)),
                fn () => $this->controller->pause($this->project, $workflow, app(\App\Workflows\WorkflowService::class)),
                fn () => $this->controller->archive($this->project, $workflow, app(\App\Workflows\WorkflowService::class)),
            ],
        ];

        foreach ($calls as $permission => $actions) {
            $this->grant(array_diff(self::ABILITIES, [$permission]));

            foreach ($actions as $action) {
                $this->assertNotFound($action);
            }
        }

        $this->assertSame('draft', $workflow->fresh()->status); // nothing happened
    }

    public function test_the_parent_permissions_cover_the_workflows(): void
    {
        // "All the project permissions" or "all the workflow permissions": the usual fallback to the parent
        foreach (['all.project', 'all.project.workflow', 'all'] as $parent) {
            $this->grant([$parent]);

            $this->assertIsArray($this->controller->index($this->project)->all(), $parent);
            $this->assertSame('draft', $this->controller->store($this->request(['name' => 'X']), $this->project)['status'], $parent);
        }
    }

    public function test_the_campaign_permissions_do_not_give_access_to_the_workflows(): void
    {
        $this->grant(['all.project.campaign.add', 'all.project.campaign.update', 'all.project.campaign.view']);

        $this->assertNotFound(fn () => $this->controller->index($this->project));
        $this->assertNotFound(fn () => $this->controller->store($this->request(['name' => 'X']), $this->project));
    }

    public function test_publishing_with_activation_needs_the_execute_permission(): void
    {
        $workflow = $this->workflow($this->validGraph());
        $this->grant(['projectWorkflowView', 'projectWorkflowUpdate']);

        $this->assertNotFound(fn () => $this->controller->publish(
            $this->request(['activate' => true]), $this->project, $workflow, app(\App\Workflows\WorkflowService::class)
        ));
        $this->assertSame(0, $workflow->versions()->count());
    }

    public function test_a_workflow_of_another_project_is_never_reachable(): void
    {
        $foreign = $this->workflow(null, 2);

        $this->assertNotFound(fn () => $this->controller->show($this->project, $foreign));
        $this->assertNotFound(fn () => $this->controller->update($this->request(['name' => 'Hacked'], 'PUT'), $this->project, $foreign, app(\App\Workflows\WorkflowValidator::class)));
        $this->assertNotFound(fn () => $this->controller->activate($this->project, $foreign, app(\App\Workflows\WorkflowService::class)));
        $this->assertNotFound(fn () => $this->controller->destroy($this->project, $foreign, app(\App\Workflows\WorkflowExecutionManager::class)));

        $this->assertSame('Test', $foreign->fresh()->name);
        $this->assertSame([], $this->controller->index($this->project)->all()); // not listed either
    }

    // CRUD and draft

    public function test_a_new_workflow_is_a_draft_that_starts_with_its_trigger(): void
    {
        $response = $this->controller->store($this->request(['name' => 'Nouveau lead', 'description' => 'Premier contact']), $this->project);

        $this->assertSame('draft', $response['status']);
        $this->assertSame('Nouveau lead', $response['name']);
        $this->assertSame('trigger', $response['graph']['nodes'][0]['type']);
        $this->assertNull($response['version']);
        $this->assertTrue($response['unpublished_changes']);
        $this->assertSame(1, Workflow::first()->project_id);
        $this->assertSame(7, Workflow::first()->creator_id);
    }

    public function test_a_workflow_needs_a_name(): void
    {
        $this->expectException(ValidationException::class);

        $this->controller->store($this->request(['name' => '']), $this->project);
    }

    public function test_the_draft_is_saved_even_incomplete_and_the_problems_are_returned(): void
    {
        $workflow = $this->workflow();
        $graph = $this->validGraph();
        $graph['nodes'][1]['config'] = []; // an action that is not configured yet

        $response = $this->controller->update(
            $this->request(['draft_graph' => $graph], 'PUT'), $this->project, $workflow, app(\App\Workflows\WorkflowValidator::class)
        );

        $this->assertSame(2, count($workflow->fresh()->draft_graph['nodes']));
        $this->assertSame(['L\'action "a" n\'est pas configurée.'], $response['errors']);
    }

    public function test_a_saved_draft_only_keeps_what_the_engine_knows(): void
    {
        $workflow = $this->workflow();
        $graph = $this->validGraph();
        $graph['nodes'][0]['script'] = '<script>alert(1)</script>';
        $graph['nodes'][] = ['no_id' => true];
        $graph['edges'][] = ['from' => 'a'];
        $graph['injected'] = 'x';

        $this->controller->update($this->request(['draft_graph' => $graph], 'PUT'), $this->project, $workflow, app(\App\Workflows\WorkflowValidator::class));

        $saved = $workflow->fresh()->draft_graph;
        $this->assertSame(['nodes', 'edges'], array_keys($saved));
        $this->assertCount(2, $saved['nodes']);
        $this->assertCount(1, $saved['edges']);
        $this->assertArrayNotHasKey('script', $saved['nodes'][0]);
    }

    public function test_a_draft_cannot_grow_without_limit(): void
    {
        $nodes = array_map(fn ($i) => ['id' => "n$i", 'type' => 'end'], range(1, WorkflowGraphSanitizer::MAX_NODES + 1));

        $this->expectException(ValidationException::class);

        $this->controller->update(
            $this->request(['draft_graph' => ['nodes' => $nodes, 'edges' => []]], 'PUT'),
            $this->project, $this->workflow(), app(\App\Workflows\WorkflowValidator::class)
        );
    }

    public function test_validation_does_not_save_anything(): void
    {
        $workflow = $this->workflow();

        $response = $this->controller->validateGraph(
            $this->request(['graph' => ['nodes' => [], 'edges' => []]]), $this->project, $workflow, app(\App\Workflows\WorkflowValidator::class)
        );

        $this->assertSame(['Le workflow est vide : ajoutez un déclencheur.'], $response['errors']);
        $this->assertSame(1, count($workflow->fresh()->draft_graph['nodes']));
    }

    public function test_each_problem_says_which_step_it_is_about(): void
    {
        $workflow = $this->workflow();
        $trigger = $workflow->draft_graph['nodes'][0];

        $graph = [
            'nodes' => [$trigger, ['id' => 'action1', 'type' => 'action', 'config' => []]],
            'edges' => [['from' => $trigger['id'], 'to' => 'action1']],
        ];

        $response = $this->controller->validateGraph(
            $this->request(['graph' => $graph]), $this->project, $workflow, app(\App\Workflows\WorkflowValidator::class)
        );

        $this->assertSame(array_column($response['problems'], 'message'), $response['errors']);
        $this->assertContains('action1', array_column($response['problems'], 'node'));
        $this->assertNull(app(\App\Workflows\WorkflowValidator::class)->diagnose(['nodes' => []])[0]['node']);
    }

    // Publication and lifecycle

    public function test_an_invalid_draft_cannot_be_published(): void
    {
        $workflow = $this->workflow(); // only a trigger that leads nowhere is fine, but no action: valid. Break it:
        $workflow->update(['draft_graph' => ['nodes' => [['id' => 'a', 'type' => 'action', 'config' => ['action' => 'mark-a']]], 'edges' => []]]);

        $response = $this->controller->publish($this->request(), $this->project, $workflow, app(\App\Workflows\WorkflowService::class));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertContains('Le workflow doit avoir exactement un déclencheur.', $response->getData(true)['errors']);
        $this->assertSame(0, $workflow->versions()->count());
    }

    public function test_publishing_creates_the_next_version_without_starting_anything(): void
    {
        $workflow = $this->workflow($this->validGraph());

        $first = $this->controller->publish($this->request(), $this->project, $workflow, app(\App\Workflows\WorkflowService::class));
        $this->assertSame(1, $first['version']['number']);
        $this->assertSame('draft', $first['status']);          // published is not active
        $this->assertFalse($first['unpublished_changes']);

        // The draft is edited: it differs from what is published, until published again
        $graph = $this->validGraph();
        $graph['nodes'][1]['name'] = 'Marquer';
        $edited = $this->controller->update($this->request(['draft_graph' => $graph], 'PUT'), $this->project, $workflow->fresh(), app(\App\Workflows\WorkflowValidator::class));
        $this->assertTrue($edited['unpublished_changes']);

        $second = $this->controller->publish($this->request(), $this->project, $workflow->fresh(), app(\App\Workflows\WorkflowService::class));
        $this->assertSame(2, $second['version']['number']);
        $this->assertSame(2, $workflow->versions()->count());
    }

    public function test_a_workflow_is_activated_paused_and_archived(): void
    {
        $workflow = $this->workflow($this->validGraph());
        $service = app(\App\Workflows\WorkflowService::class);

        $published = $this->controller->publish($this->request(['activate' => true]), $this->project, $workflow, $service);
        $this->assertSame('active', $published['status']);

        $this->assertSame('paused', $this->controller->pause($this->project, $workflow->fresh(), $service)['status']);
        $this->assertSame('active', $this->controller->activate($this->project, $workflow->fresh(), $service)['status']);
        $this->assertSame('archived', $this->controller->archive($this->project, $workflow->fresh(), $service)['status']);
    }

    public function test_activating_an_unpublished_workflow_is_refused_with_a_readable_message(): void
    {
        $response = $this->controller->activate($this->project, $this->workflow(), app(\App\Workflows\WorkflowService::class));

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(['Publiez le workflow avant de l\'activer.'], $response->getData(true)['errors']);
    }

    public function test_deleting_a_workflow_cancels_its_executions(): void
    {
        $workflow = $this->workflow($this->validGraph());
        $service = app(\App\Workflows\WorkflowService::class);
        $service->publish($workflow, $workflow->draft_graph);
        $execution = WorkflowExecution::create([
            'project_id' => 1, 'workflow_id' => $workflow->id, 'workflow_version_id' => $workflow->fresh()->active_version_id,
            'subject_type' => 'prospect', 'subject_id' => 1, 'status' => 'waiting', 'trigger_key' => 'k',
        ]);

        $this->controller->destroy($this->project, $workflow, app(\App\Workflows\WorkflowExecutionManager::class));

        $this->assertSame('cancelled', $execution->fresh()->status);
        $this->assertNull(Workflow::find($workflow->id));
    }

    // List

    public function test_the_list_counts_executions_with_a_constant_number_of_queries(): void
    {
        foreach (range(1, 5) as $i) {
            $workflow = $this->workflow($this->validGraph());
            app(\App\Workflows\WorkflowService::class)->publish($workflow, $workflow->draft_graph);
            WorkflowExecution::create([
                'project_id' => 1, 'workflow_id' => $workflow->id, 'workflow_version_id' => $workflow->fresh()->active_version_id,
                'subject_type' => 'prospect', 'subject_id' => $i, 'status' => 'waiting', 'trigger_key' => "k$i",
            ]);
        }

        DB::enableQueryLog();
        $list = $this->controller->index($this->project);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(5, $list);
        $this->assertSame(1, $list[0]['executions']['waiting']);
        // The workflows, then the counts (the permission lookups of the framework apart)
        $own = array_filter($queries, fn ($query) => !str_contains($query['query'], 'permission'));
        $this->assertCount(2, $own);
    }

    // Operators

    public function test_operators_are_listed_without_any_credential_and_filtered_by_capability(): void
    {
        $registry = app(\App\Workflows\Providers\ProviderRegistry::class);

        $all = $this->controller->providers($this->request([], 'GET'), $this->project, $registry);
        $sms = $this->controller->providers($this->request(['capability' => 'sms'], 'GET'), $this->project, $registry);

        $this->assertSame(['sms:smsbox', 'line:10'], array_column($all, 'ref'));
        $this->assertSame(['sms:smsbox'], array_column($sms, 'ref'));
        $this->assertSame(['ref', 'label', 'operator', 'capabilities'], array_keys($all[0]));

        $json = json_encode($all);
        $this->assertStringNotContainsString('SECRET-SMSBOX', $json);
        $this->assertStringNotContainsString('SECRET-KAVKOM', $json);
        $this->assertStringNotContainsString('OTHER', $json); // another project's operator

        $this->expectException(HttpException::class);
        $this->controller->providers($this->request(['capability' => 'fax'], 'GET'), $this->project, $registry);
    }

    // Catalog: what the builder offers must be what the engine accepts

    public function test_the_catalog_offers_exactly_what_the_engine_knows(): void
    {
        $catalog = app(WorkflowCatalog::class)->build();

        $this->assertEqualsCanonicalizing(config('workflows.triggers'), array_column($catalog['triggers'], 'type'));
        $this->assertEqualsCanonicalizing(\App\Workflows\WorkflowGraph::TYPES, $catalog['nodes']);
        $this->assertEqualsCanonicalizing(\App\Workflows\Nodes\WaitEventNode::EVENTS, $catalog['events']);

        // Every action of the catalog exists in the registry (for a prospect)
        $registry = new ActionRegistry();
        $registry = app(ActionRegistry::class);
        foreach ($catalog['actions'] as $action) {
            $this->assertTrue($registry->has($action['type'], 'prospect'), "Unknown action {$action['type']}");
        }

        // Every condition criterion offered exists as a filter
        $filters = new ProspectFilters();
        foreach ($catalog['conditions']['filters']['prospect'] as $filter) {
            $this->assertTrue(method_exists($filters, $filter['name']), "Unknown filter {$filter['name']}");
        }

        // The checks offered are the ones the evaluator runs
        $this->assertSame(\App\Workflows\TimeChecks::CHECKS, array_column($catalog['conditions']['checks'], 'name'));

        // ... and the variables the resolver knows
        $this->assertSame(\App\Workflows\VariableResolver::VARIABLES, $catalog['variables']);
    }

    public function test_everything_the_catalog_offers_has_a_label_in_the_interface(): void
    {
        $lang = json_decode(file_get_contents(base_path('resources/js/lang/fr.json')), true)['workflow'];
        $catalog = app(WorkflowCatalog::class)->build();
        // "prospect.created" and "prospect-attach-user" are keyed "prospect_created": vue-i18n reads a dot as a path
        $slug = fn (string $id) => preg_replace('/[.-]/', '_', $id);

        $missing = [];
        $expect = function (string $group, string $key) use ($lang, &$missing) {
            if (!isset($lang[$group][$key])) {
                $missing[] = "workflow.$group.$key";
            }
        };

        foreach ($catalog['nodes'] as $node) {
            $expect('node', $node);
            $expect('node_help', $node);
        }
        foreach ($catalog['triggers'] as $trigger) {
            $expect('trigger', $slug($trigger['type']));
            $expect('trigger_help', $slug($trigger['type'])); // what the trigger means, shown under its name
            foreach ($trigger['fields'] as $field) {
                if ($field['type'] !== 'condition') {
                    $expect('field', $field['key']);
                    $expect('help', $field['key']);
                }
            }
        }
        foreach ($catalog['actions'] as $action) {
            $expect('action_type', $slug($action['type']));
            $expect('category', $action['category']);
            foreach ($action['fields'] as $field) {
                $expect('field', last(explode('.', $field['key'])));

                // The fields of the communication actions are explained (operator, channel...)
                if ($action['category'] === 'communication') {
                    $expect('help', last(explode('.', $field['key'])));
                }
            }
            if (isset($action['capability'])) {
                $expect('no_provider', $action['capability']);
            }
        }
        foreach ($catalog['events'] as $event) {
            $expect('event', $event);
        }
        foreach ($catalog['conditions']['filters'] as $filters) {
            foreach ($filters as $filter) {
                if (!isset($lang['condition']['filter'][$filter['name']])) {
                    $missing[] = "workflow.condition.filter.{$filter['name']}";
                }
            }
        }
        foreach ($catalog['conditions']['checks'] as $check) {
            if (!isset($lang['condition']['check'][$check['name']])) {
                $missing[] = "workflow.condition.check.{$check['name']}";
            }
            foreach ($check['fields'] as $field) {
                if (!isset($lang['condition']['field'][$field['key']])) {
                    $missing[] = "workflow.condition.field.{$field['key']}";
                }
            }
        }
        foreach (range(1, 7) as $day) {
            $expect('weekday', (string) $day);
        }

        $this->assertSame([], $missing);
    }

    public function test_a_graph_made_of_the_catalog_passes_the_validation(): void
    {
        $registry = app(ActionRegistry::class);

        foreach (app(WorkflowCatalog::class)->build()['triggers'] as $trigger) {
            // Trigger-specific mandatory configuration
            $config = ['trigger' => $trigger['type']];
            if (str_contains($trigger['type'], '.filter_')) {
                $config['items'] = [['filters' => ['processed' => true]]];
            }

            $graph = ['nodes' => [['id' => 't', 'type' => 'trigger', 'config' => $config]], 'edges' => []];

            $this->assertSame([], app(\App\Workflows\WorkflowValidator::class)->validate($graph, 1), $trigger['type']);
        }
    }
}
