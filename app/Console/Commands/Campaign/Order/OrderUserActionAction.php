<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Action;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Associate user
 * with an action on the order
 * 
 * Data format:
 * {
 *    action: {action ID},
 *    user: {user ID, or 'creator' or 'prospect-creator' or 'prospect-affected-user'},
 * }
 */
class OrderUserActionAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (
            !$this->model instanceof Order ||
            !$this->action->value ||
            !isset($this->action->value['action']) || !$this->action->value['action'] ||
            !isset($this->action->value['user']) || !$this->action->value['user']
        ) {
            return null;
        }

        $action = DB::table('order_actions')
            ->where('id', $this->action->value['action'])
            ->where('project_id', $this->model->prospect->project_id)
            ->first(['id']);
    
        if (!$action) {
            return null;
        }

        $userId = $this->getUser($this->action->value['user']);
        
        if (!$userId) {
            return;
        }

        $this->model
            ->actions()
            ->withPivot(['user_id', 'created_at', 'updated_at'])
            ->syncWithoutDetaching([$action->id => [
                'user_id' => $userId,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]]);
    }

    protected function getUser($userId)
    {
        if ($userId == 'order-creator') {
            return $this->model->creator_id;
        } else if ($userId == 'prospect-creator') {
            return $this->model->prospect->creator_id;
        } else if ($userId == 'prospect-affected-user') {
            $user = $this->model->prospect
                ->users()
                ->first(['id']);

            if ($user) {
                return $user->id;
            }
        } else if (Str::startsWith($userId, 'role.')) {
            $roleId = Str::replace('role.', '', $userId);

            $role = DB::table('roles')
                ->where('project_id', $this->model->prospect->project_id)
                ->where('id', $roleId)
                ->first(['id']);

            if ($role) {
                    
                $user = DB::table('model_has_roles')
                    ->where('role_id', $role->id)
                    ->where('model_type', "App\\Models\\User")
                    ->first(['model_id']);

                if ($user) {
                    return $user->model_id;
                }
            }
        } else {
            $user = DB::table('user_project')
                ->where('project_id', $this->model->prospect->project_id)
                ->where('user_id', $userId)
                ->first(['id']);

            if ($user) {
                return $user->id;
            }
        }

        return null;
    }
}