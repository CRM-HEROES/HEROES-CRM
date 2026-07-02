<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class Policy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->is_super_admin) {
            return true;
        }
        
        // The project creator
        // should have all permissions
        // inside the project
        if (request()->project && request()->project->creator_id == $user->id) {
            return true;
        }
        
        // return true;
    }

    public function can(User $user, $permission)
    {
        while (true) {
            if ($user->can($permission)) {
                return true;
            }

            $lastDotIndex = strrpos($permission, ".");

            if ($lastDotIndex === FALSE) {
                return false;
            }

            $permission = substr($permission, 0, $lastDotIndex);
        }
    }
}