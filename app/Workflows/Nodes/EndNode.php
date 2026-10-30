<?php

namespace App\Workflows\Nodes;

use App\Workflows\NodeResult;

class EndNode implements NodeExecutorInterface
{
    public function execute(NodeContext $context): NodeResult
    {
        return NodeResult::end();
    }
}
