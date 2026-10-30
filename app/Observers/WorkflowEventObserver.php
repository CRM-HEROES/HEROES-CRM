<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\Interaction;
use App\Models\Message;
use App\Models\Order;
use App\Models\Sms;
use App\Workflows\WorkflowEvents;
use Illuminate\Database\Eloquent\Model;

/**
 * Reports to the workflow engine what a prospect does, for the workflows that
 * wait for it ("wait for an event" node, see WaitEventNode::EVENTS).
 *
 * What the prospect writes or does is told apart from what the users do by
 * the same flags the CRM uses (from_user, is_incoming).
 */
class WorkflowEventObserver
{
    public function created(Model $model): void
    {
        switch (true) {
            case $model instanceof Sms:
                // Same rule as IncomingCommunications
                if ($model->is_incoming || !$model->from_user) {
                    WorkflowEvents::awaited('sms_received', $model->prospect_id);
                }
                break;

            case $model instanceof Message:
                if (!$model->from_user) {
                    WorkflowEvents::awaited('message_received', $model->prospect_id);
                }
                break;

            case $model instanceof Interaction:
                if (!$model->from_user) {
                    WorkflowEvents::awaited('call_received', $model->prospect_id);
                }
                break;

            case $model instanceof Event:
                WorkflowEvents::awaited('appointment_created', $model->prospect_id);
                break;

            case $model instanceof Order:
                WorkflowEvents::awaited('order_created', $model->prospect_id);
                break;
        }
    }
}
