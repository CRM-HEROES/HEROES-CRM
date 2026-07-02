<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

/**
 * Detach group
 * from the prospect
 * 
 * Data format:
 * {
 *    group: {group ID},
 * }
 */
class ProspectDetachGroupAction extends Action
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
        
        if (!$this->action->value['group']) {
            return;
        }

        $group = DB::table('groups')
            ->where('groups.id', $this->action->value['group'])
            ->where('project_id', $this->model->project_id)
            ->first(['id']);
    
        if (!$group) {
            return;
        }

        $this->model->groups()->detach($group->id);
    }
}