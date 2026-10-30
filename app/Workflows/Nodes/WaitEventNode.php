<?php

namespace App\Workflows\Nodes;

use App\Workflows\NodeResult;

/**
 * "Wait for an event": { "event": "sms_received", "hours": 24 }
 *
 * Parks the workflow until the prospect does something (see EVENTS), for at
 * most the given time (minutes / hours / days, required: a workflow never
 * waits forever). Two ways out: "yes" when the event happened, "no" when the
 * time ran out. Which one is decided by the engine when the node is resumed
 * (WorkflowEngine::resumeEventWait).
 *
 * Only what happens AFTER the workflow reached this node counts. The events
 * are those of the prospect of the workflow; this node is only available for
 * workflows that run on prospects.
 */
class WaitEventNode implements NodeExecutorInterface
{
    /**
     * The events a workflow can wait for. They are reported by the CRM code
     * that creates the corresponding record (WorkflowEventObserver).
     */
    public const EVENTS = [
        'sms_received',        // the prospect sent a SMS
        'message_received',    // the prospect wrote a message / replied to an email
        'call_received',       // the prospect called
        'appointment_created', // an appointment was booked for the prospect
        'order_created',       // a quote was created for the prospect
    ];

    public function execute(NodeContext $context): NodeResult
    {
        $config = $context->config();
        $seconds = WaitNode::seconds($config);

        if (!in_array($config['event'] ?? null, self::EVENTS, true) || $seconds <= 0
            || $seconds > config('workflows.max_wait_days') * 86400) {
            return NodeResult::failed('Invalid event wait.');
        }

        $timeoutAt = now()->addSeconds($seconds);

        return NodeResult::waitForEvent($config['event'], $timeoutAt, [
            'event' => $config['event'],
            'timeout_at' => $timeoutAt->toIso8601String(),
        ]);
    }
}
