<?php

namespace App\Agents\Claude;

/**
 * An answer of the model, in the shape the CRM stores: the content blocks are
 * the arrays of the wire format (thinking blocks and their signature
 * included), kept as they are returned and replayed unchanged.
 */
final class ClaudeResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $model,
        public readonly array $content,
        public readonly ?string $stopReason,
        public readonly int $inputTokens = 0,
        public readonly int $outputTokens = 0,
        public readonly int $cacheReadTokens = 0,
        public readonly int $cacheWriteTokens = 0,
        public readonly ?string $refusalCategory = null,
    ) {
    }

    /**
     * The tool calls of the answer
     *
     * @return array<int, array{id: string, name: string, input: array}>
     */
    public function toolUses(): array
    {
        $uses = [];

        foreach ($this->content as $block) {
            if (($block['type'] ?? null) === 'tool_use') {
                $uses[] = [
                    'id' => (string) $block['id'],
                    'name' => (string) $block['name'],
                    'input' => is_array($block['input'] ?? null) ? $block['input'] : [],
                ];
            }
        }

        return $uses;
    }

    /**
     * The text blocks of the answer, joined
     */
    public function text(): string
    {
        $texts = [];

        foreach ($this->content as $block) {
            if (($block['type'] ?? null) === 'text' && ($block['text'] ?? '') !== '') {
                $texts[] = $block['text'];
            }
        }

        return implode("\n\n", $texts);
    }
}
