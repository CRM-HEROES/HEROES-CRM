<?php

namespace Tests\Feature;

use App\Agents\ApprovalService;
use App\Jobs\ExportProspects;
use App\Models\Agent;
use App\Models\AgentAction;
use App\Models\Message;
use App\Models\Sms;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Support\CrmFixtureTestCase;
use Tests\Support\DrivesAgents;

/**
 * Mass messages, exports, e-mails read, workflows built by an agent, deletions:
 * on the real tables (MySQL, rolled back): AGENTS_TEST_DB=mysql.
 */
class AgentBatchThreeTest extends CrmFixtureTestCase
{
    use DrivesAgents;

    protected function setUp(): void
    {
        parent::setUp();

        // No real SMS or e-mail may leave: the observers that send are off for these tests
        Sms::unsetEventDispatcher();
        Message::unsetEventDispatcher();

        DB::table('project_settings')->insert(['project_id' => $this->pid, 'key' => 'smsbox', 'value' => json_encode(['token' => 'x']), 'created_at' => now(), 'updated_at' => now()]);
        config(['agents.max_iterations' => 10]);
    }

    protected function tearDown(): void
    {
        Sms::setEventDispatcher(app('events'));
        Message::setEventDispatcher(app('events'));

        parent::tearDown();
    }

    protected function approve(string $who = 'admin'): AgentAction
    {
        $action = AgentAction::orderByDesc('id')->first();
        app(ApprovalService::class)->approve($action, User::find($this->u[$who]));

        return $action->fresh();
    }

    protected function skills(): array
    {
        return ['crm_exploration', 'mass_communication', 'workflow_builder', 'order_cleanup'];
    }

    // Mass messages

    public function test_a_sms_to_many_prospects_waits_for_a_human_who_sees_the_number_of_recipients(): void
    {
        $run = $this->turn($this->agent('admin', Agent::AUTO, $this->skills()), [
            ['sms_send_bulk', ['filters' => ['processed' => false], 'provider' => 'sms:smsbox', 'message' => 'Bonjour, votre devis est prêt.']],
        ]);

        $this->assertSame('pending_approval', $this->results($run)['toolu_0']['status']);
        $action = AgentAction::first();
        $this->assertSame(4, $action->preview['prospects']);
        $this->assertStringContainsString('votre devis est prêt', $action->preview['action']);
        $this->assertSame(0, DB::table('sms')->where('message', 'Bonjour, votre devis est prêt.')->count());

        $done = $this->approve();

        $this->assertSame(AgentAction::EXECUTED, $done->status);
        $this->assertSame(4, $done->result['updated']);
        $rows = DB::table('sms')->where('message', 'Bonjour, votre devis est prêt.')->get();
        $this->assertCount(4, $rows);
        $this->assertSame(['smsbox'], $rows->pluck('source')->unique()->values()->all());
        $this->assertSame([1], $rows->pluck('from_user')->map(fn ($v) => (int) $v)->unique()->values()->all());
    }

    public function test_a_mass_sms_only_reaches_the_prospects_its_owner_can_see_and_uses_a_known_channel(): void
    {
        // t1 sees p1, p2, p3
        $this->turn($this->agent('t1', Agent::AUTO, $this->skills()), [
            ['sms_send_bulk', ['prospect_ids' => [$this->p['p1'], $this->p['p2'], $this->p['p4'], $this->p['q1']], 'provider' => 'sms:smsbox', 'message' => 'Test']],
            ['sms_send_bulk', ['prospect_ids' => [$this->p['p1']], 'provider' => 'sms:inconnu', 'message' => 'Test 2']],
        ]);

        $first = AgentAction::orderBy('id')->first();
        // p4 and the other project are out of reach, p1 and p2 are t1's
        $this->assertSame(2, $first->preview['prospects']);

        app(ApprovalService::class)->approve($first, User::find($this->u['t1']));
        app(ApprovalService::class)->approve(AgentAction::orderByDesc('id')->first(), User::find($this->u['t1']));

        // The CRM sends a mass SMS only to the prospects assigned to a user who cannot see them all: p2 only
        $this->assertSame([$this->p['p2']], DB::table('sms')->where('message', 'Test')->pluck('prospect_id')->map(fn ($v) => (int) $v)->all());
        $this->assertSame(1, $first->fresh()->result['updated']);
        $this->assertSame(1, $first->fresh()->result['not_applied_by_crm']);
        $this->assertSame(AgentAction::FAILED, AgentAction::orderByDesc('id')->first()->status);
        $this->assertSame('NOT_FOUND', AgentAction::orderByDesc('id')->first()->result['code']);
        $this->assertSame(0, DB::table('sms')->where('message', 'Test 2')->count());
    }

    public function test_a_mass_e_mail_without_configured_sending_fails_cleanly(): void
    {
        $this->turn($this->agent('admin', Agent::AUTO, $this->skills()), [
            ['email_send_bulk', ['prospect_ids' => [$this->p['p1']], 'subject' => 'Offre', 'body' => '<p>Bonjour</p>']],
        ]);

        $done = $this->approve();

        // The CRM refuses to send without e-mail settings: nothing goes out
        $this->assertSame(AgentAction::FAILED, $done->status);
        $this->assertSame(0, DB::table('messages')->where('prospect_id', $this->p['p1'])->where('plain_text', 'like', '%Bonjour%')->count());
    }

    // Export

    public function test_an_export_goes_to_the_owner_only_and_never_beyond_what_the_owner_sees(): void
    {
        Queue::fake([ExportProspects::class]);
        DB::table('role_has_permissions')->insert([
            'permission_id' => DB::table('permissions')->where('name', 'all.prospect.export')->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => 'all.prospect.export', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]),
            'role_id' => DB::table('roles')->where('project_id', $this->pid)->where('name', 'Télépro')->value('id'),
        ]);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->turn($this->agent('t1', Agent::AUTO, $this->skills()), [
            ['export_prospects', ['filters' => ['processed' => false]]],
        ]);

        $action = AgentAction::first();
        // t1 sees p1, p2, p3
        $this->assertSame(3, $action->preview['prospects']);
        $this->assertStringContainsString(User::find($this->u['t1'])->email, $action->preview['action']);

        $done = $this->approve('t1');

        $this->assertSame(AgentAction::EXECUTED, $done->status);
        $export = DB::table('exports')->where('project_id', $this->pid)->orderByDesc('id')->first();
        $this->assertSame(User::find($this->u['t1'])->email, $export->email);
        $ids = json_decode($export->filters, true)['ids'];
        sort($ids);
        $this->assertSame([$this->p['p1'], $this->p['p2'], $this->p['p3']], $ids);
        Queue::assertPushed(ExportProspects::class);
    }

    public function test_an_owner_without_the_export_right_cannot_export(): void
    {
        $run = $this->turn($this->agent('t1', Agent::AUTO, $this->skills()), [['export_prospects', ['filters' => ['processed' => false]]]]);

        $this->assertSame('PERMISSION_DENIED', $this->results($run)['toolu_0']['error']['code']);
        $this->assertSame(0, AgentAction::count());
    }

    // E-mails read

    public function test_the_e_mails_of_the_prospects_are_searched_in_the_threads_the_owner_can_reach(): void
    {
        $thread = DB::table('threads')->insertGetId(['project_id' => $this->pid, 'name' => 'Relances', 'slug' => 'relances', 'private' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $foreign = DB::table('threads')->insertGetId(['project_id' => $this->beta, 'name' => 'Etranger', 'slug' => 'etranger', 'private' => 0, 'created_at' => now(), 'updated_at' => now()]);

        foreach ([
            [$thread, 'p1', 1, 'Voici notre offre de fenêtres.'],
            [$thread, 'p1', 0, 'Merci, je souhaite un devis.'],
            [$thread, 'p4', 1, 'Message sur une fiche non visible par t1.'],
            [$foreign, 'q1', 1, 'Autre projet.'],
        ] as [$threadId, $prospect, $fromUser, $text]) {
            DB::table('messages')->insert(['thread_id' => $threadId, 'prospect_id' => $this->p[$prospect], 'body' => "<p>$text</p>", 'plain_text' => $text, 'from_user' => $fromUser, 'sent' => 1, 'creator_id' => $this->u['admin'], 'created_at' => now(), 'updated_at' => now()]);
        }

        $all = $this->search('admin', 'email', ['fields' => ['thread', 'direction', 'preview']]);
        $received = $this->search('admin', 'email', ['filters' => ['direction' => 'received']]);
        $query = $this->search('admin', 'email', ['filters' => ['query' => 'devis']]);

        $this->assertSame(3, $all['total']);
        $this->assertNotContains('Autre projet.', array_column($all['rows'], 'preview'));
        $this->assertSame(['Relances'], array_values(array_unique(array_column($all['rows'], 'thread'))));
        $this->assertSame(1, $received['total']);
        $this->assertSame(1, $query['total']);

        // Without the right to read the threads, nothing
        $this->expectException(\App\Agents\Entities\AgentReadException::class);
        $this->search('t1', 'email');
    }

    // Workflows

    public function test_an_agent_builds_a_workflow_from_the_catalog_fixes_it_and_publishes_it_without_starting_it(): void
    {
        $good = [
            'nodes' => [
                ['id' => 'trigger', 'type' => 'trigger', 'config' => ['trigger' => 'prospect.created']],
                ['id' => 'w1', 'type' => 'wait', 'config' => ['days' => 1]],
                ['id' => 'end1', 'type' => 'end', 'config' => []],
            ],
            'edges' => [['from' => 'trigger', 'to' => 'w1'], ['from' => 'w1', 'to' => 'end1']],
        ];
        $broken = $good;
        $broken['edges'][1] = ['from' => 'w1', 'to' => 'nulle-part'];

        $run = $this->turn($this->agent('admin', Agent::AUTO, $this->skills()), [
            ['workflow_catalog', []],
            ['workflow_save_draft', ['name' => 'Relance J+1', 'graph' => $broken]],
        ]);

        $results = $this->results($run);
        $this->assertArrayHasKey('graph_format', $results['toolu_0']);
        $this->assertContains('send_sms', array_column($results['toolu_0']['catalog']['actions'], 'type'));
        $this->assertContains('sms:smsbox', array_column($results['toolu_0']['operators'], 'ref'));

        $draft = $results['toolu_1'];
        $this->assertTrue($draft['success']);
        $this->assertFalse($draft['ready_to_publish']);
        $this->assertNotEmpty($draft['problems']);
        $id = $draft['workflow_id'];
        $this->assertSame('draft', DB::table('workflows')->where('id', $id)->value('status'));

        // Corrected, then published
        $run = $this->turn($this->agent('admin', Agent::AUTO, $this->skills()), [
            ['workflow_save_draft', ['workflow_id' => $id, 'graph' => $good]],
            ['workflow_publish', ['workflow_id' => $id]],
        ]);

        $results = $this->results($run);
        $this->assertTrue($results['toolu_0']['ready_to_publish']);
        $this->assertSame([], $results['toolu_0']['problems']);
        $this->assertTrue($results['toolu_1']['success']);
        $this->assertSame(1, DB::table('workflow_versions')->where('workflow_id', $id)->count());
        // Published is not running
        $this->assertNotSame('active', DB::table('workflows')->where('id', $id)->value('status'));
    }

    public function test_a_workflow_of_another_project_cannot_be_edited_or_deleted(): void
    {
        $other = DB::table('workflows')->insertGetId(['project_id' => $this->beta, 'name' => 'Autre', 'status' => 'draft', 'created_at' => now(), 'updated_at' => now()]);

        $run = $this->turn($this->agent('admin', Agent::AUTO, $this->skills()), [
            ['workflow_save_draft', ['workflow_id' => $other, 'name' => 'Pris']],
            ['workflow_delete', ['workflow_id' => $other]],
        ]);

        $this->assertSame('NOT_FOUND', $this->results($run)['toolu_0']['error']['code']);
        $done = $this->approve();
        $this->assertSame(AgentAction::FAILED, $done->status);
        $this->assertSame('Autre', DB::table('workflows')->where('id', $other)->value('name'));
        $this->assertNull(DB::table('workflows')->where('id', $other)->value('deleted_at'));
    }

    public function test_a_workflow_is_deleted_only_after_the_human_approves(): void
    {
        $workflow = DB::table('workflows')->insertGetId(['project_id' => $this->pid, 'name' => 'À supprimer', 'status' => 'draft', 'created_at' => now(), 'updated_at' => now()]);

        $this->turn($this->agent('admin', Agent::AUTO, $this->skills()), [['workflow_delete', ['workflow_id' => $workflow]]]);

        $this->assertNull(DB::table('workflows')->where('id', $workflow)->value('deleted_at'));
        $this->assertSame('À supprimer', AgentAction::first()->preview['workflow']);

        $this->approve();
        $this->assertNotNull(DB::table('workflows')->where('id', $workflow)->value('deleted_at'));
    }

    // Deletions

    public function test_an_order_is_deleted_only_after_the_human_approves(): void
    {
        $order = DB::table('orders')->where('prospect_id', $this->p['p1'])->value('id');

        $this->turn($this->agent('t1', Agent::AUTO, $this->skills()), [['order_delete', ['prospect_id' => $this->p['p1'], 'order_id' => $order]]]);
        $this->assertNull(DB::table('orders')->where('id', $order)->value('deleted_at'));

        $done = $this->approve('t1');

        $this->assertSame(AgentAction::EXECUTED, $done->status);
        $this->assertNotNull(DB::table('orders')->where('id', $order)->value('deleted_at'));
    }
}
