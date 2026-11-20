<?php

namespace Tests\Support;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\ChecksBeforeApproval;
use App\Agents\Tools\ToolResult;

/**
 * A fake tool decided by a human that may refuse before asking: it counts the checks it made.
 */
class PrecheckingFakeTool extends FakeAgentTool implements ChecksBeforeApproval
{
    public int $prechecks = 0;

    public ?ToolResult $refusal = null;

    public ?\Throwable $precheckThrows = null;

    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        $this->prechecks++;

        if ($this->precheckThrows) {
            throw $this->precheckThrows;
        }

        return $this->refusal;
    }
}
