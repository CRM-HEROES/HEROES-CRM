<?php

namespace Tests\Support;

use App\Agents\Claude\ClaudeException;
use App\Agents\Claude\ClaudeGateway;
use App\Agents\Claude\ClaudeResponse;

/**
 * A gateway that answers from a script and records the requests it gets.
 * When the script is over it keeps answering with its last item.
 */
class ScriptedGateway implements ClaudeGateway
{
    /** @var array<int, array> the requests received */
    public array $requests = [];

    /**
     * @param  array<int, ClaudeResponse|ClaudeException>  $script
     */
    public function __construct(protected array $script)
    {
    }

    public function send(array $request): ClaudeResponse
    {
        $this->requests[] = $request;

        $next = $this->script[min(count($this->requests), count($this->script)) - 1];

        if ($next instanceof ClaudeException) {
            throw $next;
        }

        return $next;
    }

    public static function text(string $text, string $stop = 'end_turn', int $in = 100, int $out = 50): ClaudeResponse
    {
        return new ClaudeResponse('msg_' . uniqid(), 'claude-opus-5-5', [
            ['type' => 'text', 'text' => $text],
        ], $stop, $in, $out);
    }

    public static function toolUse(string $name, array $input = [], ?string $id = null, int $in = 100, int $out = 50): ClaudeResponse
    {
        return new ClaudeResponse('msg_' . uniqid(), 'claude-opus-5-5', [
            ['type' => 'thinking', 'thinking' => '', 'signature' => 'SIG=='],
            ['type' => 'text', 'text' => 'Je regarde.'],
            ['type' => 'tool_use', 'id' => $id ?? 'toolu_' . uniqid(), 'name' => $name, 'input' => $input, 'caller' => ['type' => 'direct']],
        ], 'tool_use', $in, $out);
    }
}
