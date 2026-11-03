<?php

namespace Tests\Feature;

use App\Agents\ApprovalService;
use App\Models\Agent;
use App\Models\AgentAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\CrmFixtureTestCase;
use Tests\Support\DrivesAgents;

/**
 * History, appointments, calls, orders, workflows, duplicates: the second
 * batch of tools, on the real tables (MySQL, rolled back): AGENTS_TEST_DB=mysql.
 */
class AgentExtendedToolsTest extends CrmFixtureTestCase
{
    use DrivesAgents;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['first_name', 'last_name', 'email'] as $slug) {
            DB::table('fields')->insert(['project_id' => $this->pid, 'slug' => $slug, 'name' => $slug, 'for' => 'prospect', 'meta' => 0, 'type' => 'text', 'searchable' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }

        config(['agents.max_iterations' => 10, 'agents.bulk_confirm_threshold' => 100]);
    }

    protected function approve(string $who = 'admin'): AgentAction
    {
        $action = AgentAction::orderByDesc('id')->first();
        app(ApprovalService::class)->approve($action, User::find($this->u[$who]));

        return $action->fresh();
    }

    protected function skills(): array
    {
        return ['crm_exploration', 'call_follow_up', 'data_quality', 'automation_control', 'appointments'];
    }

    // History

    public function test_the_history_of_a_prospect_is_read_without_personal_data(): void
    {
        $run = $this->turn($this->agent('admin', Agent::READ_ONLY, $this->skills()), [
            ['prospect_history', ['prospect_id' => $this->p['p1']]],
            ['prospect_history', ['prospect_id' => $this->p['p1'], 'type' => 'sms', 'limit' => 5]],
            ['prospect_history', ['prospect_id' => $this->p['q1']]],
        ]);

        $results = $this->results($run);
        $all = $results['toolu_0'];

        $this->assertTrue($all['success']);
        $this->assertGreaterThanOrEqual(4, $all['total']);
        $this->assertNotEmpty($all['entries']);
        $this->assertStringNotContainsString('+33600000000', json_encode($all));
        $this->assertTrue($results['toolu_1']['success']);
        $this->assertLessThan($all['total'], $results['toolu_1']['total']);
        // A prospect of another project does not exist for this agent
        $this->assertSame('NOT_FOUND', $results['toolu_2']['error']['code']);
    }

    public function test_the_history_of_a_prospect_the_owner_cannot_see_is_refused(): void
    {
        $run = $this->turn($this->agent('t1', Agent::READ_ONLY, $this->skills()), [['prospect_history', ['prospect_id' => $this->p['p4']]]]);

        $this->assertSame('NOT_FOUND', $this->results($run)['toolu_0']['error']['code']);
    }

    // Workflows and campaigns

    public function test_workflows_and_campaigns_are_listed_for_who_may_see_them(): void
    {
        DB::table('workflows')->insert(['project_id' => $this->pid, 'name' => 'Relance NRP', 'status' => 'draft', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('workflows')->insert(['project_id' => $this->beta, 'name' => 'Autre projet', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('campaigns')->insert(['project_id' => $this->pid, 'name' => 'Camp A', 'active' => 1, 'frequency' => 'daily', 'for' => 'prospect', 'date' => now(), 'style' => '{}', 'created_at' => now(), 'updated_at' => now()]);

        $workflows = $this->search('admin', 'workflow');
        $campaigns = $this->search('admin', 'campaign', ['filters' => ['active' => true]]);

        $this->assertSame(['Relance NRP'], array_column($workflows['rows'], 'name'));
        $this->assertSame('draft', $workflows['rows'][0]['status']);
        $this->assertSame(['Camp A'], array_column($campaigns['rows'], 'name'));
        $this->assertTrue($campaigns['rows'][0]['active']);

        // A member without the right does not see them
        $this->expectException(\App\Agents\Entities\AgentReadException::class);
        $this->search('bare', 'workflow');
    }

    public function test_a_workflow_that_cannot_be_activated_stays_as_it_is_and_the_reason_is_given(): void
    {
        $workflow = DB::table('workflows')->insertGetId(['project_id' => $this->pid, 'name' => 'Vide', 'status' => 'draft', 'created_at' => now(), 'updated_at' => now()]);

        $run = $this->turn($this->agent('admin', Agent::AUTO, $this->skills()), [['workflow_set_status', ['workflow_id' => $workflow, 'status' => 'active']]]);

        // Always decided by a human: it may contact many prospects
        $this->assertSame('pending_approval', $this->results($run)['toolu_0']['status']);
        $this->assertSame('Vide', AgentAction::first()->preview['workflow']);

        $action = $this->approve();

        $this->assertSame(AgentAction::FAILED, $action->status);
        $this->assertSame('VALIDATION_FAILED', $action->result['code']);
        $this->assertSame('draft', DB::table('workflows')->where('id', $workflow)->value('status'));
    }

    public function test_a_workflow_of_another_project_is_not_reachable(): void
    {
        $other = DB::table('workflows')->insertGetId(['project_id' => $this->beta, 'name' => 'Autre', 'status' => 'paused', 'created_at' => now(), 'updated_at' => now()]);

        $this->turn($this->agent('admin', Agent::AUTO, $this->skills()), [['workflow_set_status', ['workflow_id' => $other, 'status' => 'active']]]);
        $action = $this->approve();

        $this->assertSame(AgentAction::FAILED, $action->status);
        $this->assertSame('NOT_FOUND', $action->result['code']);
        $this->assertSame('paused', DB::table('workflows')->where('id', $other)->value('status'));
    }

    // Appointments

    public function test_an_appointment_is_changed_and_a_deletion_is_decided_by_a_human(): void
    {
        $event = DB::table('events')->where('name', 'RDV Dupont')->value('id');

        $run = $this->turn($this->agent('t1', Agent::AUTO, $this->skills()), [
            ['appointment_update', ['prospect_id' => $this->p['p1'], 'event_id' => $event, 'name' => 'RDV Dupont (déplacé)', 'started_at' => '2026-12-05 14:00', 'done' => false]],
            ['appointment_update', ['prospect_id' => $this->p['p1'], 'event_id' => $event]],
            ['appointment_update', ['prospect_id' => $this->p['p1'], 'event_id' => $event, 'started_at' => 'pas une date']],
            ['appointment_delete', ['prospect_id' => $this->p['p1'], 'event_id' => $event]],
        ]);

        $results = $this->results($run);
        $row = DB::table('events')->where('id', $event)->first();

        $this->assertTrue($results['toolu_0']['success']);
        $this->assertSame('RDV Dupont (déplacé)', $row->name);
        $this->assertSame('2026-12-05 14:00:00', $row->started_at);
        $this->assertSame('INVALID_INPUT', $results['toolu_1']['error']['code']);
        $this->assertSame('INVALID_INPUT', $results['toolu_2']['error']['code']);
        $this->assertSame('pending_approval', $results['toolu_3']['status']);
        $this->assertNull($row->deleted_at);

        $this->approve('t1');
        $this->assertNotNull(DB::table('events')->where('id', $event)->value('deleted_at'));
    }

    // Calls

    public function test_a_call_is_logged_with_its_note_and_a_bad_result_is_refused(): void
    {
        $run = $this->turn($this->agent('t1', Agent::AUTO, $this->skills()), [
            ['interaction_log', ['prospect_id' => $this->p['p1'], 'status' => 'no_answer', 'started_at' => '2026-11-03 09:30', 'duration_seconds' => 45, 'note' => 'Répondeur, à rappeler demain']],
            ['interaction_log', ['prospect_id' => $this->p['p1'], 'status' => 'super']],
            ['interaction_log', ['prospect_id' => $this->p['p4'], 'status' => 'answered']],
        ]);

        $results = $this->results($run);
        $call = DB::table('interactions')->where('id', $results['toolu_0']['interaction_id'])->first();

        $this->assertTrue($results['toolu_0']['success']);
        $this->assertSame('manual', $call->source);
        $this->assertSame('no_answer', $call->status);
        $this->assertSame($this->u['t1'], (int) $call->creator_id);
        $this->assertSame('2026-11-03 09:30:45', $call->ended_at);
        $this->assertSame('Répondeur, à rappeler demain', json_decode($call->data, true)['note']);
        $this->assertSame('INVALID_INPUT', $results['toolu_1']['error']['code']);
        // p4 is not one of t1's prospects
        $this->assertSame('NOT_FOUND', $results['toolu_2']['error']['code']);

        // And it shows in the history of the prospect
        $history = $this->results($this->turn($this->agent('t1', Agent::READ_ONLY, $this->skills()), [['prospect_history', ['prospect_id' => $this->p['p1'], 'type' => 'interaction']]]));
        $this->assertStringContainsString('Répondeur', json_encode($history, JSON_UNESCAPED_UNICODE));
    }

    // Orders

    public function test_an_order_is_created_and_its_status_changed_within_the_project(): void
    {
        $status = DB::table('order_status')->where('project_id', $this->pid)->value('id');
        $foreignStatus = DB::table('order_status')->insertGetId(['project_id' => $this->beta, 'name' => 'Etranger', 'created_at' => now(), 'updated_at' => now()]);
        $order = DB::table('orders')->where('prospect_id', $this->p['p1'])->value('id');

        $run = $this->turn($this->agent('t1', Agent::AUTO, $this->skills()), [
            ['order_create', ['prospect_id' => $this->p['p1'], 'description' => 'Devis fenêtres', 'status_id' => $status]],
            ['order_create', ['prospect_id' => $this->p['p1'], 'description' => 'Statut étranger', 'status_id' => $foreignStatus]],
            ['order_update', ['prospect_id' => $this->p['p1'], 'order_id' => $order, 'description' => 'Devis modifié']],
            ['order_update', ['prospect_id' => $this->p['p1'], 'order_id' => $order]],
        ]);

        $results = $this->results($run);

        $this->assertTrue($results['toolu_0']['success']);
        $this->assertSame($this->p['p1'], (int) DB::table('orders')->where('id', $results['toolu_0']['order_id'])->value('prospect_id'));
        $this->assertSame('NOT_FOUND', $results['toolu_1']['error']['code']);
        $this->assertSame(0, DB::table('orders')->where('description', 'Statut étranger')->count());
        $this->assertTrue($results['toolu_2']['success']);
        $this->assertSame('Devis modifié', DB::table('orders')->where('id', $order)->value('description'));
        $this->assertSame('INVALID_INPUT', $results['toolu_3']['error']['code']);
    }

    // Duplicates

    public function test_duplicates_are_detected_listed_and_removed_only_for_the_prospects_named(): void
    {
        DB::table('prospects')->whereIn('id', [$this->p['p1'], $this->p['p3']])->update(['email' => 'meme@doublon.test']);

        $run = $this->turn($this->agent('admin', Agent::AUTO, $this->skills()), [
            ['duplicates_detect', ['fields' => ['email']]],
            ['duplicates_detect', ['fields' => ['mot_de_passe']]],
        ]);

        $results = $this->results($run);
        $this->assertTrue($results['toolu_0']['success']);
        $this->assertSame('INVALID_INPUT', $results['toolu_1']['error']['code']);

        $found = $this->search('admin', 'prospect', ['filters' => ['is_duplicate' => true]]);
        $this->assertNotEmpty($found['rows'], 'The detection marked no duplicate.');
        $duplicateId = $found['rows'][0]['id'];

        // An empty list would mean "every duplicate" for the CRM: refused
        $run = $this->turn($this->agent('admin', Agent::AUTO, $this->skills()), [
            ['duplicates_resolve', ['action' => 'delete_listed', 'prospect_ids' => []]],
            ['duplicates_resolve', ['action' => 'delete_listed', 'prospect_ids' => [$this->p['q1']]]],
        ]);
        $this->assertSame(2, AgentAction::count());
        app(ApprovalService::class)->approve(AgentAction::orderBy('id')->first(), User::find($this->u['admin']));
        app(ApprovalService::class)->approve(AgentAction::orderByDesc('id')->first(), User::find($this->u['admin']));

        foreach (AgentAction::all() as $action) {
            $this->assertSame(AgentAction::FAILED, $action->status);
        }
        $this->assertNull(DB::table('prospects')->where('id', $this->p['q1'])->value('deleted_at'));

        // For the prospect named, with a human approval
        $this->turn($this->agent('admin', Agent::AUTO, $this->skills()), [['duplicates_resolve', ['action' => 'delete_listed', 'prospect_ids' => [$duplicateId]]]]);
        $this->assertSame(AgentAction::PENDING, AgentAction::orderByDesc('id')->first()->status);
        $this->assertNull(DB::table('prospects')->where('id', $duplicateId)->value('deleted_at'));

        $action = $this->approve();
        $this->assertSame(AgentAction::EXECUTED, $action->status);
        $this->assertNotNull(DB::table('prospects')->where('id', $duplicateId)->value('deleted_at'));
    }
}
