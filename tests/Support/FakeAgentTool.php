<?php

namespace Tests\Support;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\AgentToolInterface;
use App\Agents\Tools\ToolResult;

/**
 * A configurable tool for the tests; it counts how many times it really ran.
 */
class FakeAgentTool implements AgentToolInterface
{
    public int $calls = 0;

    /** @var array<int, array> the inputs it was run with */
    public array $inputs = [];

    public ?\Throwable $throws = null;

    public ?array $returns = null;

    public function __construct(
        protected string $key = 'echo',
        protected string $risk = 'read',
        protected ?string $ability = null,
    ) {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function description(): string
    {
        return "Outil de test {$this->key}.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['text' => ['type' => 'string', 'maxLength' => 20]],
            'required' => [],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'test';
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
        $this->calls++;
        $this->inputs[] = $input;

        if ($this->throws) {
            throw $this->throws;
        }

        return ToolResult::ok($this->returns ?? [
            'echo' => $input['text'] ?? null,
            // What a real tool relies on: the identity set by the context
            'acting_user' => auth()->id(),
            'project' => request()->project?->id,
            'team' => getPermissionsTeamId(),
        ], 'ok', 1);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return ['will' => "run {$this->key}", 'with' => $input];
    }
}
