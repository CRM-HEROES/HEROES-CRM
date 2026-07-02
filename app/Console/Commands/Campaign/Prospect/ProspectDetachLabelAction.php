<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

/**
 * Detach label
 * from the prospect
 * 
 * Data format:
 * {
 *    label: {label ID},
 * }
 */
class ProspectDetachLabelAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (!$this->model instanceof Prospect) {
            return;
        }
        
        if (!$this->action->value) {
            return;
        }
        
        if (!$this->action->value['label']) {
            return;
        }

        $label = DB::table('labels')
            ->join('categories', 'categories.id', '=', 'labels.category_id')
            ->where('labels.id', $this->action->value['label'])
            ->where('categories.project_id', $this->model->project_id)
            ->first(['labels.id']);
    
        if (!$label) {
            return;
        }

        /*DB::table('prospect_label')
            ->where('prospect_id', $this->model->id)
            ->where('label_id', $label->id)
            ->update(['deleted_at' => DB::raw('NOW()')]);*/
        
        $this->model->labels()->detach($label->id);
    }
}