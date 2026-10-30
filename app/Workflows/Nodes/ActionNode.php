<?php

namespace App\Workflows\Nodes;

use App\Workflows\Actions\ActionRegistry;
use App\Workflows\Actions\ThrottledAction;
use App\Workflows\NodeResult;
use App\Workflows\WorkflowTriggerResolver;
use Carbon\CarbonInterface;

/**
 * "Do": { "action": "send_sms", "provider": "sms:smsbox", "message": "...", "on_error": "fail" | "continue" }
 * (the legacy campaign actions keep their own format: { "action": "prospect-attach-label", "value": {...} })
 */
class ActionNode implements NodeExecutorInterface, ThrottledNodeInterface
{
    public function __construct(protected ActionRegistry $actions)
    {
    }

    /**
     * Rate limit of the operator used by the action
     */
    public function delay(NodeContext $context): ?CarbonInterface
    {
        $handler = $this->actions->get($context->config()['action'] ?? '');

        return $handler instanceof ThrottledAction
            ? $handler->delay($context->execution, $context->config())
            : null;
    }

    public function execute(NodeContext $context): NodeResult
    {
        $config = $context->config();
        $subject = $context->subject();

        if (!$subject) {
            return NodeResult::cancelled('The prospect / order / appointment does not exist anymore.');
        }

        $type = $config['action'] ?? '';

        if (!$this->actions->has($type, $context->execution->subject_type)) {
            return NodeResult::failed("Unknown action: $type");
        }

        try {
            // What the action changes must not start workflows again
            // (itself included): the CRM events it raises are not recorded.
            $data = WorkflowTriggerResolver::withoutTriggers(
                fn () => $this->actions->handle($type, $subject, $config, $context->execution)
            );
        } catch (\Throwable $e) {
            report($e);

            // An operator problem does not have to stop the whole workflow.
            // The operator is kept in the log (reference only), for the figures per operator.
            return NodeResult::failed(
                $e->getMessage(),
                isset($config['provider']) ? ['provider' => $config['provider']] : [],
                ($config['on_error'] ?? 'fail') === 'continue'
            );
        }

        return NodeResult::completed(['action' => $type] + $data);
    }
}
