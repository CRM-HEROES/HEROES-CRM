<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Action;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Attach label to an order
 * 
 * Data format:
 * {
 *    label: {label ID}
 * }
 */
class OrderAttachLabelAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (
            !$this->model instanceof Order ||
            !$this->action->value ||
            !isset($this->action->value['label']) || !$this->action->value['label']
        ) {
            return;
        }

        $label = DB::table('labels')
            ->join('categories', 'categories.id', '=', 'labels.category_id')
            ->where('categories.for', 'order')
            ->where('labels.id', $this->action->value['label'])
            ->where('categories.project_id', $this->model->project_id)
            ->first(['labels.id']);
    
        if (!$label) {
            return;
        }

        $this->model
            ->labels()
            ->withPivot(['created_at', 'updated_at'])
            ->syncWithoutDetaching([$label->id => [
                'created_at'    => \Carbon\Carbon::now(),
                'updated_at'    => \Carbon\Carbon::now(),
            ]]);
    }
}