<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Action;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Attach step to an order
 * 
 * Data format:
 * {
 *    step: {step ID}
 * }
 */
class OrderAttachStepAction extends Action
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
            ->withPivot(['created_at', 'updated_at'])
            ->syncWithoutDetaching([$step->id => [
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]]);
    }
}