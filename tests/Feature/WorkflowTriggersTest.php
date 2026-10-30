<?php

namespace Tests\Feature;

use App\Jobs\Workflow\BaselineFilterMemberships;
use App\Jobs\Workflow\EvaluateWorkflowTriggers;
use App\Models\Event;
use App\Models\Prospect;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Observers\WorkflowObserver;
use App\Workflows\Actions\ActionRegistry;
use App\Workflows\ConditionEvaluator;
use App\Workflows\FilterTransitionDetector;
use App\Workflows\TimeChecks;
use App\Workflows\TimeTriggerScanner;
use App\Workflows\WorkflowEvents;
use App\Workflows\WorkflowService;
use App\Workflows\WorkflowTriggerResolver;
use App\Workflows\WorkflowValidator;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Support\RecordingHandler;
use Tests\TestCase;

class WorkflowTriggersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'workflow_triggers_test',
            'database.connections.workflow_triggers_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            'queue.default' => 'sync',
        ]);

        (include base_path('database/migrations/2026_10_30_000001_create_workflow_tables.php'))->up();
        (include base_path('database/migrations/2026_10_30_000002_add_workflow_filter_memberships.php'))->up();
        (include base_path('database/migrations/2026_10_30_000003_add_waiting_event_to_workflow_executions.php'))->up();

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('import_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        $schema->create('project_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->string('key');
            $table->text('value')->nullable();
        });
        $schema->create('calendars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->softDeletes();
        });
        $schema->create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->softDeletes();
        });

        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'processed_at' => null, 'created_at' => '2026-10-01 10:00:00'],
            ['id' => 2, 'project_id' => 1, 'processed_at' => '2026-10-01 10:00:00', 'created_at' => '2026-10-01 10:00:00'],
        ]);

        RecordingHandler::$calls = [];

        $registry = new ActionRegistry();
        $registry->register('mark-a', new RecordingHandler('a'));
        $registry->register('mark-yes', new RecordingHandler('yes'));
        $registry->register('mark-no', new RecordingHandler('no'));
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

    /**
     * trigger -> action
     */
    protected function graph(string $trigger, array $triggerConfig = [], string $action = 'mark-a'): array
    {
        return [
            'nodes' => [
                $this->node('t', 'trigger', ['trigger' => $trigger] + $triggerConfig),
                $this->node('a', 'action', ['action' => $action]),
            ],
            'edges' => [$this->edge('t', 'a')],
        ];
    }

    protected function activate(array $graph, int $projectId = 1): Workflow
    {
        $workflow = Workflow::create(['project_id' => $projectId, 'name' => 'Test']);
        $service = app(WorkflowService::class);
        $service->publish($workflow, $graph);
        $service->activate($workflow);

        return $workflow->fresh();
    }

    protected function prospect(int $id, int $projectId = 1): Prospect
    {
        $prospect = new Prospect();
        $prospect->forceFill(['id' => $id, 'project_id' => $projectId]);
        $prospect->exists = true;

        return $prospect;
    }

    /**
     * What the observer does after a change of the prospects
     */
    protected function changed(array $ids, string $event = 'updated', int $stamp = 1000): void
    {
        EvaluateWorkflowTriggers::dispatchSync('prospect', $ids, 1, $event, $stamp);
    }

    protected function setProcessed(int $id, bool $processed): void
    {
        DB::table('prospects')->where('id', $id)->update(['processed_at' => $processed ? '2026-10-02 10:00:00' : null]);
    }

    protected function executions(string $status = null): int
    {
        return WorkflowExecution::when($status, fn ($q) => $q->where('status', $status))->count();
    }

    // Events

    public function test_creating_and_updating_a_prospect_start_the_matching_workflows(): void
    {
        $this->activate($this->graph('prospect.created'));
        $this->activate($this->graph('prospect.updated', [], 'mark-yes'));

        $observer = new WorkflowObserver();
        $observer->created($this->prospect(1));
        $this->assertSame(['a:1'], RecordingHandler::$calls);

        $observer->updated($this->prospect(1));
        $this->assertSame(['a:1', 'yes:1'], RecordingHandler::$calls);
    }

    public function test_nothing_is_queued_and_no_query_runs_when_no_workflow_concerns_the_event(): void
    {
        $this->activate($this->graph('prospect.created'), 2); // another project
        Queue::fake();
        app(WorkflowTriggerResolver::class)->index(); // built once, then cached

        DB::enableQueryLog();
        (new WorkflowObserver())->created($this->prospect(1));
        (new WorkflowObserver())->updated($this->prospect(1));
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(0, $queries);
        Queue::assertNothingPushed();
    }

    public function test_the_request_only_queues_a_job(): void
    {
        $this->activate($this->graph('prospect.created'));
        Queue::fake();

        (new WorkflowObserver())->created($this->prospect(1));

        Queue::assertPushed(EvaluateWorkflowTriggers::class, 1);
        $this->assertSame(0, $this->executions());
    }

    public function test_what_a_workflow_changes_does_not_start_workflows(): void
    {
        $this->activate($this->graph('prospect.updated'));
        Queue::fake();

        WorkflowTriggerResolver::withoutTriggers(fn () => (new WorkflowObserver())->updated($this->prospect(1)));

        Queue::assertNothingPushed();

        // ... and only inside the section
        (new WorkflowObserver())->updated($this->prospect(1));
        Queue::assertPushed(EvaluateWorkflowTriggers::class, 1);
    }

    public function test_an_update_received_twice_is_one_occurrence(): void
    {
        $this->activate($this->graph('prospect.updated'));

        $this->changed([1], 'updated', 5000);
        $this->changed([1], 'updated', 5000); // same change, job replayed
        $this->assertSame(1, $this->executions());

        $this->changed([1], 'updated', 5001); // a later change
        $this->assertSame(2, $this->executions());
    }

    public function test_prospects_imported_in_bulk_are_reported_when_the_import_is_over(): void
    {
        $this->activate($this->graph('prospect.created'));
        DB::table('prospects')->insert([
            ['id' => 10, 'project_id' => 1, 'import_id' => 5],
            ['id' => 11, 'project_id' => 1, 'import_id' => 5],
            ['id' => 12, 'project_id' => 1, 'import_id' => 6], // another import
        ]);

        WorkflowEvents::importFinished(5);

        $this->assertEqualsCanonicalizing(['a:10', 'a:11'], RecordingHandler::$calls);
    }

    public function test_bulk_events_are_ignored_without_workflow(): void
    {
        Queue::fake();

        WorkflowEvents::importFinished(5);
        WorkflowEvents::prospectsCreated(1, [1, 2]);

        Queue::assertNothingPushed();
    }

    // Filter triggers

    protected function filterGraph(string $trigger, array $extra = []): array
    {
        return $this->graph($trigger, ['filters' => ['processed' => true]] + $extra);
    }

    public function test_entering_a_filter_fires_once_and_existing_members_do_not_fire(): void
    {
        $this->activate($this->filterGraph('prospect.filter_entered'));

        // Prospect 2 matched before the workflow: recorded by the initialisation, nothing fires
        $this->assertSame(0, $this->executions());
        $this->assertSame([2], DB::table(FilterTransitionDetector::TABLE)->pluck('subject_id')->all());

        // Prospect 1 starts to match
        $this->setProcessed(1, true);
        $this->changed([1]);
        $this->assertSame(['a:1'], RecordingHandler::$calls);

        // Same state later: no transition
        $this->changed([1], 'updated', 2000);
        $this->changed([2], 'updated', 2000);
        $this->assertSame(['a:1'], RecordingHandler::$calls);
    }

    public function test_leaving_then_entering_again_fires_again(): void
    {
        $this->activate($this->filterGraph('prospect.filter_entered'));

        $this->setProcessed(1, true);
        $this->changed([1]);
        $this->setProcessed(1, false);
        $this->changed([1], 'updated', 2000);
        $this->setProcessed(1, true);
        $this->changed([1], 'updated', 3000);

        $this->assertSame(['a:1', 'a:1'], RecordingHandler::$calls);
    }

    public function test_leaving_a_filter_fires_the_left_trigger(): void
    {
        $this->activate($this->filterGraph('prospect.filter_left'));

        $this->setProcessed(2, false); // prospect 2 was a member
        $this->changed([2]);

        $this->assertSame(['a:2'], RecordingHandler::$calls);

        $this->changed([2], 'updated', 2000); // still out: nothing more
        $this->assertSame(['a:2'], RecordingHandler::$calls);
    }

    public function test_leaving_stops_the_executions_in_progress_when_configured(): void
    {
        $graph = [
            'nodes' => [
                $this->node('t', 'trigger', ['trigger' => 'prospect.filter_entered', 'filters' => ['processed' => true], 'exit_on_leave' => true]),
                $this->node('w', 'wait', ['hours' => 2]),
                $this->node('a', 'action', ['action' => 'mark-a']),
            ],
            'edges' => [$this->edge('t', 'w'), $this->edge('w', 'a')],
        ];
        $this->activate($graph);

        $this->setProcessed(1, true);
        $this->changed([1]);
        $this->assertSame(1, $this->executions(WorkflowExecution::WAITING));

        $this->setProcessed(1, false);
        $this->changed([1], 'updated', 2000);

        $this->assertSame(1, $this->executions(WorkflowExecution::CANCELLED));
    }

    public function test_leaving_lets_the_executions_go_on_by_default(): void
    {
        $graph = [
            'nodes' => [
                $this->node('t', 'trigger', ['trigger' => 'prospect.filter_entered', 'filters' => ['processed' => true]]),
                $this->node('w', 'wait', ['hours' => 2]),
                $this->node('a', 'action', ['action' => 'mark-a']),
            ],
            'edges' => [$this->edge('t', 'w'), $this->edge('w', 'a')],
        ];
        $this->activate($graph);

        $this->setProcessed(1, true);
        $this->changed([1]);
        $this->setProcessed(1, false);
        $this->changed([1], 'updated', 2000);

        $this->assertSame(1, $this->executions(WorkflowExecution::WAITING));
    }

    public function test_a_filter_trigger_stays_off_until_its_memberships_are_initialised(): void
    {
        Queue::fake([BaselineFilterMemberships::class]);
        $workflow = $this->activate($this->filterGraph('prospect.filter_entered'));
        $versionId = $workflow->active_version_id;

        // The initialisation is queued but did not run: nothing is evaluated
        $this->setProcessed(1, true);
        $this->changed([1]);
        $this->assertSame(0, $this->executions());
        $this->assertSame(0, DB::table(FilterTransitionDetector::TABLE)->count());

        (new BaselineFilterMemberships($workflow->id, $versionId))->handle(app(ConditionEvaluator::class));

        // Prospects 1 and 2 match: both recorded, none fired
        $this->assertEqualsCanonicalizing([1, 2], DB::table(FilterTransitionDetector::TABLE)->pluck('subject_id')->all());
        $this->assertSame(0, $this->executions());
        $this->assertNotNull(Workflow::find($workflow->id)->activeVersion->baselined_at);
    }

    public function test_publishing_a_new_filter_version_rebuilds_the_memberships(): void
    {
        $workflow = $this->activate($this->filterGraph('prospect.filter_entered'));
        $this->assertSame([2], DB::table(FilterTransitionDetector::TABLE)->pluck('subject_id')->all());

        // New filter: "not processed"
        app(WorkflowService::class)->publish($workflow, $this->graph('prospect.filter_entered', ['filters' => ['processed' => false]]));

        $this->assertSame([1], DB::table(FilterTransitionDetector::TABLE)->pluck('subject_id')->all());
        $this->assertSame(0, $this->executions());
    }

    public function test_a_batch_of_changes_is_evaluated_with_two_queries_per_workflow(): void
    {
        $this->activate($this->filterGraph('prospect.filter_entered'));
        $rows = [];
        for ($i = 100; $i < 150; $i++) {
            $rows[] = ['id' => $i, 'project_id' => 1, 'processed_at' => '2026-10-01 10:00:00'];
        }
        DB::table('prospects')->insert($rows);
        $ids = range(100, 149);

        $this->changed($ids, 'created'); // 50 arrivals: recorded, one execution each
        $this->assertSame(50, $this->executions());

        DB::enableQueryLog();
        app(FilterTransitionDetector::class)->detect(1, 'prospect', $ids); // no change
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(2, $queries);
    }

    // Conditions

    protected function satisfies(array $config, int $prospectId = 1): bool
    {
        return app(ConditionEvaluator::class)->matches(1, 'prospect', $prospectId, $config);
    }

    public function test_condition_groups_can_be_nested(): void
    {
        // Prospect 1: not processed, created long ago. Prospect 2: processed.
        $config = ['op' => 'or', 'items' => [
            ['filters' => ['processed' => true]],
            ['op' => 'and', 'items' => [
                ['filters' => ['processed' => false]],
                ['filters' => ['createdDaysAgo' => 1]], // created in the last day
            ]],
        ]];

        $this->assertTrue($this->satisfies($config, 2));  // first branch
        $this->assertFalse($this->satisfies($config, 1)); // not processed but old

        DB::table('prospects')->where('id', 1)->update(['created_at' => now()]);
        $this->assertTrue($this->satisfies($config, 1));  // second branch
    }

    public function test_filters_inside_an_item_are_combined_with_and(): void
    {
        $this->assertFalse($this->satisfies(['items' => [['filters' => ['processed' => true, 'createdDaysAgo' => 1]]]], 2));
        $this->assertTrue($this->satisfies(['items' => [['filters' => ['processed' => true]]]], 2));
    }

    public function test_work_hours_use_the_timezone_of_the_project(): void
    {
        $hours = ['items' => [['check' => 'work_hours', 'days' => [1, 2, 3, 4, 5], 'from' => '08:00', 'to' => '17:00']]];

        // Friday 2026-10-30, 07:30 UTC = 08:30 in Paris (UTC+1)
        Carbon::setTestNow(Carbon::parse('2026-10-30 07:30:00', 'UTC'));

        // No timezone configured: the application one (UTC) => before 08:00
        $this->assertFalse($this->satisfies($hours));

        DB::table('project_settings')->insert(['project_id' => 1, 'key' => 'timezone', 'value' => json_encode('Europe/Paris')]);
        \Illuminate\Support\Facades\Cache::forget('workflows:timezone:1');
        $this->assertTrue($this->satisfies($hours));

        // 16:30 UTC = 17:30 in Paris: closed
        Carbon::setTestNow(Carbon::parse('2026-10-30 16:30:00', 'UTC'));
        $this->assertFalse($this->satisfies($hours));

        // Saturday morning
        Carbon::setTestNow(Carbon::parse('2026-10-31 09:00:00', 'UTC'));
        $this->assertFalse($this->satisfies($hours));
    }

    public function test_work_hours_can_cross_midnight(): void
    {
        $config = ['days' => [5], 'from' => '22:00', 'to' => '06:00']; // Friday night

        $this->assertTrue(TimeChecks::workHours($config, Carbon::parse('2026-10-30 23:00:00')));  // Friday 23:00
        $this->assertTrue(TimeChecks::workHours($config, Carbon::parse('2026-10-31 05:00:00')));  // Saturday 05:00 (still Friday's night)
        $this->assertFalse(TimeChecks::workHours($config, Carbon::parse('2026-10-31 23:00:00'))); // Saturday 23:00
        $this->assertFalse(TimeChecks::workHours($config, Carbon::parse('2026-10-30 05:00:00'))); // Friday 05:00 (Thursday's night)
    }

    public function test_a_condition_with_a_time_check_is_a_single_query(): void
    {
        $config = ['op' => 'and', 'items' => [
            ['check' => 'work_hours', 'from' => '00:00', 'to' => '24:00'],
            ['op' => 'or', 'items' => [['filters' => ['processed' => true]], ['filters' => ['createdDaysAgo' => 1]]]],
        ]];

        DB::enableQueryLog();
        $this->satisfies($config, 2);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // + the timezone of the project, read once and cached
        $this->assertLessThanOrEqual(2, count($queries));
    }

    public function test_the_workflow_follows_the_work_hours_branch(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-30 10:00:00', 'UTC')); // Friday, open (UTC)
        $graph = [
            'nodes' => [
                $this->node('t', 'trigger', ['trigger' => 'prospect.created']),
                $this->node('c', 'condition', ['items' => [['check' => 'work_hours', 'days' => [1, 2, 3, 4, 5], 'from' => '08:00', 'to' => '17:00']]]),
                $this->node('y', 'action', ['action' => 'mark-yes']),
                $this->node('n', 'action', ['action' => 'mark-no']),
            ],
            'edges' => [$this->edge('t', 'c'), $this->edge('c', 'y', 'yes'), $this->edge('c', 'n', 'no')],
        ];
        $this->activate($graph);

        $this->changed([1], 'created');
        Carbon::setTestNow(Carbon::parse('2026-10-31 10:00:00', 'UTC')); // Saturday, closed
        $this->changed([2], 'created');

        $this->assertSame(['yes:1', 'no:2'], RecordingHandler::$calls);
    }

    // Appointments

    protected function appointment(int $id, ?string $startedAt, ?string $endedAt = null, int $calendar = 1): void
    {
        DB::table('events')->insert(['id' => $id, 'calendar_id' => $calendar, 'started_at' => $startedAt, 'ended_at' => $endedAt]);
    }

    public function test_an_appointment_starting_soon_starts_the_workflow_once(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');
        DB::table('calendars')->insert([['id' => 1, 'project_id' => 1], ['id' => 2, 'project_id' => 2]]);
        $this->appointment(1, '2026-10-30 10:10:00');            // in 10 min: reminder time (15 min before) passed 5 min ago
        $this->appointment(2, '2026-10-30 10:40:00');            // in 40 min: too early
        $this->appointment(3, '2026-10-30 09:50:00');            // already started
        $this->appointment(4, '2026-10-30 10:10:00', null, 2);   // other project

        $this->activate($this->graph('event.starting_soon', ['minutes_before' => 15]));
        $scanner = app(TimeTriggerScanner::class);

        $this->assertSame(1, $scanner->scan());
        $this->assertSame(['a:1'], RecordingHandler::$calls);

        // Next minutes: the same appointment is still in the window, no second execution
        Carbon::setTestNow('2026-10-30 10:01:00');
        $this->assertSame(0, $scanner->scan());

        // The appointment of 10:40 is reached later
        Carbon::setTestNow('2026-10-30 10:27:00');
        $this->assertSame(1, $scanner->scan());
        $this->assertSame(['a:1', 'a:2'], RecordingHandler::$calls);
    }

    public function test_a_rescheduled_appointment_triggers_again(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');
        DB::table('calendars')->insert(['id' => 1, 'project_id' => 1]);
        $this->appointment(1, '2026-10-30 10:10:00');
        $this->activate($this->graph('event.starting_soon', ['minutes_before' => 15]));
        $scanner = app(TimeTriggerScanner::class);

        $scanner->scan();
        DB::table('events')->where('id', 1)->update(['started_at' => '2026-10-30 10:12:00']);

        $this->assertSame(1, $scanner->scan());
    }

    public function test_an_ended_appointment_starts_the_workflow(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');
        DB::table('calendars')->insert(['id' => 1, 'project_id' => 1]);
        $this->appointment(1, '2026-10-30 09:00:00', '2026-10-30 09:58:00');
        $this->appointment(2, '2026-10-30 07:00:00', '2026-10-30 08:00:00'); // long ago
        $this->appointment(3, '2026-10-30 09:30:00', '2026-10-30 10:30:00'); // not over

        $this->activate($this->graph('event.ended'));

        $this->assertSame(1, app(TimeTriggerScanner::class)->scan());
        $this->assertSame(['a:1'], RecordingHandler::$calls);
    }

    public function test_the_clock_scan_costs_nothing_without_appointment_workflow(): void
    {
        $this->activate($this->graph('prospect.created'));

        DB::enableQueryLog();
        $this->assertSame(0, app(TimeTriggerScanner::class)->scan());
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(1, $queries); // the (cached) index, built once
    }

    // Validation

    protected function errors(array $graph): array
    {
        return app(WorkflowValidator::class)->validate($graph);
    }

    public function test_a_filter_trigger_needs_a_filter(): void
    {
        $this->assertContains(
            'Le déclencheur "entre dans / sort d\'un filtre" doit définir le filtre à surveiller.',
            $this->errors($this->graph('prospect.filter_entered'))
        );
        $this->assertSame([], $this->errors($this->filterGraph('prospect.filter_left')));
    }

    public function test_invalid_time_checks_and_groups_are_refused(): void
    {
        $node = fn (array $config) => [
            'nodes' => [
                $this->node('t', 'trigger', ['trigger' => 'prospect.created']),
                $this->node('c', 'condition', $config),
                $this->node('a', 'action', ['action' => 'mark-a']),
                $this->node('b', 'action', ['action' => 'mark-a']),
            ],
            'edges' => [$this->edge('t', 'c'), $this->edge('c', 'a', 'yes'), $this->edge('c', 'b', 'no')],
        ];

        $this->assertNotEmpty($this->errors($node(['items' => [['check' => 'work_hours', 'from' => '25:00', 'to' => '17:00']]])));
        $this->assertNotEmpty($this->errors($node(['items' => [['check' => 'work_hours', 'from' => '08:00', 'to' => '08:00']]])));
        $this->assertNotEmpty($this->errors($node(['items' => [['check' => 'work_hours', 'days' => [0, 9]]]])));
        $this->assertNotEmpty($this->errors($node(['items' => [['check' => 'work_hours', 'timezone' => 'Mars/Phobos']]])));
        $this->assertNotEmpty($this->errors($node(['items' => [['check' => 'moon_phase']]])));
        $this->assertNotEmpty($this->errors($node(['items' => [[]]])));
        $this->assertNotEmpty($this->errors($node(['items' => [['filters' => ['nope' => 1]]]])));

        $deep = ['filters' => ['processed' => true]];
        for ($i = 0; $i < 6; $i++) {
            $deep = ['items' => [$deep]];
        }
        $this->assertNotEmpty($this->errors($node($deep)));

        $this->assertSame([], $this->errors($node(['items' => [
            ['check' => 'work_hours', 'days' => [1, 2, 3], 'from' => '08:00', 'to' => '17:00', 'timezone' => 'Europe/Paris'],
            ['op' => 'or', 'items' => [['filters' => ['processed' => true]], ['check' => 'date_range', 'from' => '2026-12-24']]],
        ]])));
    }

    public function test_starting_soon_needs_a_valid_delay(): void
    {
        $this->assertNotEmpty($this->errors($this->graph('event.starting_soon', ['minutes_before' => -5])));
        $this->assertNotEmpty($this->errors($this->graph('event.starting_soon', ['minutes_before' => 'soon'])));
        $this->assertSame([], $this->errors($this->graph('event.starting_soon', ['minutes_before' => 30])));
    }
}
