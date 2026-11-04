<?php

namespace Tests\Feature;

use App\Agents\AgentAccessException;
use App\Agents\AgentRunner;
use App\Agents\ApprovalService;
use App\Agents\AgentRequestException;
use App\Agents\Claude\ClaudeGatewayFactory;
use App\Models\Agent;
use App\Models\AgentAction;
use App\Models\AgentConversation;
use App\Models\AgentMessage;
use App\Models\AgentRun;
use App\Models\AgentRunStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\CrmFixtureTestCase;
use Tests\Support\DrivesAgents;
use Tests\Support\ScriptedGateway;
use Tests\Support\ScriptedGatewayFactory;

/**
 * What an agent DOES in the CRM, on the real tables (MySQL, rolled back) and
 * through the real routes of the CRM: AGENTS_TEST_DB=mysql.
 */
class AgentWriteToolsTest extends CrmFixtureTestCase
{
    use DrivesAgents;

    protected int $statusCategory;
    protected int $nouveau;
    protected int $nrp;

    protected function setUp(): void
    {
        parent::setUp();

        // The team adds the abilities of the interface that the fixture roles lack
        $this->grant('Télépro', ['all.prospect.event']);
        $this->statusCategory = DB::table('categories')->insertGetId(['project_id' => $this->pid, 'name' => 'Statut', 'for' => 'prospect', 'unique' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $this->nouveau = DB::table('labels')->insertGetId(['category_id' => $this->statusCategory, 'name' => 'Nouveau', 'created_at' => now(), 'updated_at' => now()]);
        $this->nrp = DB::table('labels')->insertGetId(['category_id' => $this->statusCategory, 'name' => 'NRP', 'created_at' => now(), 'updated_at' => now()]);
        // A label of the other project
        $strangerCategory = DB::table('categories')->insertGetId(['project_id' => $this->beta, 'name' => 'Statut', 'for' => 'prospect', 'created_at' => now(), 'updated_at' => now()]);
        $this->foreignLabel = DB::table('labels')->insertGetId(['category_id' => $strangerCategory, 'name' => 'Autre', 'created_at' => now(), 'updated_at' => now()]);

        // Fields of the project
        foreach ([['first_name', 0], ['last_name', 0], ['email', 0], ['city', 0], ['company_name', 0], ['source', 1]] as [$slug, $meta]) {
            DB::table('fields')->insert(['project_id' => $this->pid, 'slug' => $slug, 'name' => $slug, 'for' => 'prospect', 'meta' => $meta, 'type' => 'text', 'created_at' => now(), 'updated_at' => now()]);
        }

        // The CRM shows a team member only the categories and labels their role is given
        $role = DB::table('roles')->where('project_id', $this->pid)->where('name', 'Télépro')->value('id');
        DB::table('role_category')->insert(['role_id' => $role, 'category_id' => $this->statusCategory]);
        foreach ([$this->nouveau, $this->nrp] as $label) {
            DB::table('role_label')->insert(['role_id' => $role, 'label_id' => $label]);
        }

        config(['agents.max_iterations' => 8]);
    }

    protected int $foreignLabel;

    protected function grant(string $role, array $permissions): void
    {
        $roleId = DB::table('roles')->where('project_id', $this->pid)->where('name', $role)->value('id');

        foreach ($permissions as $name) {
            $id = DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => $name, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('role_has_permissions')->updateOrInsert(['permission_id' => $id, 'role_id' => $roleId]);
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // Helpers

    // Writing, within the rights

    public function test_an_agent_labels_prospects_through_the_routes_of_the_crm(): void
    {
        $run = $this->turn($this->agent('admin', Agent::AUTO), [
            ['prospect_set_labels', ['prospect_ids' => [$this->p['p1'], $this->p['p2']], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
        ]);

        $result = $this->results($run)['toolu_0'];

        $this->assertTrue($result['success']);
        $this->assertSame(['processed' => 2, 'updated' => 2, 'failed' => 0, 'skipped_processed' => 0], array_intersect_key($result, array_flip(['processed', 'updated', 'failed', 'skipped_processed'])));
        $this->assertSame([$this->nrp], $this->labelsOf($this->p['p1']));
        $this->assertSame([$this->nrp], $this->labelsOf($this->p['p2']));
        $this->assertSame([], $this->labelsOf($this->p['p3']));
        $this->assertSame(AgentRunStep::COMPLETED, AgentRunStep::where('tool_key', 'prospect_set_labels')->first()->status);
    }

    public function test_the_status_of_prospects_found_by_filter_is_changed_when_they_were_not_contacted(): void
    {
        // p1, p2 and p4 have been called by the team, p3 and p5 never
        $found = $this->search('admin', 'prospect', ['filters' => ['never_contacted' => true]]);
        $this->assertSame($this->expected('p3', 'p5'), $this->ids($found));
        // "contacted in the last day": nobody, so everybody qualifies
        $this->assertSame($this->expected('p1', 'p2', 'p3', 'p4', 'p5'), $this->ids($this->search('admin', 'prospect', ['filters' => ['not_contacted_since_days' => 1]])));
        // and nobody was contacted during the last 5 years but p3 and p5 never were
        $this->assertSame($this->expected('p3', 'p5'), $this->ids($this->search('admin', 'prospect', ['filters' => ['not_contacted_since_days' => 2000]])));

        $run = $this->turn($this->agent('admin', Agent::AUTO), [
            ['prospect_set_labels', ['filters' => ['never_contacted' => true], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
        ]);

        $result = $this->results($run)['toolu_0'];

        // p5 is processed: the CRM does not let it be modified, and it is reported
        $this->assertSame(1, $result['updated']);
        $this->assertSame(1, $result['skipped_processed']);
        $this->assertSame([$this->nrp], $this->labelsOf($this->p['p3']));
        $this->assertSame([], $this->labelsOf($this->p['p5']));
    }

    public function test_a_status_label_replaces_the_previous_one_like_in_the_interface(): void
    {
        DB::table('prospect_label')->insert(['prospect_id' => $this->p['p1'], 'label_id' => $this->nouveau, 'created_at' => now(), 'updated_at' => now()]);

        $this->turn($this->agent('admin', Agent::AUTO), [
            ['prospect_set_labels', ['prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
        ]);

        $this->assertContains($this->nrp, $this->labelsOf($this->p['p1']));
    }

    public function test_a_prospect_is_updated_with_known_fields_only(): void
    {
        $run = $this->turn($this->agent('admin', Agent::AUTO), [
            ['prospect_update', ['prospect_id' => $this->p['p1'], 'values' => ['city' => 'Nice', 'source' => 'Facebook']]],
            ['prospect_update', ['prospect_id' => $this->p['p1'], 'values' => ['password' => 'x']]],
            ['prospect_update', ['prospect_id' => $this->p['p5'], 'values' => ['city' => 'Rome']]],
        ]);

        $results = $this->results($run);
        $prospect = DB::table('prospects')->where('id', $this->p['p1'])->first();

        $this->assertTrue($results['toolu_0']['success']);
        $this->assertSame('Nice', $prospect->city);
        $this->assertSame('Facebook', json_decode($prospect->meta, true)['source']);

        $this->assertFalse($results['toolu_1']['success']);
        $this->assertSame('INVALID_INPUT', $results['toolu_1']['error']['code']);
        $this->assertStringContainsString('Champs possibles', $results['toolu_1']['error']['message']);

        // A processed prospect is locked by the CRM
        $this->assertFalse($results['toolu_2']['success']);
        $this->assertNotSame('Rome', DB::table('prospects')->where('id', $this->p['p5'])->value('city'));
    }

    public function test_a_prospect_is_created_in_the_project_of_the_run(): void
    {
        $run = $this->turn($this->agent('admin', Agent::AUTO), [
            ['prospect_create', ['values' => ['last_name' => 'Nouveau', 'company_name' => 'Zeta', 'city' => 'Lille']]],
        ]);

        $created = $this->results($run)['toolu_0'];
        $row = DB::table('prospects')->where('id', $created['prospect_id'])->first();

        $this->assertSame($this->pid, (int) $row->project_id);
        $this->assertSame($this->u['admin'], (int) $row->creator_id);
        $this->assertSame('Zeta', $row->company_name);
    }

    public function test_an_appointment_is_booked_in_a_calendar_of_the_project(): void
    {
        $agent = $this->agent('t1', Agent::AUTO, ['appointments']);
        $run = $this->turn($agent, [
            ['appointment_create', ['prospect_id' => $this->p['p1'], 'calendar_id' => $this->calendar, 'user_id' => $this->u['t1'], 'name' => 'Rappeler Jean', 'started_at' => '2026-12-01 10:00']],
            ['appointment_create', ['prospect_id' => $this->p['p1'], 'calendar_id' => 999999, 'user_id' => $this->u['t1'], 'name' => 'Mauvais calendrier', 'started_at' => '2026-12-01 10:00']],
            ['appointment_create', ['prospect_id' => $this->p['p1'], 'calendar_id' => $this->calendar, 'user_id' => $this->u['t1'], 'name' => 'Fin avant début', 'started_at' => '2026-12-01 10:00', 'ended_at' => '2026-12-01 09:00']],
        ]);

        $results = $this->results($run);
        $event = DB::table('events')->where('name', 'Rappeler Jean')->first();

        $this->assertTrue($results['toolu_0']['success']);
        $this->assertSame($this->u['t1'], (int) $event->user_id);
        $this->assertSame('2026-12-01 10:30:00', $event->ended_at);
        $this->assertFalse($results['toolu_1']['success']);
        $this->assertSame('NOT_FOUND', $results['toolu_1']['error']['code']);
        $this->assertFalse($results['toolu_2']['success']);
        $this->assertSame(0, DB::table('events')->whereIn('name', ['Mauvais calendrier', 'Fin avant début'])->count());
    }

    // Rights and isolation

    public function test_a_tool_the_owner_has_no_right_for_is_refused_and_not_even_listed(): void
    {
        // The télépro role cannot delete prospects
        $agent = $this->agent('t1', Agent::AUTO, ['prospect_management', 'prospect_deletion']);
        $gateway = new ScriptedGateway([ScriptedGateway::toolUse('prospect_delete', ['prospect_ids' => [$this->p['p1']]], 'toolu_0'), ScriptedGateway::text('ok')]);
        app()->instance(ClaudeGatewayFactory::class, new ScriptedGatewayFactory($gateway));

        $conversation = AgentConversation::create(['agent_id' => $agent->id, 'user_id' => $agent->user_id, 'project_id' => $this->pid]);
        AgentMessage::create(['conversation_id' => $conversation->id, 'role' => 'user', 'content' => [['type' => 'text', 'text' => 'Supprime']]]);
        $run = AgentRun::create(['conversation_id' => $conversation->id, 'agent_id' => $agent->id, 'user_id' => $agent->user_id, 'project_id' => $this->pid, 'status' => 'pending', 'model' => $agent->model]);
        app(AgentRunner::class)->run($run->id);

        $listed = array_column($gateway->requests[0]['tools'], 'name');

        $this->assertNotContains('prospect_delete', $listed);
        $this->assertContains('prospect_update', $listed);
        $result = $this->results($run->fresh())['toolu_0'];
        $this->assertFalse($result['success']);
        $this->assertSame('PERMISSION_DENIED', $result['error']['code']);
        $this->assertNull(DB::table('prospects')->where('id', $this->p['p1'])->value('deleted_at'));
    }

    public function test_an_agent_only_reaches_the_prospects_its_owner_can_see(): void
    {
        // t1 sees p1, p2, p3 only
        $run = $this->turn($this->agent('t1', Agent::AUTO), [
            ['prospect_set_labels', ['prospect_ids' => [$this->p['p1'], $this->p['p4'], $this->p['q1']], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
            ['prospect_update', ['prospect_id' => $this->p['p4'], 'values' => ['city' => 'Hack']]],
            ['prospect_update', ['prospect_id' => $this->p['q1'], 'values' => ['city' => 'Hack']]],
        ]);

        $results = $this->results($run);

        $this->assertSame(1, $results['toolu_0']['updated']);
        $this->assertSame([], $this->labelsOf($this->p['p4']));
        $this->assertSame([], $this->labelsOf($this->p['q1']));
        $this->assertSame('NOT_FOUND', $results['toolu_1']['error']['code']);
        $this->assertSame('NOT_FOUND', $results['toolu_2']['error']['code']);
        $this->assertNotSame('Hack', DB::table('prospects')->where('id', $this->p['q1'])->value('city'));
    }

    public function test_labels_groups_and_users_of_another_project_are_refused(): void
    {
        $run = $this->turn($this->agent('admin', Agent::AUTO), [
            ['prospect_set_labels', ['prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'label_ids' => [$this->foreignLabel]]],
            ['prospect_assign_users', ['prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'user_ids' => [$this->u['stranger']]]],
        ]);

        $results = $this->results($run);

        $this->assertSame('NOT_FOUND', $results['toolu_0']['error']['code']);
        $this->assertSame('NOT_FOUND', $results['toolu_1']['error']['code']);
        $this->assertSame([], $this->labelsOf($this->p['p1']));
        $this->assertSame(0, DB::table('prospect_user')->where('prospect_id', $this->p['p1'])->count());
    }

    public function test_an_empty_selection_is_never_everything(): void
    {
        $run = $this->turn($this->agent('admin', Agent::AUTO), [
            ['prospect_set_labels', ['action' => 'attach', 'label_ids' => [$this->nrp]]],
            ['prospect_set_labels', ['filters' => [], 'prospect_ids' => [], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
            ['prospect_set_labels', ['filters' => ['query' => 'dupont'], 'prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
        ]);

        foreach ($this->results($run) as $result) {
            $this->assertFalse($result['success']);
            $this->assertSame('INVALID_INPUT', $result['error']['code']);
        }

        $this->assertSame(0, DB::table('prospect_label')->where('label_id', $this->nrp)->count());
    }

    public function test_nothing_is_written_by_an_agent_in_read_only_mode(): void
    {
        $run = $this->turn($this->agent('admin', Agent::READ_ONLY), [
            ['prospect_set_labels', ['prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
        ]);

        $this->assertSame('PERMISSION_DENIED', $this->results($run)['toolu_0']['error']['code']);
        $this->assertSame([], $this->labelsOf($this->p['p1']));
    }

    // Confirmation

    public function test_a_proposing_agent_waits_for_the_human_and_the_confirmation_runs_it_on_the_server(): void
    {
        $agent = $this->agent('admin', Agent::PROPOSE);
        $run = $this->turn($agent, [['prospect_set_labels', ['prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'label_ids' => [$this->nrp]]]]);

        $this->assertSame('pending_approval', $this->results($run)['toolu_0']['status']);
        $this->assertSame([], $this->labelsOf($this->p['p1']));

        $action = AgentAction::first();
        $this->assertSame(AgentAction::PENDING, $action->status);
        $this->assertSame(1, $action->preview['prospects']);
        $this->assertStringContainsString('NRP', $action->preview['action']);

        // Another user cannot decide for the owner
        try {
            app(ApprovalService::class)->approve($action, User::find($this->u['t3']));
            $this->fail('Somebody else approved the action.');
        } catch (AgentAccessException) {
        }
        $this->assertSame([], $this->labelsOf($this->p['p1']));

        app(ApprovalService::class)->approve($action, User::find($this->u['admin']));

        $action->refresh();
        $this->assertSame(AgentAction::EXECUTED, $action->status);
        $this->assertSame(1, $action->result['updated']);
        $this->assertSame([$this->nrp], $this->labelsOf($this->p['p1']));

        // Pressing the button twice does not run it twice
        $this->expectException(AgentRequestException::class);
        app(ApprovalService::class)->approve($action, User::find($this->u['admin']));
    }

    public function test_a_refused_action_is_never_run(): void
    {
        $run = $this->turn($this->agent('admin', Agent::PROPOSE), [['prospect_set_labels', ['prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'label_ids' => [$this->nrp]]]]);
        $action = AgentAction::first();

        app(ApprovalService::class)->reject($action, User::find($this->u['admin']));

        $this->assertSame(AgentAction::REJECTED, $action->fresh()->status);
        $this->assertSame([], $this->labelsOf($this->p['p1']));

        $this->expectException(AgentRequestException::class);
        app(ApprovalService::class)->approve($action, User::find($this->u['admin']));
    }

    public function test_the_rights_are_checked_again_when_the_action_runs(): void
    {
        $this->turn($this->agent('t1', Agent::PROPOSE), [['prospect_set_labels', ['prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'label_ids' => [$this->nrp]]]]);
        $action = AgentAction::first();

        // Between the proposal and the decision, the owner loses the right
        DB::table('model_has_roles')->where('model_id', $this->u['t1'])->delete();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        app(ApprovalService::class)->approve($action, User::find($this->u['t1']));

        $action->refresh();
        $this->assertSame(AgentAction::FAILED, $action->status);
        $this->assertSame('PERMISSION_DENIED', $action->result['code']);
        $this->assertSame([], $this->labelsOf($this->p['p1']));
    }

    public function test_an_expired_action_cannot_be_approved(): void
    {
        $this->turn($this->agent('admin', Agent::PROPOSE), [['prospect_set_labels', ['prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'label_ids' => [$this->nrp]]]]);
        $action = AgentAction::first();
        DB::table('agent_actions')->where('id', $action->id)->update(['created_at' => now()->subHours(48)]);

        try {
            app(ApprovalService::class)->approve($action->fresh(), User::find($this->u['admin']));
            $this->fail('An expired action was approved.');
        } catch (AgentRequestException) {
        }

        $this->assertSame(AgentAction::EXPIRED, $action->fresh()->status);
        $this->assertSame([], $this->labelsOf($this->p['p1']));
    }

    public function test_a_deletion_always_needs_a_human_even_for_an_autonomous_agent(): void
    {
        $run = $this->turn($this->agent('admin', Agent::AUTO, ['prospect_management', 'prospect_deletion']), [
            ['prospect_delete', ['prospect_ids' => [$this->p['p3']]]],
        ]);

        $this->assertSame('pending_approval', $this->results($run)['toolu_0']['status']);
        $this->assertNull(DB::table('prospects')->where('id', $this->p['p3'])->value('deleted_at'));

        app(ApprovalService::class)->approve(AgentAction::first(), User::find($this->u['admin']));

        // Soft delete: the prospect is in the trash of the CRM
        $this->assertNotNull(DB::table('prospects')->where('id', $this->p['p3'])->value('deleted_at'));
        $this->assertSame(AgentAction::EXECUTED, AgentAction::first()->status);
    }

    public function test_an_action_that_reaches_many_prospects_waits_for_a_human_even_for_an_autonomous_agent(): void
    {
        config(['agents.bulk_confirm_threshold' => 2]);

        $run = $this->turn($this->agent('admin', Agent::AUTO), [
            ['prospect_set_labels', ['prospect_ids' => [$this->p['p1'], $this->p['p2']], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
            ['prospect_set_labels', ['filters' => ['processed' => false], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
        ]);

        $results = $this->results($run);

        // Two prospects: allowed, three or more: a human decides
        $this->assertTrue($results['toolu_0']['success']);
        $this->assertSame('pending_approval', $results['toolu_1']['status']);
        $this->assertSame(4, AgentAction::first()->preview['prospects']);
        $this->assertSame([], $this->labelsOf($this->p['p3']));
    }

    // Many records

    public function test_ten_thousand_prospects_are_processed_by_chunks_with_a_count_as_the_answer(): void
    {
        $now = date('Y-m-d H:i:s');
        $rows = [];

        for ($i = 0; $i < 10000; $i++) {
            $rows[] = ['project_id' => $this->pid, 'last_name' => "Lot$i", 'city' => 'Bulk', 'creator_id' => $this->u['admin'], 'created_at' => $now, 'updated_at' => $now];
        }

        foreach (array_chunk($rows, 2000) as $chunk) {
            DB::table('prospects')->insert($chunk);
        }

        config(['agents.bulk_chunk' => 1000]);

        // Count the internal calls
        $calls = 0;
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Routing\Events\RouteMatched::class, function ($event) use (&$calls) {
            if (str_ends_with($event->route->uri(), 'prospect/label/bulk')) {
                $calls++;
            }
        });

        $run = $this->turn($this->agent('admin', Agent::PROPOSE), [
            ['prospect_set_labels', ['filters' => ['city' => 'Bulk'], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
        ]);

        $action = AgentAction::first();
        $this->assertSame(10000, $action->preview['prospects']);
        $this->assertSame(0, DB::table('prospect_label')->where('label_id', $this->nrp)->count());

        app(ApprovalService::class)->approve($action, User::find($this->u['admin']));

        $action->refresh();
        $this->assertSame(AgentAction::EXECUTED, $action->status);
        $this->assertSame(['processed' => 10000, 'updated' => 10000, 'failed' => 0], array_intersect_key($action->result, array_flip(['processed', 'updated', 'failed'])));
        $this->assertSame(10000, DB::table('prospect_label')->where('label_id', $this->nrp)->whereNull('deleted_at')->count());
        // 10 requests of 1000, not 10 000 requests
        $this->assertSame(10, $calls);
    }

    public function test_a_selection_over_the_limit_is_refused_not_cut(): void
    {
        config(['agents.bulk_max' => 3, 'agents.bulk_confirm_threshold' => 1000]);

        $run = $this->turn($this->agent('admin', Agent::AUTO), [
            ['prospect_set_labels', ['filters' => ['processed' => false], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
        ]);

        $this->assertSame('LIMIT_EXCEEDED', $this->results($run)['toolu_0']['error']['code']);
        $this->assertSame(0, DB::table('prospect_label')->where('label_id', $this->nrp)->count());
    }

    // Audit and side effects

    public function test_every_call_and_every_decision_leaves_a_trace(): void
    {
        $run = $this->turn($this->agent('admin', Agent::PROPOSE), [
            ['crm_search', ['entity' => 'prospect']],
            ['prospect_set_labels', ['prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'label_ids' => [$this->nrp]]],
        ]);
        app(ApprovalService::class)->approve(AgentAction::first(), User::find($this->u['admin']));

        $steps = AgentRunStep::where('run_id', $run->id)->where('type', 'tool_call')->orderBy('seq')->get();

        $this->assertSame(['crm_search', 'prospect_set_labels', 'prospect_set_labels'], $steps->pluck('tool_key')->all());
        $this->assertSame(['allow', 'approve', 'approve'], $steps->pluck('permission_decision')->all());
        $this->assertSame(['completed', 'pending_approval', 'completed'], $steps->pluck('status')->all());
        $this->assertSame($this->u['admin'], (int) $steps[2]->input['decided_by']);
        $this->assertSame($this->u['admin'], (int) $run->user_id);
        $this->assertSame($this->pid, (int) $run->project_id);
        // The outcome is told to the agent, in the conversation
        $this->assertStringContainsString('EXÉCUTÉE', json_encode(AgentMessage::where('conversation_id', $run->conversation_id)->orderByDesc('id')->first()->content, JSON_UNESCAPED_UNICODE));
    }

    public function test_an_agent_is_not_a_visit_of_its_owner(): void
    {
        $before = DB::table('tracker_logs')->count();

        $this->turn($this->agent('admin', Agent::AUTO), [
            ['prospect_update', ['prospect_id' => $this->p['p1'], 'values' => ['city' => 'Nice']]],
        ]);

        $this->assertSame($before, DB::table('tracker_logs')->count());
    }

    public function test_the_internal_calls_leave_the_application_as_they_found_it(): void
    {
        $guard = auth()->getDefaultDriver();

        $this->turn($this->agent('admin', Agent::AUTO), [
            ['prospect_update', ['prospect_id' => $this->p['p1'], 'values' => ['city' => 'Nice']]],
        ]);

        $this->assertSame($guard, auth()->getDefaultDriver());
        $this->assertNull(auth()->user());
        $this->assertNull(getPermissionsTeamId());
    }

    public function test_a_message_cannot_be_sent_by_a_channel_the_project_does_not_have(): void
    {
        $agent = $this->agent('t1', Agent::AUTO, ['communication']);
        DB::table('prospects')->where('id', $this->p['p1'])->update(['mobile_phone_number' => '+33600000001', 'email' => 'jean@acme.test']);

        $run = $this->turn($agent, [['sms_send', ['prospect_id' => $this->p['p1'], 'provider' => 'sms:smsbox', 'message' => 'Bonjour']]]);

        // Even for an autonomous agent a message waits for a human; the channel is checked when it runs
        $this->assertSame('pending_approval', $this->results($run)['toolu_0']['status']);
        app(ApprovalService::class)->approve(AgentAction::first(), User::find($this->u['t1']));

        $action = AgentAction::first();
        $this->assertSame(AgentAction::FAILED, $action->status);
        $this->assertSame('NOT_FOUND', $action->result['code']);
        $this->assertSame(0, DB::table('sms')->where('prospect_id', $this->p['p1'])->where('message', 'Bonjour')->count());
    }

    public function test_the_project_configuration_gives_the_ids_the_other_tools_need(): void
    {
        $agent = $this->agent('admin', Agent::READ_ONLY, ['crm_exploration']);
        $run = $this->turn($agent, [['project_config', ['section' => 'labels']], ['project_config', []]]);

        $results = $this->results($run);
        $category = $results['toolu_0']['labels'][0];

        $this->assertSame('Statut', $category['category']);
        $this->assertTrue($category['one_label_per_prospect']);
        $this->assertEqualsCanonicalizing(['Nouveau', 'NRP'], array_column($category['labels'], 'name'));
        $this->assertNotContains('Autre', array_column($category['labels'], 'name'));
        $this->assertEqualsCanonicalizing(['labels', 'groups', 'calendars', 'fields', 'order_statuses', 'threads', 'messaging'], array_keys(array_diff_key($results['toolu_1'], ['success' => 1])));
        $this->assertSame('RDV Tel', $results['toolu_1']['calendars'][0]['name']);
    }
}
