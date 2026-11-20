<?php

namespace Tests\Unit;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Agents\Tools\ProspectRecordTool;
use App\Agents\Tools\ToolResult;
use App\Agents\Writes\InternalApi;
use App\Agents\Writes\InternalResponse;
use App\Models\Project;
use App\Models\User;
use Mockery;
use Tests\TestCase;

/**
 * "verify" of a write definition: what the CRM did after the route answered is read back, so that an operator which
 * refused an SMS is not hidden behind "done".
 */
class ProspectRecordVerifyTest extends TestCase
{
    protected function tool(?callable $verify): ProspectRecordTool
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturn(new InternalResponse(200, ['id' => 77], '{}'));

        return new ProspectRecordTool([
            'key' => 'fake_send', 'risk' => 'communication', 'ability' => null,
            'schema' => [], 'required' => [],
            'request' => fn ($context, $input, $prospect) => ['POST', 'x', []],
            'describe' => fn () => ['action' => 'x'],
            'verify' => $verify,
        ], $api, Mockery::mock(ReadGateway::class));
    }

    protected function context(): AgentExecutionContext
    {
        $project = new Project();
        $project->id = 1;

        return new AgentExecutionContext(new User(), $project);
    }

    public function test_what_the_crm_refused_after_the_route_answered_is_told(): void
    {
        $result = $this->tool(fn ($context, $json) => ToolResult::error("Opérateur sans configuration (n° {$json['id']})", ToolResult::UNAVAILABLE))->handle($this->context(), []);

        $this->assertTrue($result->isError);
        $this->assertStringContainsString('n° 77', $result->data['error']);
    }

    public function test_when_nothing_is_wrong_the_action_is_done(): void
    {
        $result = $this->tool(fn () => null)->handle($this->context(), []);

        $this->assertFalse($result->isError);
        $this->assertTrue($result->data['done']);
    }
}
