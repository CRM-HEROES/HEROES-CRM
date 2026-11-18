<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\AgentRunner;
use App\Agents\Claude\ClaudeGatewayFactory;
use App\Agents\Tools\AppHelpTool;
use App\Models\Agent;
use App\Models\AgentConversation;
use App\Models\AgentMessage;
use App\Models\AgentRun;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\ScriptedGateway;
use Tests\Support\ScriptedGatewayFactory;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

/**
 * "Aide sur le CRM" with the real tool, the real skills and the real help sheets: only the model is scripted.
 */
class AgentHelpEndToEndTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected int $ownerId;
    protected int $projectId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        (require database_path('migrations/2026_11_13_000001_create_agent_message_attachments_table.php'))->up();
        (require database_path('migrations/2026_11_14_000001_add_summary_to_agent_message_attachments.php'))->up();
        config(['logging.default' => 'null']);
        $this->ownerId = $this->makeUser('Alice');
        $this->projectId = $this->makeProject('Alpha', $this->ownerId);
    }

    protected function agent(array $settings, array $skills = []): Agent
    {
        $agent = Agent::create([
            'user_id' => $this->ownerId,
            'name' => 'Lyra',
            'model' => 'claude-opus-5-5',
            'effort' => 'medium',
            'autonomy' => Agent::READ_ONLY,
            'status' => Agent::ACTIVE,
            'settings' => $settings,
        ]);
        foreach ($skills as $skill) {
            DB::table('agent_skills')->insert(['agent_id' => $agent->id, 'skill_key' => $skill]);
        }

        return $agent;
    }

    protected function ask(Agent $agent, array $script, string $question): array
    {
        $gateway = new ScriptedGateway($script);
        app()->instance(ClaudeGatewayFactory::class, new ScriptedGatewayFactory($gateway));

        $conversation = AgentConversation::create([
            'agent_id' => $agent->id, 'user_id' => $this->ownerId, 'project_id' => $this->projectId, 'title' => 'Aide',
        ]);
        AgentMessage::create(['conversation_id' => $conversation->id, 'role' => 'user', 'content' => [['type' => 'text', 'text' => $question]]]);
        $run = AgentRun::create([
            'conversation_id' => $conversation->id, 'agent_id' => $agent->id, 'user_id' => $this->ownerId,
            'project_id' => $this->projectId, 'status' => AgentRun::PENDING, 'model' => $agent->model,
        ]);

        app(AgentRunner::class)->run($run->id);

        return [$run->fresh(), $gateway, $conversation];
    }

    protected function toolNames(array $request): array
    {
        return array_column($request['tools'] ?? [], 'name');
    }

    protected function toolResults(AgentConversation $conversation)
    {
        return AgentMessage::where('conversation_id', $conversation->id)->where('role', 'user')->orderBy('id')->get()
            ->filter(fn ($message) => ($message->content[0]['type'] ?? null) === 'tool_result')
            ->map(fn ($message) => $message->content[0])
            ->values();
    }

    public function test_an_agent_with_every_skill_is_given_the_help_tool_and_its_playbook(): void
    {
        [$run, $gateway] = $this->ask(
            $this->agent(['all_skills' => true]),
            [ScriptedGateway::text('Voici.')],
            'Comment envoyer un SMS ?'
        );

        $this->assertSame(AgentRun::COMPLETED, $run->status);
        $this->assertContains('app_help', $this->toolNames($gateway->requests[0]));
        $system = is_array($gateway->requests[0]['system'])
            ? json_encode($gateway->requests[0]['system'], JSON_UNESCAPED_UNICODE)
            : $gateway->requests[0]['system'];
        $this->assertStringContainsString('appelle app_help avant de répondre', $system);
    }

    public function test_an_agent_with_a_short_list_of_skills_can_still_explain_the_crm(): void
    {
        [, $gateway] = $this->ask(
            $this->agent(['all_skills' => false], ['crm_exploration']),
            [ScriptedGateway::text('Voici.')],
            'Comment créer une campagne ?'
        );

        $this->assertContains('app_help', $this->toolNames($gateway->requests[0]));
        $this->assertContains('crm_search', $this->toolNames($gateway->requests[0]));
        // It gets no tool of another skill
        $this->assertNotContains('sms_send', $this->toolNames($gateway->requests[0]));
    }

    public function test_the_model_asks_for_a_sheet_and_gets_the_text_of_the_sheet_back(): void
    {
        [$run, , $conversation] = $this->ask(
            $this->agent(['all_skills' => true]),
            [
                ScriptedGateway::toolUse('app_help', ['topic' => 'sms-emails']),
                ScriptedGateway::text('1. Ouvrez la fiche du prospect puis « SMS ».'),
            ],
            'Comment envoyer un SMS ?'
        );

        $this->assertSame(AgentRun::COMPLETED, $run->status);
        $this->assertSame(2, $run->iterations);

        // What went back to the model is the real sheet, flagged as data
        $result = $this->toolResults($conversation)->first();
        $this->assertEmpty($result['is_error'] ?? false);
        $this->assertStringContainsString('untrusted="true"', $result['content']);
        $this->assertStringContainsString('SMS via votre téléphone', $result['content']);
        $this->assertStringContainsString('Saisir un SMS', $result['content']);
    }

    public function test_a_long_sheet_is_given_as_an_index_then_by_section(): void
    {
        config(['agents.max_tool_result_chars' => 3000]);

        [, , $conversation] = $this->ask(
            $this->agent(['all_skills' => true]),
            [
                ScriptedGateway::toolUse('app_help', ['topic' => 'sms-emails']),
                ScriptedGateway::toolUse('app_help', ['topic' => 'sms-emails', 'section' => 'Règles']),
                ScriptedGateway::text('Voici.'),
            ],
            'Quelles sont les règles des SMS ?'
        );

        $results = $this->toolResults($conversation);

        $this->assertStringContainsString('Cette fiche est longue', $results[0]['content']);
        $this->assertStringContainsString('- Règles', $results[0]['content']);
        $this->assertStringContainsString('coupe-circuit', $results[1]['content']);
    }

    public function test_every_topic_of_the_list_can_be_opened(): void
    {
        [, , $conversation] = $this->ask(
            $this->agent(['all_skills' => true]),
            [ScriptedGateway::toolUse('app_help'), ScriptedGateway::text('Voici.')],
            'Que sais-tu expliquer ?'
        );

        preg_match('#<crm_data untrusted="true">(.*)</crm_data>#s', $this->toolResults($conversation)->first()['content'], $matches);
        $topics = array_keys(json_decode($matches[1], true)['topics']);

        $this->assertCount(count(glob(base_path('resources/agent-knowledge/*.md'))), $topics);
        $this->assertContains('agents-ia', $topics);
        // The list says what each sheet covers, to open the right one
        $summaries = json_decode($matches[1], true)['summaries'];
        $this->assertSame($topics, array_keys($summaries));
        foreach ($summaries as $topic => $summary) {
            $this->assertNotSame('', $summary, $topic);
        }

        $context = new AgentExecutionContext(User::findOrFail($this->ownerId), Project::withoutGlobalScopes()->findOrFail($this->projectId));
        foreach ($topics as $topic) {
            $result = app(AppHelpTool::class)->handle($context, ['topic' => $topic]);
            $this->assertFalse($result->isError, $topic);
        }
    }
}
