<?php

namespace App\Workflows\Nodes;

use Carbon\CarbonInterface;

/**
 * A node that may have to wait for an external service before running.
 * Asked by the engine before the node is started (so nothing is half done).
 */
interface ThrottledNodeInterface
{
    /**
     * @return CarbonInterface|null null: run now; a date: postpone the node until then
     */
    public function delay(NodeContext $context): ?CarbonInterface;
}
