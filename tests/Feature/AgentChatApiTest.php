<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\ReportCreateTool;
use App\Models\Agent;
use App\Models\AgentConversation;
use App\Models\AgentReport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\CrmFixtureTestCase;
use Tests\Support\DrivesAgents;

/**
 * What the chat page needs: the history of the project, the documents the
 * agents produce, archiving. Through the whole kernel, on the real tables
 * (MySQL, rolled back): AGENTS_TEST_DB=mysql AGENTS_TEST_ALLOW_DATA=1.
 */
class AgentChatApiTest extends CrmFixtureTestCase
{
    use DrivesAgents;

    protected function as(string $who): static
    {
        return $this->actingAs(User::find($this->u[$who]), 'sanctum');
    }

    protected function slug(): string
    {
        return DB::table('projects')->where('id', $this->pid)->value('slug');
    }

    // The document

    public function test_a_read_only_agent_saves_a_document_that_is_shown_with_the_conversation(): void
    {
        $agent = $this->agent('admin', Agent::READ_ONLY, ['reporting']);

        $run = $this->turn($agent, [['report_create', ['title' => 'Activité de la semaine', 'content' => "# Synthèse\n\n| A | B |\n|---|---|\n| 1 | 2 |"]]]);
        $result = $this->results($run)['toolu_0'];

        $this->assertTrue($result['success']);

        $report = AgentReport::find($result['report_id']);
        $this->assertSame([$agent->id, $this->u['admin'], $this->pid, $run->id], [(int) $report->agent_id, (int) $report->user_id, (int) $report->project_id, (int) $report->run_id]);
        $this->assertSame('markdown', $report->format);
        $this->assertStringContainsString('# Synthèse', $report->content);

        $conversation = $this->as('admin')->getJson('/api/agent/conversation/' . $run->conversation_id)->assertOk()->json();
        $this->assertSame([$report->id], array_column($conversation['reports'], 'id'));
        $this->assertSame($run->id, $conversation['reports'][0]['run_id']);
        $this->assertSame('Activité de la semaine', $conversation['reports'][0]['title']);
        // Every message says which run it comes from, to place the document in the conversation
        $this->assertArrayHasKey('run_id', $conversation['messages'][0]);

        $this->as('admin')->getJson('/api/agent/report/' . $report->id)->assertOk()
            ->assertJson(['title' => 'Activité de la semaine', 'format' => 'markdown', 'agent' => 'Agent']);
    }

    public function test_nobody_reads_the_document_of_somebody_else(): void
    {
        $run = $this->turn($this->agent('admin', Agent::READ_ONLY, ['reporting']), [['report_create', ['title' => 'Secret', 'content' => 'Contenu']]]);
        $id = $this->results($run)['toolu_0']['report_id'];

        $this->as('t1')->getJson("/api/agent/report/$id")->assertNotFound();
        $this->as('t1')->getJson('/api/agent/conversation/' . $run->conversation_id)->assertNotFound();
        $this->assertSame(200, $this->as('admin')->getJson("/api/agent/report/$id")->status());
    }

    public function test_an_empty_or_oversized_document_is_refused(): void
    {
        $run = $this->turn($this->agent('admin', Agent::READ_ONLY, ['reporting']), [
            ['report_create', ['title' => '   ', 'content' => 'x']],
            ['report_create', ['title' => 'Vide', 'content' => "  \n "]],
            ['report_create', ['title' => 'Énorme', 'content' => str_repeat('a', ReportCreateTool::MAX_CHARS + 1)]],
        ]);

        foreach ($this->results($run) as $result) {
            $this->assertFalse($result['success']);
            $this->assertSame('INVALID_INPUT', $result['error']['code']);
        }

        $this->assertSame(0, AgentReport::where('user_id', $this->u['admin'])->where('title', 'Énorme')->count());
    }

    public function test_a_document_needs_an_agent_run(): void
    {
        $context = new AgentExecutionContext(User::find($this->u['admin']), Project::withoutGlobalScopes()->find($this->pid));

        $result = $context->run(fn () => app(ReportCreateTool::class)->handle($context, ['title' => 'T', 'content' => 'C']));

        $this->assertTrue($result->isError);
        $this->assertSame('UNAVAILABLE', $result->code);
    }

    public function test_the_document_tool_is_part_of_what_every_agent_knows(): void
    {
        $this->assertContains('report_create', array_keys(app(\App\Agents\Tools\ToolRegistry::class)->all()));
        $this->assertContains('report_create', app(\App\Agents\SkillRegistry::class)->toolKeys(array_keys(config('agents.skills'))));
    }

    // The history of the project

    public function test_the_history_lists_the_conversations_of_the_user_in_the_project_whatever_the_agent(): void
    {
        $lyra = $this->agent('admin', Agent::READ_ONLY, ['reporting']);
        $other = Agent::create(['user_id' => $this->u['admin'], 'name' => 'Max', 'model' => 'claude-opus-5-5', 'effort' => 'medium', 'autonomy' => 'read_only', 'status' => 'active']);

        $first = $this->turn($lyra, [['report_create', ['title' => 'A', 'content' => 'a']]]);
        $second = $this->turn($other, []);
        // The other user's conversation, and one in another project, are not mine
        $this->turn($this->agent('t1', Agent::READ_ONLY, ['reporting']), []);
        $foreign = AgentConversation::create(['agent_id' => $lyra->id, 'user_id' => $this->u['admin'], 'project_id' => $this->beta, 'title' => 'Ailleurs']);

        DB::table('agent_conversations')->where('id', $first->conversation_id)->update(['last_message_at' => now()->subHour()]);
        DB::table('agent_conversations')->where('id', $second->conversation_id)->update(['last_message_at' => now()]);

        $list = $this->as('admin')->getJson('/api/agent/conversation?project=' . $this->slug())->assertOk()->json();

        $this->assertSame([$second->conversation_id, $first->conversation_id], array_column($list, 'id'));
        $this->assertSame(['Max', 'Agent'], array_column(array_column($list, 'agent'), 'name'));
        $this->assertNotContains($foreign->id, array_column($list, 'id'));
        $this->assertArrayHasKey('avatar_url', $list[0]['agent']);

        $this->assertSame(1, count($this->as('t1')->getJson('/api/agent/conversation?project=' . $this->slug())->json()));
    }

    public function test_the_history_needs_a_project_the_user_belongs_to(): void
    {
        $this->as('admin')->getJson('/api/agent/conversation')->assertStatus(422);
        $this->as('admin')->getJson('/api/agent/conversation?project=inexistant')->assertNotFound();
        // The stranger is not in Alpha
        $this->as('stranger')->getJson('/api/agent/conversation?project=' . $this->slug())->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/agent/conversation?project=' . $this->slug())->assertUnauthorized();
    }

    public function test_a_conversation_removed_from_the_history_is_archived_and_its_trail_stays(): void
    {
        $run = $this->turn($this->agent('admin', Agent::READ_ONLY, ['reporting']), [['report_create', ['title' => 'Garde-moi', 'content' => 'x']]]);
        $id = $run->conversation_id;

        $this->as('t1')->deleteJson("/api/agent/conversation/$id")->assertNotFound();
        $this->as('admin')->deleteJson("/api/agent/conversation/$id")->assertOk();

        $list = $this->as('admin')->getJson('/api/agent/conversation?project=' . $this->slug())->json();
        $this->assertNotContains($id, array_column($list, 'id'));
        $this->assertSame('archived', AgentConversation::find($id)->status);
        // What the agent did is still recorded
        $this->assertSame(1, DB::table('agent_runs')->where('id', $run->id)->count());
        $this->assertGreaterThan(0, DB::table('agent_run_steps')->where('run_id', $run->id)->count());
        $this->assertSame(1, AgentReport::where('title', 'Garde-moi')->count());
    }
}
