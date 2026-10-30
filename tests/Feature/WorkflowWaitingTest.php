<?php

namespace Tests\Feature;

use App\Jobs\Workflow\WakeWorkflowExecutions;
use App\Models\Event;
use App\Models\Interaction;
use App\Models\Line;
use App\Models\Message;
use App\Models\Order;
use App\Models\Sms;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\WorkflowNodeExecution;
use App\Observers\WorkflowEventObserver;
use App\Services\CloudTalk;
use App\Workflows\Actions\ActionRegistry;
use App\Workflows\Nodes\WaitNode;
use App\Workflows\Providers\ProviderRegistry;
use App\Workflows\WorkflowEventWaker;
use App\Workflows\WorkflowExecutionManager;
use App\Workflows\WorkflowService;
use App\Workflows\WorkflowTriggerResolver;
use App\Workflows\WorkflowValidator;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Support\RecordingHandler;
use Tests\TestCase;

/**
 * Phase 4: waiting until a time / a date, waiting for an event of the
 * prospect, and the CloudTalk lines as call operators.
 */
class WorkflowWaitingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'workflow_waiting_test',
            'database.connections.workflow_waiting_test' => [
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
            $table->string('first_name')->nullable();
            $table->string('mobile_phone_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
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

        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'first_name' => 'Marie', 'mobile_phone_number' => '06 11 22 33 44'],
            ['id' => 2, 'project_id' => 1, 'first_name' => 'Pierre', 'mobile_phone_number' => null],
        ]);
        DB::table('project_settings')->insert(['project_id' => 1, 'key' => 'timezone', 'value' => json_encode('Europe/Paris')]);

        RecordingHandler::$calls = [];

        $registry = app(ActionRegistry::class);
        $registry->register('mark-yes', new RecordingHandler('yes'));
        $registry->register('mark-no', new RecordingHandler('no'));
        $registry->register('mark-a', new RecordingHandler('a'));
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

    protected function trigger(string $type = 'prospect.created'): array
    {
        return $this->node('t', 'trigger', ['trigger' => $type]);
    }

    /**
     * trigger -> wait for an event -> yes: mark-yes / no: mark-no
     */
    protected function eventWaitGraph(array $wait = null): array
    {
        return [
            'nodes' => [
                $this->trigger(),
                $this->node('w', 'wait_event', $wait ?? ['event' => 'sms_received', 'hours' => 24]),
                $this->node('y', 'action', ['action' => 'mark-yes']),
                $this->node('n', 'action', ['action' => 'mark-no']),
            ],
            'edges' => [
                $this->edge('t', 'w'),
                $this->edge('w', 'y', 'yes'),
                $this->edge('w', 'n', 'no'),
            ],
        ];
    }

    protected function activate(array $graph): Workflow
    {
        $workflow = Workflow::create(['project_id' => 1, 'name' => 'Test']);
        $service = app(WorkflowService::class);
        $service->publish($workflow, $graph);
        $service->activate($workflow);

        return $workflow->fresh();
    }

    protected function fire(int $prospectId = 1): void
    {
        app(WorkflowTriggerResolver::class)->fire('prospect.created', 1, $prospectId, "prospect.created:$prospectId");
    }

    protected function execution(int $id = 1): WorkflowExecution
    {
        return WorkflowExecution::findOrFail($id);
    }

    protected function incomingSms(int $prospectId): Sms
    {
        $sms = new Sms();
        $sms->forceFill(['prospect_id' => $prospectId, 'is_incoming' => true, 'from_user' => false]);

        return $sms;
    }

    protected function errors(array $graph): array
    {
        return app(WorkflowValidator::class)->validate($graph, 1);
    }

    // Wait until a time or a date

    protected function resumeAt(array $config, string $now): ?string
    {
        return WaitNode::resumeAt($config, 'Europe/Paris', Carbon::parse($now, 'UTC'))?->setTimezone('UTC')->format('Y-m-d H:i:s');
    }

    public function test_wait_until_a_time_goes_to_its_next_occurrence_in_the_timezone(): void
    {
        // Friday 2026-10-30. Paris is UTC+1: 09:00 in Paris is 08:00 UTC
        $this->assertSame('2026-10-30 08:00:00', $this->resumeAt(['until_time' => '09:00'], '2026-10-30 07:00:00')); // 08:00 in Paris: today
        $this->assertSame('2026-10-31 08:00:00', $this->resumeAt(['until_time' => '09:00'], '2026-10-30 08:30:00')); // 09:30 in Paris: tomorrow
        $this->assertSame('2026-10-31 08:00:00', $this->resumeAt(['until_time' => '09:00'], '2026-10-30 08:00:00')); // exactly 09:00: the next one
    }

    public function test_wait_until_a_time_can_be_limited_to_some_weekdays(): void
    {
        // Friday evening, only on Mondays (1): Monday 2026-11-02, 09:00 in Paris
        $this->assertSame(
            '2026-11-02 08:00:00',
            $this->resumeAt(['until_time' => '09:00', 'days' => [1]], '2026-10-30 18:00:00')
        );
        // Weekdays: Friday evening gives Monday, Thursday evening gives Friday
        $this->assertSame('2026-11-02 08:00:00', $this->resumeAt(['until_time' => '09:00', 'days' => [1, 2, 3, 4, 5]], '2026-10-30 18:00:00'));
        $this->assertSame('2026-10-30 08:00:00', $this->resumeAt(['until_time' => '09:00', 'days' => [1, 2, 3, 4, 5]], '2026-10-29 18:00:00'));
    }

    public function test_wait_until_a_date_uses_the_timezone_and_a_past_date_does_not_wait(): void
    {
        $this->assertSame('2026-12-24 07:00:00', $this->resumeAt(['until_date' => '2026-12-24 08:00'], '2026-10-30 07:00:00'));
        $this->assertSame('2026-10-01 06:00:00', $this->resumeAt(['until_date' => '2026-10-01 08:00'], '2026-10-30 07:00:00')); // past
    }

    public function test_an_execution_waits_until_the_time_with_the_timezone_of_the_project(): void
    {
        Carbon::setTestNow('2026-10-30 07:00:00'); // 08:00 in Paris
        $graph = [
            'nodes' => [$this->trigger(), $this->node('w', 'wait', ['until_time' => '09:00']), $this->node('a', 'action', ['action' => 'mark-a'])],
            'edges' => [$this->edge('t', 'w'), $this->edge('w', 'a')],
        ];
        $this->activate($graph);

        $this->fire();

        $this->assertSame(WorkflowExecution::WAITING, $this->execution()->status);
        $this->assertSame('2026-10-30 08:00:00', $this->execution()->resume_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow('2026-10-30 08:01:00');
        app(WorkflowExecutionManager::class)->resumeDue(100);

        $this->assertSame(['a:1'], RecordingHandler::$calls);
    }

    public function test_a_wait_until_a_past_date_continues_at_once(): void
    {
        Carbon::setTestNow('2026-10-30 07:00:00');
        $graph = [
            'nodes' => [$this->trigger(), $this->node('w', 'wait', ['until_date' => '2026-01-01 08:00']), $this->node('a', 'action', ['action' => 'mark-a'])],
            'edges' => [$this->edge('t', 'w'), $this->edge('w', 'a')],
        ];
        $this->activate($graph);

        $this->fire();

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $this->assertSame(['a:1'], RecordingHandler::$calls);
    }

    // Wait for an event

    public function test_the_event_wakes_the_execution_which_takes_the_yes_way(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');
        $this->activate($this->eventWaitGraph());

        $this->fire();

        $execution = $this->execution();
        $this->assertSame(WorkflowExecution::WAITING, $execution->status);
        $this->assertSame('sms_received', $execution->waiting_event);
        $this->assertSame('w', $execution->current_node_id);
        $this->assertSame('2026-10-31 10:00:00', $execution->resume_at->format('Y-m-d H:i:s')); // timeout
        $this->assertSame([], RecordingHandler::$calls);

        // The prospect answers
        Carbon::setTestNow('2026-10-30 12:00:00');
        (new WorkflowEventObserver())->created($this->incomingSms(1));

        $execution = $this->execution();
        $this->assertSame(WorkflowExecution::COMPLETED, $execution->status);
        $this->assertNull($execution->waiting_event);
        $this->assertSame(['yes:1'], RecordingHandler::$calls);

        $row = WorkflowNodeExecution::where('node_id', 'w')->first();
        $this->assertSame('completed', $row->status);
        $this->assertSame('yes', $row->branch);
        $this->assertSame('event', $row->result['outcome']);
    }

    public function test_the_timeout_takes_the_no_way(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');
        $this->activate($this->eventWaitGraph());
        $this->fire();

        // Too early: the scheduler has nothing to resume
        Carbon::setTestNow('2026-10-31 09:00:00');
        $this->assertSame(0, app(WorkflowExecutionManager::class)->resumeDue(100));

        Carbon::setTestNow('2026-10-31 10:01:00');
        $this->assertSame(1, app(WorkflowExecutionManager::class)->resumeDue(100));

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $this->assertSame(['no:1'], RecordingHandler::$calls);

        $row = WorkflowNodeExecution::where('node_id', 'w')->first();
        $this->assertSame('no', $row->branch);
        $this->assertSame('timeout', $row->result['outcome']);
    }

    public function test_only_the_awaited_event_of_the_waiting_prospect_wakes_the_execution(): void
    {
        $this->activate($this->eventWaitGraph());
        $this->fire(1);

        $observer = new WorkflowEventObserver();

        // Another prospect
        $observer->created($this->incomingSms(2));
        // A SMS written by a user, not by the prospect
        $sms = new Sms();
        $sms->forceFill(['prospect_id' => 1, 'is_incoming' => false, 'from_user' => true]);
        $observer->created($sms);
        // Another kind of event
        $message = new Message();
        $message->forceFill(['prospect_id' => 1, 'from_user' => false]);
        $observer->created($message);

        $this->assertSame(WorkflowExecution::WAITING, $this->execution()->status);
        $this->assertSame([], RecordingHandler::$calls);
    }

    public function test_an_event_reported_twice_wakes_the_execution_once(): void
    {
        $this->activate($this->eventWaitGraph());
        $this->fire();

        $waker = app(WorkflowEventWaker::class);

        $this->assertSame(1, $waker->wake('sms_received', 1));
        $this->assertSame(0, $waker->wake('sms_received', 1));
        $this->assertSame(['yes:1'], RecordingHandler::$calls);
    }

    public function test_an_event_from_before_the_wait_does_not_count(): void
    {
        $this->activate($this->eventWaitGraph());

        // The prospect answered BEFORE the workflow reached the wait
        $this->assertSame(0, app(WorkflowEventWaker::class)->wake('sms_received', 1));
        $this->fire();

        $this->assertSame(WorkflowExecution::WAITING, $this->execution()->status);
        $this->assertSame('sms_received', $this->execution()->waiting_event);
    }

    public function test_a_paused_workflow_keeps_the_answer_until_it_is_activated_again(): void
    {
        $workflow = $this->activate($this->eventWaitGraph());
        $this->fire();
        app(WorkflowService::class)->pause($workflow);

        (new WorkflowEventObserver())->created($this->incomingSms(1));

        // The event is recorded (it did happen) but nothing runs while paused
        $this->assertSame(WorkflowExecution::WAITING, $this->execution()->status);
        $this->assertNull($this->execution()->waiting_event);
        $this->assertSame([], RecordingHandler::$calls);

        app(WorkflowService::class)->activate($workflow->fresh());
        $this->assertSame(1, app(WorkflowExecutionManager::class)->resumeDue(100));

        $this->assertSame(['yes:1'], RecordingHandler::$calls);
    }

    public function test_archiving_cancels_an_execution_waiting_for_an_event(): void
    {
        $workflow = $this->activate($this->eventWaitGraph());
        $this->fire();

        app(WorkflowService::class)->archive($workflow);

        $this->assertSame(WorkflowExecution::CANCELLED, $this->execution()->status);
        $this->assertNull($this->execution()->waiting_event);
        $this->assertSame(0, app(WorkflowEventWaker::class)->wake('sms_received', 1));
    }

    public function test_the_prospect_events_are_reported_with_their_own_names(): void
    {
        $this->activate($this->eventWaitGraph());
        Queue::fake();
        $observer = new WorkflowEventObserver();

        $message = new Message();
        $message->forceFill(['prospect_id' => 1, 'from_user' => false]);
        $call = new Interaction();
        $call->forceFill(['prospect_id' => 1, 'from_user' => false]);
        $appointment = new Event();
        $appointment->forceFill(['prospect_id' => 1]);
        $order = new Order();
        $order->forceFill(['prospect_id' => 1]);

        foreach ([$message, $call, $appointment, $order] as $model) {
            $observer->created($model);
        }

        $events = [];
        Queue::assertPushed(WakeWorkflowExecutions::class, function ($job) use (&$events) {
            $events[] = $job->event;

            return true;
        });
        $this->assertSame(['message_received', 'call_received', 'appointment_created', 'order_created'], $events);

        // What the users do is not what a prospect does
        Queue::fake();
        $outgoing = new Message();
        $outgoing->forceFill(['prospect_id' => 1, 'from_user' => true]);
        $outgoingCall = new Interaction();
        $outgoingCall->forceFill(['prospect_id' => 1, 'from_user' => true]);
        $observer->created($outgoing);
        $observer->created($outgoingCall);
        Queue::assertNothingPushed();
    }

    public function test_nothing_is_queued_when_no_workflow_is_active(): void
    {
        Queue::fake();
        app(WorkflowTriggerResolver::class)->index(); // built once, then cached

        DB::enableQueryLog();
        (new WorkflowEventObserver())->created($this->incomingSms(1));
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(0, $queries);
        Queue::assertNothingPushed();
    }

    public function test_workflows_that_do_not_run_on_a_prospect_cannot_wait_for_an_event(): void
    {
        $graph = $this->eventWaitGraph();
        $graph['nodes'][0] = $this->trigger('event.created');

        $this->assertContains(
            "L'attente \"w\" (événement) n'est disponible que pour les workflows qui portent sur un prospect.",
            $this->errors($graph)
        );
    }

    public function test_an_event_wait_needs_a_known_event_a_timeout_and_both_ways(): void
    {
        $this->assertSame([], $this->errors($this->eventWaitGraph()));

        $this->assertContains(
            "L'attente \"w\" n'a pas d'événement valide à attendre.",
            $this->errors($this->eventWaitGraph(['event' => 'full_moon', 'hours' => 1]))
        );
        $this->assertContains(
            "L'attente \"w\" doit avoir un délai maximum (au moins une minute) : un workflow n'attend jamais indéfiniment.",
            $this->errors($this->eventWaitGraph(['event' => 'sms_received']))
        );

        $graph = $this->eventWaitGraph();
        unset($graph['edges'][2]['branch']);
        $this->assertContains(
            "L'attente \"w\" doit avoir une sortie OUI (l'événement a eu lieu) et une sortie NON (le délai est dépassé).",
            $this->errors($graph)
        );
    }

    public function test_waits_are_validated(): void
    {
        $wait = fn (array $config) => [
            'nodes' => [$this->trigger(), $this->node('w', 'wait', $config), $this->node('a', 'action', ['action' => 'mark-a'])],
            'edges' => [$this->edge('t', 'w'), $this->edge('w', 'a')],
        ];

        $this->assertSame([], $this->errors($wait(['until_time' => '09:00', 'days' => [1, 2, 3]])));
        $this->assertSame([], $this->errors($wait(['until_date' => '2026-12-24 08:00', 'timezone' => 'Europe/Paris'])));

        $this->assertNotEmpty($this->errors($wait(['until_time' => '25:00'])));
        $this->assertNotEmpty($this->errors($wait(['until_time' => '09:00', 'days' => [0, 8]])));
        $this->assertNotEmpty($this->errors($wait(['until_date' => 'when pigs fly'])));
        $this->assertNotEmpty($this->errors($wait(['until_time' => '09:00', 'timezone' => 'Mars/Phobos'])));
        $this->assertNotEmpty($this->errors($wait(['days' => 4000]))); // longer than the maximum
        $this->assertNotEmpty($this->errors($wait([])));
    }

    // CloudTalk

    protected function cloudTalkLine(array $config = null): void
    {
        DB::table('lines')->insert([
            'id' => 20, 'project_id' => 1, 'name' => 'Agent CT', 'operator' => 'cloudtalk', 'user_id' => 7,
            'config' => json_encode($config ?? ['api_key_id' => 'ID', 'api_key_secret' => 'SECRET-CT', 'agent_id' => 42, 'numero' => '+33700000000']),
        ]);
    }

    public function test_a_cloudtalk_line_is_offered_for_calls_and_sms_when_it_is_complete(): void
    {
        $this->cloudTalkLine();
        DB::table('lines')->insert([
            'id' => 21, 'project_id' => 1, 'name' => 'Only SMS', 'operator' => 'cloudtalk', 'user_id' => 7,
            'config' => json_encode(['numero' => '+33700000001']),
        ]);

        $registry = app(ProviderRegistry::class);

        $this->assertSame(['line:20'], array_column($registry->forProject(1, 'call'), 'ref'));
        $this->assertEqualsCanonicalizing(['line:20', 'line:21'], array_column($registry->forProject(1, 'sms'), 'ref'));
        $this->assertStringNotContainsString('SECRET-CT', json_encode(array_map([ProviderRegistry::class, 'public'], $registry->forProject(1))));
    }

    public function test_a_workflow_calls_through_cloudtalk_with_an_international_number(): void
    {
        $this->cloudTalkLine();
        $this->partialMock(CloudTalk::class, function ($mock) {
            $mock->shouldReceive('makeCall')
                ->once()
                ->withArgs(fn (Line $line, string $number) => $line->id === 20 && $number === '+33611223344')
                ->andReturn(['responseData' => ['data' => ['call_id' => 'ct-99']]]);
        });
        $this->app->forgetInstance(ActionRegistry::class); // rebuilt with the mocked service
        $this->activate([
            'nodes' => [$this->trigger(), $this->node('c', 'action', ['action' => 'make_call', 'provider' => 'line:20'])],
            'edges' => [$this->edge('t', 'c')],
        ]);

        $this->fire(1);

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $row = WorkflowNodeExecution::where('node_id', 'c')->first();
        $this->assertSame('line:20', $row->result['provider']);
        $this->assertSame('ct-99', $row->result['call_id']);
    }

    public function test_a_refused_cloudtalk_call_fails_the_step_with_the_message_of_cloudtalk(): void
    {
        $this->cloudTalkLine();
        $this->partialMock(CloudTalk::class, function ($mock) {
            $mock->shouldReceive('makeCall')->once()->andThrow(new \RuntimeException("L'agent CloudTalk n'est pas disponible."));
        });
        $this->app->forgetInstance(ActionRegistry::class); // rebuilt with the mocked service
        $this->activate([
            'nodes' => [$this->trigger(), $this->node('c', 'action', ['action' => 'make_call', 'provider' => 'line:20'])],
            'edges' => [$this->edge('t', 'c')],
        ]);

        $this->fire(1);

        $this->assertSame(WorkflowExecution::FAILED, $this->execution()->status);
        $this->assertSame("L'agent CloudTalk n'est pas disponible.", $this->execution()->error);
    }
}
