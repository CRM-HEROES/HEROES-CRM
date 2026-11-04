<?php

namespace Tests\Unit\Agents;

use App\Agents\Tools\InputValidator;
use App\Agents\Tools\ToolRegistry;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeAgentTool;

class ToolsTest extends TestCase
{
    protected array $schema = [
        'type' => 'object',
        'properties' => [
            'entity' => ['type' => 'string', 'enum' => ['prospect', 'user']],
            'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50],
            'fields' => ['type' => 'array', 'items' => ['type' => 'string'], 'maxItems' => 2],
            'active' => ['type' => 'boolean'],
            'name' => ['type' => 'string', 'minLength' => 2, 'maxLength' => 5],
        ],
        'required' => ['entity'],
        'additionalProperties' => false,
    ];

    public function test_a_valid_input_has_no_error(): void
    {
        $this->assertSame([], InputValidator::validate($this->schema, ['entity' => 'prospect', 'limit' => 10, 'fields' => ['a'], 'active' => true, 'name' => 'abc']));
    }

    public function test_an_empty_input_is_valid_when_nothing_is_required(): void
    {
        $this->assertSame([], InputValidator::validate(['type' => 'object', 'properties' => [], 'additionalProperties' => false], []));
    }

    /**
     * @dataProvider invalidInputs
     */
    public function test_an_invalid_input_is_rejected(array $input): void
    {
        $this->assertNotSame([], InputValidator::validate($this->schema, $input));
    }

    public static function invalidInputs(): array
    {
        return [
            'missing required' => [[]],
            'unknown parameter' => [['entity' => 'prospect', 'sql' => 'drop table']],
            'wrong enum' => [['entity' => 'passwords']],
            'wrong type' => [['entity' => 'prospect', 'limit' => '10']],
            'below minimum' => [['entity' => 'prospect', 'limit' => 0]],
            'above maximum' => [['entity' => 'prospect', 'limit' => 5000]],
            'too many items' => [['entity' => 'prospect', 'fields' => ['a', 'b', 'c']]],
            'wrong item type' => [['entity' => 'prospect', 'fields' => [1]]],
            'too short' => [['entity' => 'prospect', 'name' => 'a']],
            'too long' => [['entity' => 'prospect', 'name' => 'abcdefgh']],
            'a list for an object value' => [['entity' => ['prospect']]],
        ];
    }

    public function test_the_registry_keeps_a_fixed_order_and_only_the_asked_tools(): void
    {
        $registry = new ToolRegistry();
        $registry->register(new FakeAgentTool('zeta'));
        $registry->register(new FakeAgentTool('alpha'));
        $registry->register(new FakeAgentTool('mid'));

        $this->assertSame(['alpha', 'mid', 'zeta'], array_keys($registry->all()));
        $this->assertSame(['alpha', 'zeta'], array_column($registry->definitions(['zeta', 'alpha', 'unknown']), 'name'));
    }

    public function test_a_tool_with_an_open_schema_or_a_bad_name_cannot_be_registered(): void
    {
        $registry = new ToolRegistry();

        $open = new class extends FakeAgentTool {
            public function inputSchema(): array
            {
                return ['type' => 'object', 'properties' => []];
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $registry->register($open);
    }

    public function test_a_tool_name_the_api_would_refuse_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new ToolRegistry())->register(new FakeAgentTool('not valid!'));
    }

    public function test_an_unknown_risk_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new ToolRegistry())->register(new FakeAgentTool('x', 'dangerous'));
    }
}
