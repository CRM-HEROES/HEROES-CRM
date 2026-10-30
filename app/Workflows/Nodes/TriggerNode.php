<?php

namespace App\Workflows\Nodes;

use App\Workflows\NodeResult;

/**
 * Entry of the workflow. The event already happened (that is why the
 * execution exists): the node only appears in the execution log.
 */
class TriggerNode implements NodeExecutorInterface
{
    public function execute(NodeContext $context): NodeResult
    {
        return NodeResult::completed(['trigger' => $context->config()['trigger'] ?? null]);
    }
}
