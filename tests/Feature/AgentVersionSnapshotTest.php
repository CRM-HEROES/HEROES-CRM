<?php

namespace Tests\Feature;

use App\Agents\SkillRegistry;
use App\Agents\Tools\ToolRegistry;
use App\Http\Controllers\API\Agent\AgentController;
use App\Models\Agent;
use ReflectionMethod;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

class AgentVersionSnapshotTest extends TestCase
{
    use UsesInMemoryDatabase;

    public function test_agent_version_snapshot_contains_specialty_and_description(): void
    {
        $this->setUpInMemoryDatabase();
        config(['agents.skills' => []]);
        $userId = $this->makeUser();
        $agent = Agent::create([
            'user_id' => $userId,
            'name' => 'Lyra',
            'specialty' => 'Conseil commercial',
            'description' => 'Accompagner les prospects.',
            'model' => 'claude-opus-5-5',
            'effort' => 'medium',
            'autonomy' => Agent::READ_ONLY,
            'settings' => ['all_skills' => true],
            'status' => Agent::ACTIVE,
        ]);
        $controller = new AgentController(new SkillRegistry(), new ToolRegistry());
        $publish = new ReflectionMethod($controller, 'publish');
        $publish->invoke($controller, $agent, $userId);

        $config = $agent->fresh()->activeVersion->config;

        $this->assertSame('Conseil commercial', $config['specialty']);
        $this->assertSame('Accompagner les prospects.', $config['description']);
    }
}
