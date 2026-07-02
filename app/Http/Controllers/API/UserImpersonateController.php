<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Lab404\Impersonate\Services\ImpersonateManager;

class UserImpersonateController extends Controller
{
    /**
     * Impersonate user
     */
    public function impersonate(User $user)
    {
        abort_unless(auth()->user()->is_super_admin, 404);

        $manager = app('impersonate');

        if ($manager->isImpersonating()) {
            $impersonatingUser = User::find($manager->getImpersonatorId());
        } else {
            $impersonatingUser = auth()->user();
        }

        if ($impersonatingUser->id != $user->id) {
            $impersonatingUser->impersonate($user);
        }

        return ['message' => trans('common.success.user_impersonated')];
    }
    
    /**
     * Leave impersonate user
     */
    public function leaveImpersonation(ImpersonateManager $manager)
    {
    	$user = User::find($manager->getImpersonatorId());
        // abort_unless($user->projects()->where('id', $project->id)->first(), 401);

    	if ($user) {
	        auth("web")->quietLogout();
	        auth("web")->quietLogin($user);

	        $manager->clear();
	        session()->forget('impersonate_project');
        }
        
        return ['message' => trans('common.success.leave_impersonate')];
    }
}
