<?php

namespace Tests\Support;

use App\Agents\AgentRunner;
use App\Agents\Claude\ClaudeGatewayFactory;
use App\Models\Agent;
use App\Models\AgentConversation;
use App\Models\AgentMessage;
use App\Models\AgentRun;
use Illuminate\Support\Facades\DB;

/**
 * Runs an agent turn with a scripted model, on the fixtures of CrmFixtureTestCase.
 */
trait DrivesAgents
{

    protected function agent(string $owner, string $autonomy, array $skills = ['prospect_management']): Agent
    {
        $agent = Agent::create([
            'user_id' => $this->u[$owner], 'name' => 'Agent', 'model' => 'claude-opus-5-5', 'effort' => 'medium',
            'autonomy' => $autonomy, 'status' => Agent::ACTIVE,
        ]);

        foreach ($skills as $skill) {
            DB::table('agent_skills')->insert(['agent_id' => $agent->id, 'skill_key' => $skill]);
        }

        return $agent;
    }

    /**
     * Runs one turn where the model calls the given tools, one after the other.
     */
    protected function turn(Agent $agent, array $calls): AgentRun
    {
        $script = [];

        foreach ($calls as $i => [$tool, $input]) {
            $script[] = ScriptedGateway::toolUse($tool, $input, "toolu_$i");
        }

        $script[] = ScriptedGateway::text('Terminé.');
        app()->instance(ClaudeGatewayFactory::class, new ScriptedGatewayFactory(new ScriptedGateway($script)));

        $conversation = AgentConversation::create(['agent_id' => $agent->id, 'user_id' => $agent->user_id, 'project_id' => $this->pid, 'title' => 't']);
        AgentMessage::create(['conversation_id' => $conversation->id, 'role' => 'user', 'content' => [['type' => 'text', 'text' => 'Fais-le']]]);
        $run = AgentRun::create(['conversation_id' => $conversation->id, 'agent_id' => $agent->id, 'user_id' => $agent->user_id, 'project_id' => $this->pid, 'status' => AgentRun::PENDING, 'model' => $agent->model]);

        app(AgentRunner::class)->run($run->id);

        return $run->fresh();
    }

    /**
     * What the tools told the model, in order (decoded).
     */
    protected function results(AgentRun $run): array
    {
        $out = [];

        foreach (AgentMessage::where('conversation_id', $run->conversation_id)->where('role', 'user')->orderBy('id')->get() as $message) {
            foreach ($message->content as $block) {
                if (($block['type'] ?? '') !== 'tool_result') {
                    continue;
                }

                preg_match('#<crm_data untrusted="true">(.*)</crm_data>#s', $block['content'], $m);
                $out[$block['tool_use_id']] = $m ? json_decode($m[1], true) : json_decode($block['content'], true);
            }
        }

        return $out;
    }

    protected function labelsOf(int $prospectId): array
    {
        return DB::table('prospect_label')->where('prospect_id', $prospectId)->whereNull('deleted_at')->pluck('label_id')->map(fn ($id) => (int) $id)->all();
    }

}
