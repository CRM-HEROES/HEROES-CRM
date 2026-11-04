<?php

namespace Tests\Unit\Agents;

use App\Agents\AgentExecutionContext;
use App\Agents\PermissionGate;
use App\Agents\Tools\AgentToolInterface;
use App\Agents\Tools\ToolResult;
use App\Models\Agent;
use Tests\TestCase;

class PermissionGateTest extends TestCase
{
    protected function tool(string $risk = 'read', ?string $ability = 'prospectView', string $key = 'crm_search'): AgentToolInterface
    {
        return new class($risk, $ability, $key) implements AgentToolInterface {
            public function __construct(private string $risk, private ?string $ability, private string $key)
            {
            }

            public function key(): string
            {
                return $this->key;
            }

            public function description(): string
            {
                return '';
            }

            public function inputSchema(): array
            {
                return ['type' => 'object', 'additionalProperties' => false];
            }

            public function module(): string
            {
                return 'prospect';
            }

            public function risk(): string
            {
                return $this->risk;
            }

            public function ability(): ?string
            {
                return $this->ability;
            }

            public function handle(AgentExecutionContext $context, array $input): ToolResult
            {
                return ToolResult::ok([]);
            }

            public function preview(AgentExecutionContext $context, array $input): array
            {
                return [];
            }
        };
    }

    public function test_a_read_tool_of_a_skill_runs_when_the_owner_has_the_ability(): void
    {
        $decision = (new PermissionGate())->decide($this->tool(), Agent::READ_ONLY, ['crm_search'], [], true);

        $this->assertTrue($decision->isAllowed());
    }

    public function test_a_tool_outside_the_skills_is_refused(): void
    {
        $decision = (new PermissionGate())->decide($this->tool(), Agent::AUTO, ['other'], [], true);

        $this->assertTrue($decision->isDenied());
    }

    public function test_an_explicit_deny_wins_over_the_skills_and_over_an_allow(): void
    {
        $decision = (new PermissionGate())->decide($this->tool(), Agent::AUTO, ['crm_search'], ['crm_search' => 'deny'], true);

        $this->assertTrue($decision->isDenied());
    }

    public function test_an_agent_never_has_more_rights_than_its_owner(): void
    {
        $gate = new PermissionGate();

        $this->assertTrue($gate->decide($this->tool(), Agent::AUTO, ['crm_search'], [], false)->isDenied());
        $this->assertTrue($gate->decide($this->tool(), Agent::AUTO, ['crm_search'], [], null)->isDenied());
    }

    public function test_a_tool_without_ability_does_not_need_the_owner_right(): void
    {
        $decision = (new PermissionGate())->decide($this->tool('read', null), Agent::READ_ONLY, ['crm_search'], [], null);

        $this->assertTrue($decision->isAllowed());
    }

    public function test_a_read_only_agent_cannot_write(): void
    {
        $decision = (new PermissionGate())->decide($this->tool('write', 'prospectUpdate', 'prospect_update'), Agent::READ_ONLY, ['prospect_update'], [], true);

        $this->assertTrue($decision->isDenied());
    }

    public function test_a_proposing_agent_has_every_write_approved_by_a_human(): void
    {
        $decision = (new PermissionGate())->decide($this->tool('write', 'prospectUpdate', 'prospect_update'), Agent::PROPOSE, ['prospect_update'], [], true);

        $this->assertTrue($decision->needsApproval());
    }

    public function test_an_automatic_agent_runs_a_simple_write_but_not_a_communication_or_a_deletion(): void
    {
        config(['agents.approval_required_risks' => ['communication', 'destructive']]);
        $gate = new PermissionGate();

        $write = $gate->decide($this->tool('write', 'prospectUpdate', 'a'), Agent::AUTO, ['a'], [], true);
        $sms = $gate->decide($this->tool('communication', 'prospectSmsAdd', 'b'), Agent::AUTO, ['b'], [], true);
        $delete = $gate->decide($this->tool('destructive', 'prospectDelete', 'c'), Agent::AUTO, ['c'], [], true);

        $this->assertTrue($write->isAllowed());
        $this->assertTrue($sms->needsApproval());
        $this->assertTrue($delete->needsApproval());
    }

    public function test_an_unknown_risk_or_autonomy_is_refused(): void
    {
        $gate = new PermissionGate();

        $this->assertTrue($gate->decide($this->tool('weird'), Agent::AUTO, ['crm_search'], [], true)->isDenied());
        $this->assertTrue($gate->decide($this->tool('write'), 'unknown', ['crm_search'], [], true)->isDenied());
    }
}
