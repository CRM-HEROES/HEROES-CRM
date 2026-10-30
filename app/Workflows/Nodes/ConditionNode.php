<?php

namespace App\Workflows\Nodes;

use App\Workflows\ConditionEvaluator;
use App\Workflows\NodeResult;
use App\Workflows\WorkflowGraph;

/**
 * "If": follows the "yes" or the "no" edge.
 */
class ConditionNode implements NodeExecutorInterface
{
    public function __construct(protected ConditionEvaluator $evaluator)
    {
    }

    public function execute(NodeContext $context): NodeResult
    {
        if (!$context->subject()) {
            return NodeResult::cancelled('The prospect / order / appointment does not exist anymore.');
        }

        $matches = $this->evaluator->evaluate($context->execution, $context->config());

        return NodeResult::completed(
            ['matches' => $matches],
            $matches ? WorkflowGraph::YES : WorkflowGraph::NO
        );
    }
}
