<?php

namespace Tests\Feature;

use App\Agents\AgentAccessException;
use App\Agents\AgentConversationService;
use App\Agents\AgentRequestException;
use App\Jobs\Agent\RunAgentTurn;
use App\Models\Agent;
use App\Models\AgentConversation;
use App\Models\AgentMessage;
use App\Models\AgentRun;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

class AgentConversationServiceTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected User $owner;
    protected Project $project;
    protected Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        // Nothing may run: only what the service queues is looked at
        Queue::fake();

        $ownerId = $this->makeUser('Alice');
        $this->owner = User::find($ownerId);
        $this->project = Project::withoutGlobalScopes()->find($this->makeProject('Alpha', $ownerId));
        $this->agent = Agent::create([
            'user_id' => $ownerId,
            'name' => 'Lyra',
            'model' => 'claude-opus-5-5',
            'effort' => 'medium',
            'autonomy' => Agent::READ_ONLY,
            'status' => Agent::ACTIVE,
        ]);
    }

    protected function service(): AgentConversationService
    {
        return app(AgentConversationService::class);
    }

    public function test_a_message_starts_a_conversation_and_queues_a_run(): void
    {
        $run = $this->service()->ask($this->agent, $this->owner, $this->project, '  Fais-moi un rapport.  ');

        $this->assertSame(AgentRun::PENDING, $run->status);
        $this->assertSame('claude-opus-5-5', $run->model);
        $this->assertSame($this->project->id, (int) $run->project_id);

        $conversation = AgentConversation::first();
        $this->assertSame('Fais-moi un rapport.', $conversation->title);
        $this->assertSame($this->project->id, (int) $conversation->project_id);

        Queue::assertPushedOn(config('agents.queue'), RunAgentTurn::class, fn ($job) => $job->runId === $run->id);
    }

    public function test_the_context_of_the_moment_travels_with_the_message_not_in_the_system_prompt(): void
    {
        $this->service()->ask($this->agent, $this->owner, $this->project, 'Bonjour');

        $blocks = AgentMessage::first()->content;

        $this->assertStringContainsString('<contexte_operateur>', $blocks[0]['text']);
        $this->assertStringContainsString('Projet : Alpha', $blocks[0]['text']);
        $this->assertStringContainsString('Utilisateur : Alice', $blocks[0]['text']);
        $this->assertSame('Bonjour', $blocks[1]['text']);
    }

    public function test_a_second_message_goes_in_the_same_conversation_once_the_first_run_is_over(): void
    {
        $first = $this->service()->ask($this->agent, $this->owner, $this->project, 'Un');
        $conversationId = (int) $first->conversation_id;

        try {
            $this->service()->ask($this->agent, $this->owner, $this->project, 'Deux', $conversationId);
            $this->fail('The agent was still answering.');
        } catch (AgentRequestException $e) {
            $this->assertStringContainsString('encore', $e->getMessage());
        }

        $first->update(['status' => AgentRun::COMPLETED]);
        $second = $this->service()->ask($this->agent, $this->owner, $this->project, 'Deux', $conversationId);

        $this->assertSame($conversationId, (int) $second->conversation_id);
        $this->assertSame(1, AgentConversation::count());
        $this->assertSame(2, AgentMessage::count());
    }

    public function test_bad_messages_are_refused(): void
    {
        $this->expectException(AgentRequestException::class);
        $this->service()->ask($this->agent, $this->owner, $this->project, '   ');
    }

    public function test_a_message_that_is_too_long_is_refused(): void
    {
        $this->expectException(AgentRequestException::class);
        $this->service()->ask($this->agent, $this->owner, $this->project, str_repeat('a', config('agents.max_message_chars') + 1));
    }

    public function test_nobody_uses_the_agent_of_someone_else(): void
    {
        $bobId = $this->makeUser('Bob');
        $this->makeProject('Beta', $bobId);
        DB::table('user_project')->insert(['user_id' => $bobId, 'project_id' => $this->project->id]);

        $this->expectException(AgentRequestException::class);
        $this->service()->ask($this->agent, User::find($bobId), $this->project, 'Bonjour');
    }

    public function test_nobody_reads_the_conversation_of_someone_else(): void
    {
        $run = $this->service()->ask($this->agent, $this->owner, $this->project, 'Secret');
        $run->update(['status' => AgentRun::COMPLETED]);

        $bobId = $this->makeUser('Bob');
        DB::table('user_project')->insert(['user_id' => $bobId, 'project_id' => $this->project->id]);
        $bobAgent = Agent::create(['user_id' => $bobId, 'name' => 'B', 'model' => 'claude-opus-5-5', 'effort' => 'medium', 'status' => Agent::ACTIVE]);

        $this->expectException(AgentRequestException::class);
        $this->service()->ask($bobAgent, User::find($bobId), $this->project, 'Montre-moi', (int) $run->conversation_id);
    }

    public function test_a_user_outside_the_project_cannot_start_a_run_in_it(): void
    {
        $otherProject = Project::withoutGlobalScopes()->find($this->makeProject('Beta'));

        $this->expectException(AgentAccessException::class);
        $this->service()->ask($this->agent, $this->owner, $otherProject, 'Bonjour');
    }

    public function test_an_agent_bound_to_a_project_is_refused_in_another_one(): void
    {
        $this->agent->update(['project_id' => $this->project->id]);
        $other = Project::withoutGlobalScopes()->find($this->makeProject('Beta', $this->owner->id));

        $this->expectException(AgentRequestException::class);
        $this->service()->ask($this->agent, $this->owner, $other, 'Bonjour');
    }

    public function test_a_disabled_agent_is_refused(): void
    {
        $this->agent->update(['status' => Agent::DISABLED]);

        $this->expectException(AgentRequestException::class);
        $this->service()->ask($this->agent, $this->owner, $this->project, 'Bonjour');
    }
}
