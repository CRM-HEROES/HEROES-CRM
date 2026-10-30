<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowExecution;
use Illuminate\Database\Eloquent\Model;

/**
 * An action of a workflow node.
 *
 * It must be retry-safe as far as possible and throw when it fails: the
 * engine records the error on the node and stops the execution. The engine
 * never runs a node twice (see WorkflowEngine), so a handler is not called
 * again after a crash; it should not hold a database transaction open while
 * it calls an external service.
 */
interface ActionHandlerInterface
{
    /**
     * @param  Model  $subject  prospect / order / event of the execution
     * @param  array  $config  configuration of the node
     * @return array data to keep in the execution log (no secret)
     */
    public function handle(Model $subject, array $config, WorkflowExecution $execution): array;
}
