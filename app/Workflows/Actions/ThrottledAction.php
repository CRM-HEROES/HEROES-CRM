<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowExecution;
use Carbon\CarbonInterface;

/**
 * An action that must not be run faster than an external service accepts.
 *
 * The engine asks BEFORE running the node: when the service is saturated, the
 * node is not executed and the execution is parked as "waiting" until the
 * returned date (nothing blocks, nothing is lost, nothing is sent twice).
 */
interface ThrottledAction
{
    /**
     * @return CarbonInterface|null null: go ahead; a date: try again then
     */
    public function delay(WorkflowExecution $execution, array $config): ?CarbonInterface;
}
