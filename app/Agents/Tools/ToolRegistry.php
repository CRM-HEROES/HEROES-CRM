<?php

namespace App\Agents\Tools;

/**
 * The tools an agent can use (same role as the ActionRegistry of the
 * workflows). A new tool is registered in AppServiceProvider.
 */
class ToolRegistry
{
    /**
     * @var array<string, AgentToolInterface>
     */
    protected array $tools = [];

    public function register(AgentToolInterface $tool): void
    {
        $key = $tool->key();

        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $key)) {
            throw new \InvalidArgumentException("Invalid tool name: $key");
        }

        $schema = $tool->inputSchema();

        // The API guarantees the arguments only for a strict, closed schema
        if (($schema['type'] ?? null) !== 'object' || ($schema['additionalProperties'] ?? null) !== false) {
            throw new \InvalidArgumentException("The input schema of the tool $key must be an object with additionalProperties: false.");
        }

        if (!in_array($tool->risk(), AgentToolInterface::RISKS, true)) {
            throw new \InvalidArgumentException("The tool $key has an unknown risk: {$tool->risk()}.");
        }

        $this->tools[$key] = $tool;
    }

    public function get(string $key): ?AgentToolInterface
    {
        return $this->tools[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->tools[$key]);
    }

    /**
     * @return array<string, AgentToolInterface>
     */
    public function all(): array
    {
        $tools = $this->tools;
        ksort($tools);

        return $tools;
    }

    /**
     * Definitions sent to the model, in a fixed order: the list of tools is
     * the first part of the prompt cache, any change of order would break it.
     *
     * @param  string[]  $keys
     * @return array<int, array{name: string, description: string, schema: array}>
     */
    public function definitions(array $keys): array
    {
        $definitions = [];

        foreach ($this->all() as $key => $tool) {
            if (in_array($key, $keys, true)) {
                $definitions[] = [
                    'name' => $key,
                    'description' => $tool->description(),
                    'schema' => $tool->inputSchema(),
                ];
            }
        }

        return $definitions;
    }
}
