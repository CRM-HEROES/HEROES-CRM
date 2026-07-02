<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Action;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Detach step from an order
 * 
 * Data format:
 * {
 *    step: {step ID}
 * }
 */
class OrderDetachStepAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (
            !$this->model instanceof Order ||
            !$this->action->value ||
            !isset($this->action->value['step']) || !$this->action->value['step']
        ) {
            return null;
        }

        // Order step
        $step = DB::table('order_steps')
            ->where('id', $this->action->value['step'])
            ->where('project_id', $this->model->prospect->project_id)
            ->first(['id']);
    
        if (!$step) {
            return null;
        }

        $this->model
            ->steps()
            ->detach($step->id);
    }
}