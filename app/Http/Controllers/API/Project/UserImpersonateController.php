<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Lab404\Impersonate\Services\ImpersonateManager;

class UserImpersonateController extends Controller
{
    /**
     * Impersonate other user
     */
    public function impersonate(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('', $project), 404);
        abort_unless($user->projects()->where('id', $project->id)->first(), 404);
        
        $manager = app('impersonate');

        if ($manager->isImpersonating()) {
            $impersonatingUser = User::find($manager->getImpersonatorId());
        } else {
            $impersonatingUser = auth()->user();
        }

        if ($impersonatingUser->id != $user->id) {
            $impersonatingUser->impersonate($user);
            session(['impersonate_project' => $project->slug]);
        }

        return redirect()->route('project.prospect.index', [$project->slug]);
    }

    /**
     * Leave impersonate
     */
    public function leaveImpersonation(ImpersonateManager $manager, Project $project)
    {
        abort_unless(auth()->user()->can('', $project), 404);

    	$user = User::find($manager->getImpersonatorId());
        // abort_unless($user->projects()->where('id', $project->id)->first(), 401);

    	if ($user) {
	        auth("web")->quietLogout();
	        auth("web")->quietLogin($user);

	        $manager->clear();
	        session()->forget('impersonate_project');
        }
        
        return redirect()->route('project.prospect.index', [$project]);
    }
}
