<?php

namespace App\Workflows\Nodes;

use App\Workflows\NodeResult;

interface NodeExecutorInterface
{
    public function execute(NodeContext $context): NodeResult;
}
