<?php

namespace Tests\Feature;

use App\Agents\Claude\ClaudeGatewayFactory;
use App\Models\User;
use App\Services\Pappers;
use App\Services\PappersImmo;
use Illuminate\Support\Facades\DB;
use Tests\Support\CrmFixtureTestCase;
use Tests\Support\ScriptedGateway;
use Tests\Support\ScriptedGatewayFactory;

/**
 * The whole way of an action of an agent, through the HTTP kernel and the REAL routes of the CRM, on the real tables
 * (MySQL, rolled back): the model (scripted) proposes, a human approves with POST /api/agent/action/{id}/approve, the
 * server runs it, and what the CRM stored is read back.
 *
 *   AGENTS_TEST_DB=mysql php vendor/bin/phpunit --filter AgentApprovalEndToEndTest
 *
 * The assertions are on the rows of the test itself, never on whole tables.
 */
class AgentApprovalEndToEndTest extends CrmFixtureTestCase
{
    protected int $telepro;

    protected function setUp(): void
    {
        parent::setUp();

        $this->telepro = (int) DB::table('roles')->where('project_id', $this->pid)->where('name', 'Télépro')->value('id');
    }

    protected function as(string $who): static
    {
        return $this->actingAs(User::find($this->u[$who]), 'sanctum');
    }

    protected function slug(): string
    {
        return DB::table('projects')->where('id', $this->pid)->value('slug');
    }

    /**
     * The model proposes one call; returns the id of the pending action (or null when the tool refused it before asking).
     */
    protected function propose(array $skills, string $tool, array $input): ?int
    {
        $create = $this->as('admin')->postJson('/api/agent', ['name' => 'Lyra', 'instructions' => 'Aide-moi.', 'autonomy' => 'propose', 'skills' => $skills]);
        $create->assertStatus(201);

        app()->instance(ClaudeGatewayFactory::class, new ScriptedGatewayFactory(new ScriptedGateway([
            ScriptedGateway::toolUse($tool, $input, 'toolu_e2e'),
            ScriptedGateway::text('Terminé.'),
        ])));

        $ask = $this->as('admin')->postJson('/api/agent/' . $create->json('id') . '/ask', ['project' => $this->slug(), 'message' => 'Fais-le'])->assertStatus(202);
        $run = $this->as('admin')->getJson('/api/agent/run/' . $ask->json('run_id'))->json();

        return $run['pending_actions'][0]['id'] ?? null;
    }

    /** Approves as a human and returns the action once the server has run it */
    protected function approve(int $actionId): array
    {
        $this->as('admin')->postJson("/api/agent/action/$actionId/approve")->assertStatus(202);

        return $this->as('admin')->getJson("/api/agent/action/$actionId")->json();
    }

    public function test_a_prospect_in_the_trash_comes_back_after_the_approval(): void
    {
        $id = $this->makeProspect(['first_name' => 'Paul', 'last_name' => 'Supprimé', 'creator_id' => $this->u['admin'], 'deleted_at' => now()]);

        $action = $this->propose(['trash_restore'], 'trash_restore', ['type' => 'prospect', 'ids' => [$id]]);

        $this->assertNotNull($action);
        $this->assertNotNull(DB::table('prospects')->where('id', $id)->value('deleted_at'), 'Rien ne bouge avant la validation');

        $done = $this->approve($action);

        $this->assertSame('executed', $done['status']);
        $this->assertNull(DB::table('prospects')->where('id', $id)->value('deleted_at'));
    }

    public function test_a_campaign_is_turned_off_after_the_approval(): void
    {
        $campaign = DB::table('campaigns')->insertGetId([
            'project_id' => $this->pid, 'name' => 'Relance', 'for' => 'prospect', 'active' => 1, 'style' => '{}',
            'creator_id' => $this->u['admin'], 'created_at' => now(), 'updated_at' => now(),
        ]);

        $action = $this->propose(['campaign_control'], 'campaign_set_active', ['campaign_id' => $campaign, 'active' => false]);

        $this->assertSame(1, (int) DB::table('campaigns')->where('id', $campaign)->value('active'));
        $this->assertSame('executed', $this->approve($action)['status']);
        $this->assertSame(0, (int) DB::table('campaigns')->where('id', $campaign)->value('active'));
    }

    public function test_the_columns_of_an_import_are_linked_after_the_approval(): void
    {
        DB::table('fields')->insert(['project_id' => $this->pid, 'slug' => 'city', 'name' => 'Ville', 'for' => 'prospect', 'type' => 'text', 'meta' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $import = DB::table('imports')->insertGetId([
            'project_id' => $this->pid, 'name' => 'Salon', 'source' => 'file', 'path' => 'x/y.xlsx', 'token' => 'e2e' . uniqid(), 'rows_count' => 10, 'cols_count' => 2,
            'headers' => json_encode(['Nom', 'Ville']), 'mapping' => json_encode(['last_name', null]), 'creator_id' => $this->u['admin'], 'created_at' => now(), 'updated_at' => now(),
        ]);

        $action = $this->propose(['prospect_import'], 'import_configure', ['import_id' => $import, 'mapping' => [['column' => 1, 'target' => ['type' => 'field', 'slug' => 'city']]]]);

        $this->assertSame(['last_name', null], json_decode(DB::table('imports')->where('id', $import)->value('mapping'), true));
        $done = $this->approve($action);

        $this->assertSame('executed', $done['status']);
        $this->assertSame(['last_name', 'city'], json_decode(DB::table('imports')->where('id', $import)->value('mapping'), true));
    }

    public function test_a_role_is_given_and_a_user_taken_out_of_the_project_after_the_approvals(): void
    {
        $give = $this->propose(['team_management'], 'user_roles_set', ['action' => 'assign', 'user_ids' => [$this->u['bare']], 'role_ids' => [$this->telepro]]);
        $this->assertFalse(DB::table('model_has_roles')->where('model_id', $this->u['bare'])->where('role_id', $this->telepro)->exists());
        $this->assertSame('executed', $this->approve($give)['status']);
        $this->assertTrue(DB::table('model_has_roles')->where('model_id', $this->u['bare'])->where('role_id', $this->telepro)->where('project_id', $this->pid)->exists());

        $out = $this->propose(['team_management'], 'project_user_membership', ['action' => 'detach', 'user_ids' => [$this->u['bare']]]);
        $this->assertTrue(DB::table('user_project')->where('user_id', $this->u['bare'])->where('project_id', $this->pid)->exists());
        $this->assertSame('executed', $this->approve($out)['status']);
        $this->assertFalse(DB::table('user_project')->where('user_id', $this->u['bare'])->where('project_id', $this->pid)->exists());
    }

    public function test_a_request_that_will_be_refused_is_never_put_to_the_human(): void
    {
        // The creator of the project is never touched by an agent: nothing to approve
        $action = $this->propose(['team_management'], 'user_roles_set', ['action' => 'assign', 'user_ids' => [$this->u['admin']], 'role_ids' => [$this->telepro]]);

        $this->assertNull($action);
        $this->assertFalse(DB::table('model_has_roles')->where('model_id', $this->u['admin'])->where('role_id', $this->telepro)->exists());
    }

    public function test_a_waiting_execution_of_an_automation_is_cancelled_after_the_approval(): void
    {
        $workflow = DB::table('workflows')->insertGetId(['project_id' => $this->pid, 'name' => 'Relance J+2', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $version = DB::table('workflow_versions')->insertGetId(['workflow_id' => $workflow, 'version' => 1, 'trigger_type' => 'prospect.created', 'graph' => json_encode(['nodes' => [], 'edges' => []]), 'created_at' => now(), 'updated_at' => now()]);
        $execution = DB::table('workflow_executions')->insertGetId([
            'project_id' => $this->pid, 'workflow_id' => $workflow, 'workflow_version_id' => $version, 'subject_type' => 'prospect', 'subject_id' => $this->p['p1'],
            'status' => 'waiting', 'trigger_key' => 'e2e' . uniqid(), 'resume_at' => now()->addDay(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $action = $this->propose(['automation_control'], 'workflow_cancel_execution', ['execution_id' => $execution]);

        $this->assertSame('waiting', DB::table('workflow_executions')->where('id', $execution)->value('status'));
        $this->assertSame('executed', $this->approve($action)['status']);
        $this->assertSame('cancelled', DB::table('workflow_executions')->where('id', $execution)->value('status'));
    }

    public function test_an_owner_search_with_pappers_adds_a_prospect_only_after_the_approval_and_spends_nothing_here(): void
    {
        // Nothing leaves the machine: the two services of Pappers are replaced
        putenv('PAPPERS_TOKEN=dummy');
        $_ENV['PAPPERS_TOKEN'] = $_SERVER['PAPPERS_TOKEN'] = 'dummy';
        $this->app->bind(PappersImmo::class, fn () => new class extends PappersImmo {
            public function getPlots($params = [])
            {
                return ['resultats' => [['numero' => '0042', 'adresse' => '12 rue Test', 'code_commune' => '69383', 'commune' => 'Lyon 3e',
                    'proprietaires' => [['siren' => '555444333', 'nom_entreprise' => 'SCI Fictive', 'personnes_physiques' => []]]]]];
            }
        });
        $this->app->bind(Pappers::class, fn () => new class extends Pappers {
            public function showCompany($siren, $params = [])
            {
                return ['telephone' => '+33400000000', 'email' => 'contact@sci-fictive.test'];
            }
        });

        try {
            $action = $this->propose(['pappers_prospecting'], 'pappers_search', ['filters' => ['code_postal' => '69003'], 'par_page' => 5]);

            $this->assertNotNull($action);
            $this->assertSame(0, DB::table('prospects')->where('project_id', $this->pid)->where('meta', 'like', '%555444333%')->count());

            $done = $this->approve($action);

            $this->assertSame('executed', $done['status']);
            $this->assertSame(1, $done['result']['prospects_added']);
            $this->assertSame(1, DB::table('prospects')->where('project_id', $this->pid)->where('meta', 'like', '%555444333%')->count());
        } finally {
            putenv('PAPPERS_TOKEN');
            unset($_ENV['PAPPERS_TOKEN'], $_SERVER['PAPPERS_TOKEN']);
        }
    }

    public function test_someone_else_cannot_approve_the_action_of_the_owner(): void
    {
        $id = $this->makeProspect(['first_name' => 'Zoé', 'last_name' => 'Supprimée', 'creator_id' => $this->u['admin'], 'deleted_at' => now()]);
        $action = $this->propose(['trash_restore'], 'trash_restore', ['type' => 'prospect', 'ids' => [$id]]);

        $this->as('t1')->postJson("/api/agent/action/$action/approve")->assertNotFound();

        $this->assertNotNull(DB::table('prospects')->where('id', $id)->value('deleted_at'));
    }
}
