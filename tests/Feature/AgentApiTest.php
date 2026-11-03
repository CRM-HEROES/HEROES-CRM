<?php

namespace Tests\Feature;

use App\Agents\Claude\ClaudeGatewayFactory;
use App\Models\Agent;
use App\Models\AgentAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\CrmFixtureTestCase;
use Tests\Support\ScriptedGateway;
use Tests\Support\ScriptedGatewayFactory;

/**
 * The HTTP API of the agents (create, talk, approve), through the whole
 * kernel, on the real tables (MySQL, rolled back): AGENTS_TEST_DB=mysql.
 */
class AgentApiTest extends CrmFixtureTestCase
{
    protected int $nrp;

    protected function setUp(): void
    {
        parent::setUp();

        $category = DB::table('categories')->insertGetId(['project_id' => $this->pid, 'name' => 'Statut', 'for' => 'prospect', 'unique' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $this->nrp = DB::table('labels')->insertGetId(['category_id' => $category, 'name' => 'NRP', 'created_at' => now(), 'updated_at' => now()]);
    }

    protected function as(string $who): static
    {
        return $this->actingAs(User::find($this->u[$who]), 'sanctum');
    }

    protected function slug(): string
    {
        return DB::table('projects')->where('id', $this->pid)->value('slug');
    }

    protected function createAgent(string $who = 'admin', array $extra = []): int
    {
        $response = $this->as($who)->postJson('/api/agent', $extra + [
            'name' => 'Lyra', 'instructions' => 'Aide-moi.', 'autonomy' => 'propose', 'skills' => ['prospect_management'],
        ]);
        $response->assertStatus(201);

        return $response->json('id');
    }

    protected function script(array $steps): void
    {
        app()->instance(ClaudeGatewayFactory::class, new ScriptedGatewayFactory(new ScriptedGateway($steps)));
    }

    // Agents

    public function test_an_agent_is_created_configured_and_versioned(): void
    {
        $id = $this->createAgent('admin', ['model' => 'claude-sonnet-5-5', 'effort' => 'low', 'tool_grants' => ['prospect_set_field' => 'deny']]);

        $response = $this->as('admin')->getJson("/api/agent/$id");
        $response->assertOk()->assertJson(['name' => 'Lyra', 'model' => 'claude-sonnet-5-5', 'effort' => 'low', 'autonomy' => 'propose', 'skills' => ['prospect_management'], 'tool_grants' => ['prospect_set_field' => 'deny']]);

        $this->as('admin')->putJson("/api/agent/$id", ['instructions' => 'Autre', 'skills' => ['crm_exploration', 'statistics_analysis']])->assertOk()->assertJson(['skills' => ['crm_exploration', 'statistics_analysis']]);

        $agent = Agent::find($id);
        $this->assertSame(2, $agent->versions()->count());
        $this->assertSame(2, $agent->activeVersion->version);
        $this->assertSame('Autre', $agent->activeVersion->config['instructions']);
    }

    public function test_an_agent_created_without_a_list_of_skills_knows_every_function_of_the_crm(): void
    {
        $response = $this->as('admin')->postJson('/api/agent', ['name' => 'Lyra', 'instructions' => 'Aide-moi.', 'project' => $this->slug()]);
        $response->assertStatus(201)->assertJson(['all_skills' => true, 'model' => config('agents.default_model'), 'autonomy' => 'read_only']);

        $this->assertEqualsCanonicalizing(array_keys(config('agents.skills')), $response->json('skills'));
        $this->assertSame($this->pid, (int) Agent::find($response->json('id'))->project_id);

        // A skill added to the CRM later is known to the agent at once, without touching it
        config(['agents.skills.nouvelle_competence' => ['label' => 'Nouvelle', 'tools' => ['crm_describe'], 'playbook' => 'Test']]);
        $this->assertContains('nouvelle_competence', Agent::find($response->json('id'))->skillKeys());
    }

    public function test_an_agent_with_every_skill_still_only_gets_the_tools_its_owner_may_use(): void
    {
        $id = $this->as('t1')->postJson('/api/agent', ['name' => 'Tele', 'autonomy' => 'auto', 'project' => $this->slug()])->assertStatus(201)->json('id');
        $adminId = $this->as('admin')->postJson('/api/agent', ['name' => 'Chef', 'autonomy' => 'auto', 'project' => $this->slug()])->assertStatus(201)->json('id');

        $offered = function (int $agentId, string $who) {
            $gateway = new ScriptedGateway([ScriptedGateway::text('ok')]);
            app()->instance(ClaudeGatewayFactory::class, new ScriptedGatewayFactory($gateway));
            $this->as($who)->postJson("/api/agent/$agentId/ask", ['project' => $this->slug(), 'message' => 'Salut'])->assertStatus(202);

            return array_column($gateway->requests[0]['tools'], 'name');
        };

        $telepro = $offered($id, 't1');
        $admin = $offered($adminId, 'admin');

        // The administrator's agent has the whole catalogue; the télépro's has less, never more
        $this->assertGreaterThan(count($telepro), count($admin));
        $this->assertSame([], array_values(array_diff($telepro, $admin)));
        // The télépro role has no right to delete prospects or to read the workflows
        $this->assertContains('prospect_delete', $admin);
        $this->assertNotContains('prospect_delete', $telepro);
        $this->assertNotContains('workflow_catalog', $telepro);
        $this->assertContains('prospect_update', $telepro);
    }

    public function test_a_list_of_skills_given_by_the_api_narrows_the_agent_and_all_skills_widens_it_back(): void
    {
        $id = $this->createAgent('admin', ['skills' => ['crm_exploration']]);
        $this->as('admin')->getJson("/api/agent/$id")->assertJson(['all_skills' => false, 'skills' => ['crm_exploration']]);

        $this->as('admin')->putJson("/api/agent/$id", ['all_skills' => true])->assertOk()->assertJson(['all_skills' => true]);
        $this->assertGreaterThan(1, count(Agent::find($id)->skillKeys()));
    }

    public function test_what_does_not_exist_cannot_be_configured(): void
    {
        $this->as('admin')->postJson('/api/agent', ['name' => 'X', 'model' => 'gpt-4'])->assertStatus(422);
        $this->as('admin')->postJson('/api/agent', ['name' => 'X', 'skills' => ['hack']])->assertStatus(422);
        $this->as('admin')->postJson('/api/agent', ['name' => 'X', 'autonomy' => 'root'])->assertStatus(422);
        $this->as('admin')->postJson('/api/agent', ['name' => 'X', 'tool_grants' => ['rm_rf' => 'allow']])->assertStatus(422);
        $this->as('admin')->postJson('/api/agent', ['instructions' => 'sans nom'])->assertStatus(422);
        // A project the user does not belong to
        $this->as('stranger')->postJson('/api/agent', ['name' => 'X', 'project' => $this->slug()])->assertStatus(404);
        $this->assertSame(0, Agent::count());
    }

    public function test_nobody_sees_or_changes_the_agent_of_somebody_else(): void
    {
        $id = $this->createAgent('admin');

        $this->as('t1')->getJson("/api/agent/$id")->assertNotFound();
        $this->as('t1')->putJson("/api/agent/$id", ['name' => 'Pris'])->assertNotFound();
        $this->as('t1')->deleteJson("/api/agent/$id")->assertNotFound();
        $this->as('t1')->postJson("/api/agent/$id/ask", ['project' => $this->slug(), 'message' => 'Salut'])->assertNotFound();
        $this->assertSame([], $this->as('t1')->getJson('/api/agent')->json());
        $this->assertCount(1, $this->as('admin')->getJson('/api/agent')->json());
    }

    public function test_the_catalog_tells_what_can_be_chosen(): void
    {
        $catalog = $this->as('admin')->getJson('/api/agent/catalog')->assertOk()->json();
        $skills = collect($catalog['skills'])->keyBy('key');

        $this->assertArrayHasKey('claude-opus-5-5', $catalog['models']);
        $this->assertTrue($skills['prospect_management']['writes']);
        $this->assertFalse($skills['statistics_analysis']['writes']);
        $this->assertContains('sms_send', array_column($catalog['tools'], 'key'));
    }

    public function test_an_unauthenticated_visitor_gets_nothing(): void
    {
        $this->getJson('/api/agent')->assertUnauthorized();
        $this->getJson('/api/agent/action')->assertUnauthorized();
    }

    // Talking

    public function test_a_message_is_answered_and_the_history_shows_what_was_said_and_done(): void
    {
        $id = $this->createAgent('admin', ['autonomy' => 'read_only', 'skills' => ['crm_exploration']]);
        $this->script([ScriptedGateway::toolUse('project_config', ['section' => 'labels'], 'toolu_1'), ScriptedGateway::text('Voici vos labels.')]);

        // The queue is synchronous in tests: the answer is there at once
        $response = $this->as('admin')->postJson("/api/agent/$id/ask", ['project' => $this->slug(), 'message' => 'Quels labels ?']);
        $response->assertStatus(202);

        $run = $this->as('admin')->getJson('/api/agent/run/' . $response->json('run_id'))->assertOk()->json();
        $this->assertTrue($run['finished']);
        $this->assertSame('completed', $run['status']);
        $this->assertSame('project_config', $run['steps'][0]['tool']);

        $history = $this->as('admin')->getJson('/api/agent/conversation/' . $response->json('conversation_id'))->assertOk()->json();
        $kinds = array_map(fn ($m) => $m['role'] . ':' . $m['kind'], $history['messages']);

        $this->assertSame(['user:text', 'agent:text', 'agent:tool', 'agent:text'], $kinds);
        $this->assertSame('Quels labels ?', $history['messages'][0]['text']);
        $encoded = json_encode($history);
        // The context added for the model, the thinking and the raw data are not shown
        $this->assertStringNotContainsString('contexte_operateur', $encoded);
        $this->assertStringNotContainsString('SIG==', $encoded);
        $this->assertStringNotContainsString('crm_data', $encoded);
    }

    public function test_one_cannot_talk_in_a_project_one_is_not_in_nor_read_the_conversation_of_somebody_else(): void
    {
        $id = $this->createAgent('admin');
        $this->script([ScriptedGateway::text('Bonjour')]);

        $this->as('admin')->postJson("/api/agent/$id/ask", ['project' => 'inexistant', 'message' => 'Salut'])->assertNotFound();
        $this->as('admin')->postJson("/api/agent/$id/ask", ['project' => $this->slug()])->assertStatus(422);

        $ask = $this->as('admin')->postJson("/api/agent/$id/ask", ['project' => $this->slug(), 'message' => 'Secret'])->assertStatus(202);

        $this->as('t1')->getJson('/api/agent/conversation/' . $ask->json('conversation_id'))->assertNotFound();
        $this->as('t1')->getJson('/api/agent/run/' . $ask->json('run_id'))->assertNotFound();
    }

    public function test_an_agent_bound_to_a_project_refuses_another(): void
    {
        $id = $this->createAgent('admin', ['project' => $this->slug()]);
        $other = DB::table('projects')->where('id', $this->makeProject('Gamma', $this->u['admin']))->value('slug');

        $this->script([ScriptedGateway::text('x')]);
        $this->as('admin')->postJson("/api/agent/$id/ask", ['project' => $other, 'message' => 'Salut'])->assertStatus(422);
    }

    // Approving

    public function test_a_proposed_action_is_listed_then_approved_from_the_api_and_run_by_the_server(): void
    {
        $id = $this->createAgent('admin');
        $this->script([
            ScriptedGateway::toolUse('prospect_set_labels', ['prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'label_ids' => [$this->nrp]], 'toolu_1'),
            ScriptedGateway::text('En attente de votre validation.'),
        ]);

        $ask = $this->as('admin')->postJson("/api/agent/$id/ask", ['project' => $this->slug(), 'message' => 'Passe p1 en NRP'])->assertStatus(202);

        // The pending action is visible from the run and from the conversation
        $run = $this->as('admin')->getJson('/api/agent/run/' . $ask->json('run_id'))->json();
        $this->assertCount(1, $run['pending_actions']);
        $actionId = $run['pending_actions'][0]['id'];

        $list = $this->as('admin')->getJson('/api/agent/action')->assertOk()->json();
        $this->assertSame([$actionId], array_column($list, 'id'));
        $this->assertSame(1, $list[0]['preview']['prospects']);
        $this->assertSame('prospect_set_labels', $list[0]['tool']);
        // The stored payload is not sent back
        $this->assertArrayNotHasKey('payload', $list[0]);
        $this->assertNotNull($list[0]['expires_at']);
        $this->assertSame(0, DB::table('prospect_label')->where('prospect_id', $this->p['p1'])->count());

        // Somebody else neither sees nor decides it
        $this->as('t1')->getJson("/api/agent/action/$actionId")->assertNotFound();
        $this->as('t1')->postJson("/api/agent/action/$actionId/approve")->assertNotFound();
        $this->assertSame([], $this->as('t1')->getJson('/api/agent/action')->json());
        $this->assertSame(0, DB::table('prospect_label')->where('prospect_id', $this->p['p1'])->count());

        $this->as('admin')->postJson("/api/agent/action/$actionId/approve")->assertStatus(202);

        $this->assertSame([$this->nrp], DB::table('prospect_label')->where('prospect_id', $this->p['p1'])->pluck('label_id')->map(fn ($v) => (int) $v)->all());
        $done = $this->as('admin')->getJson("/api/agent/action/$actionId")->json();
        $this->assertSame('executed', $done['status']);
        $this->assertSame(1, $done['result']['updated']);
        $this->assertNull($done['expires_at']);

        // The agent is told in the conversation
        $history = $this->as('admin')->getJson('/api/agent/conversation/' . $ask->json('conversation_id'))->json();
        $this->assertSame('system', end($history['messages'])['role']);
        $this->assertStringContainsString('EXÉCUTÉE', end($history['messages'])['text']);
        $this->assertSame([], $history['pending_actions']);

        // A second click does nothing more
        $this->as('admin')->postJson("/api/agent/action/$actionId/approve")->assertStatus(422);
        $this->assertSame(1, DB::table('prospect_label')->where('prospect_id', $this->p['p1'])->count());
    }

    public function test_a_refused_action_is_not_run_and_the_list_can_show_every_status(): void
    {
        $id = $this->createAgent('admin');
        $this->script([ScriptedGateway::toolUse('prospect_set_labels', ['prospect_ids' => [$this->p['p1']], 'action' => 'attach', 'label_ids' => [$this->nrp]], 'toolu_1'), ScriptedGateway::text('ok')]);
        $this->as('admin')->postJson("/api/agent/$id/ask", ['project' => $this->slug(), 'message' => 'Fais-le'])->assertStatus(202);
        $actionId = AgentAction::first()->id;

        $this->as('admin')->postJson("/api/agent/action/$actionId/reject")->assertOk()->assertJson(['status' => 'rejected']);

        $this->assertSame(0, DB::table('prospect_label')->where('prospect_id', $this->p['p1'])->count());
        $this->assertSame([], $this->as('admin')->getJson('/api/agent/action')->json());
        $this->assertCount(1, $this->as('admin')->getJson('/api/agent/action?status=all')->json());
        $this->as('admin')->postJson("/api/agent/action/$actionId/approve")->assertStatus(422);
    }

    public function test_an_unknown_action_is_a_404(): void
    {
        $this->as('admin')->postJson('/api/agent/action/999999/approve')->assertNotFound();
    }
}
