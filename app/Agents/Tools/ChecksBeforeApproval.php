<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;

/**
 * A tool that decides by a human can refuse a request that will certainly fail BEFORE asking the human.
 *
 * Without it, the human validates an action and only then learns it is refused (a missing place, a protected user, a
 * prospect who does not want to be contacted). The check must change nothing: it runs when the action is proposed, and
 * the tool checks again when it runs, because things may have changed in between.
 */
interface ChecksBeforeApproval
{
    /**
     * The refusal the tool would give anyway, or null when the request may be proposed.
     */
    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult;
}
