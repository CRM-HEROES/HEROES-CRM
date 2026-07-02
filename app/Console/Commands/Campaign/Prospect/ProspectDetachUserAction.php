<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

/**
 * Detach user
 * from the prospect
 * 
 * Data format:
 * {
 *    user: {user ID},
 * }
 */
class ProspectDetachUserAction extends Action
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
        
        if (!$this->action->value['user']) {
            return;
        }

        $user = DB::table('users')
            ->join('user_project', 'user_project.user_id', '=', 'users.id')
            ->where('users.id', $this->action->value['user'])
            ->where('user_project.project_id', $this->model->project_id)
            ->first(['users.id']);
    
        if (!$user) {
            return;
        }

        $this->model->users()->detach($user->id);
    }
}