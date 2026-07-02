<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

class ProspectAttachLabelAction extends Action
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
            ->where('categories.for', 'prospect')
            ->where('labels.id', $this->action->value['label'])
            ->where('categories.project_id', $this->model->project_id)
            ->first(['labels.id']);
    
        if (!$label) {
            return;
        }

        $this->model
            ->labels()
            ->withPivot('created_at', 'updated_at')
            ->syncWithoutDetaching([$label->id => [
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);
    }
}