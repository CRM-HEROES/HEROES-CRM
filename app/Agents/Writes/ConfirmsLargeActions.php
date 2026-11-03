<?php

namespace App\Agents\Writes;

use App\Agents\AgentExecutionContext;

/**
 * A tool whose effect grows with the number of records it reaches. Above
 * agents.bulk_confirm_threshold records, a human approves the call even when
 * the agent is allowed to act alone.
 */
interface ConfirmsLargeActions
{
    /**
     * How many records the call would reach. Cheap (a count), run as the owner.
     */
    public function affectedCount(AgentExecutionContext $context, array $input): int;
}
