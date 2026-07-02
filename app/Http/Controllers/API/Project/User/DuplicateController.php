<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuplicateController extends Controller
{
    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateRoles(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("roles", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $user2
            ->roles()
            ->detach($user2->roles()->where('roles.project_id', $project->id)->get()->pluck('id'));

        $roles = [];

        foreach ($user
                ->roles()
                ->where('roles.project_id', $project->id)
                ->get()
                ->pluck('id') as $role) {
            $roles[$role] = [
                'project_id' => $project->id,
            ];
        }

        $user2
            ->roles()
            ->syncWithoutDetaching($roles);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicatePermissions(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("permissions", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $permissions = DB::table('model_has_permissions')
            ->where('project_id', $project->id)
            ->where('model_id', $user->id)
            ->where('model_type', 'App\Models\User')
            ->get();
            
        DB::table('model_has_permissions')
            ->where('project_id', $project->id)
            ->where('model_id', $user2->id)
            ->where('model_type', 'App\Models\User')
            ->delete();

        DB::table('model_has_permissions')->insert($permissions->map(function($permission) use($user2) {
            return [
                'permission_id' => $permission->permission_id,
                'project_id' => $permission->project_id,
                'model_type' => $permission->model_type,
                'model_id' => $user2->id,
            ];
        })->toArray());

        $user2->forgetCachedPermissions();
        $user2->load('permissions');

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateFolders(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("folders", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $folders =
            $user
                ->folders()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $user2
            ->folders()
            ->detach($user2->folders()->where('project_id', $project->id)->get()->pluck('id'));

        $user2
            ->folders()
            ->syncWithoutDetaching($folders);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateThreads(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("threads", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $threads =
            $user
                ->threads()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $user2
            ->threads()
            ->detach($user2->threads()->where('project_id', $project->id)->get()->pluck('id'));

        $user2
            ->threads()
            ->syncWithoutDetaching($threads);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateGroups(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("groups", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $groups =
            $user
                ->groups()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $user2
            ->groups()
            ->detach($user2->groups()->where('project_id', $project->id)->get()->pluck('id'));

        $user2
            ->groups()
            ->syncWithoutDetaching($groups);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateCategories(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("categories", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $categories = 
            $user
                ->categories()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $user2
            ->categories()
            ->detach($user2->categories()->where('project_id', $project->id)->get()->pluck('id'));

        $user2
            ->categories()
            ->syncWithoutDetaching($categories);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateUsers(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("users", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $assignableUsers = DB::table('user_assignable_user')
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->get();
            
        DB::table('user_assignable_user')
            ->where('project_id', $project->id)
            ->where('user_id', $user2->id)
            ->delete();

        DB::table('user_assignable_user')->insert($assignableUsers->map(function($assignableUser) use($user2) {
            return [
                'project_id' => $assignableUser->project_id,
                'user_id' => $user2->id,
                'assignable_user_id' => $assignableUser->assignable_user_id,
            ];
        })->toArray());

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateQuestionnaires(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("questionnaires", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $questionnaires =
            $user
                ->questionnaires()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $user2
            ->questionnaires()
            ->detach($user2->questionnaires()->where('project_id', $project->id)->get()->pluck('id'));

        $user2
            ->questionnaires()
            ->syncWithoutDetaching($questionnaires);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateDocuments(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("documents", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $documents =
            $user
                ->documents()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $user2
            ->documents()
            ->detach($user2->documents()->where('project_id', $project->id)->get()->pluck('id'));

        $user2
            ->documents()
            ->syncWithoutDetaching($documents);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateCalendars(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("calendars", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $calendars =
            $user
                ->calendars()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $user2
            ->calendars()
            ->detach($user2->calendars()->where('project_id', $project->id)->get()->pluck('id'));

        $user2
            ->calendars()
            ->syncWithoutDetaching($calendars);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateMenus(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("menus", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $user2
            ->menus()
            ->detach($user2->menus()->where('project_id', $project->id)->get()->pluck('id'));

        $menus = [];

        foreach ($user2->menus()->where('project_id', $project->id)->get() as $menu) {
            $menus[$menu->id] = [
                'order' => $menu->pivot->order,
            ];
        }

        $user2->menus()->syncWithoutDetaching($menus);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateSetting(Request $request, Project $project, User $user, User $user2, $key)
    {
        if (array_search("setting->" . $key, $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $userSetting = UserSetting::where([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'key' => $key,
            ])
            ->first();

        if (!$userSetting) {
            return false;
        }

        UserSetting::updateOrCreate([
            'project_id' => $project->id,
            'user_id' => $user2->id,
            'key' => $key,
        ], [
            'value' => $userSetting->value,
            'creator_id' => auth()->id(),
        ]);
        
        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateProspects(Request $request, Project $project, User $user, User $user2)
    {
        if (array_search("prospects", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $prospects =
            $user
                ->prospects()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $user2
            ->prospects()
            ->detach($user2->prospects()->where('project_id', $project->id)->get()->pluck('id'));

        $user2
            ->prospects()
            ->syncWithoutDetaching($prospects);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateProspectsTableSetting(Request $request, Project $project, User $user, User $user2)
    {
        return $this->duplicateSetting($request, $project, $user, $user2, 'prospects-table');
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateProspectsTableMenuSetting(Request $request, Project $project, User $user, User $user2)
    {
        return $this->duplicateSetting($request, $project, $user, $user2, 'prospects-table.menus');
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    private function duplicateProspectProfileSetting(Request $request, Project $project, User $user, User $user2)
    {
        return $this->duplicateSetting($request, $project, $user, $user2, 'prospect-profile');
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @param User $user2
     */
    public function duplicate(Request $request, Project $project, User $user)
    {
        abort_unless($user->projects()->where('id', $project->id)->first(), 401);
        
    	if (!$request->has('users') || empty($request->input('users')) || !is_array($request->input('users'))) {
    		return response('Aucun utilisateur avec un profil à dupliquer n\'a été trouvé.', 400);
    	}

        $users = $project
            ->users()
            ->whereIn('id', $request->input('users'))
            ->get();

    	foreach ($users as $user2) {
            $this->duplicateRoles($request, $project, $user, $user2);
            $this->duplicatePermissions($request, $project, $user, $user2);
            $this->duplicateFolders($request, $project, $user, $user2);
            $this->duplicateThreads($request, $project, $user, $user2);
            $this->duplicateGroups($request, $project, $user, $user2);
            $this->duplicateCategories($request, $project, $user, $user2);
            $this->duplicateUsers($request, $project, $user, $user2);
            $this->duplicateProspectsTableSetting($request, $project, $user, $user2);
            $this->duplicateProspectsTableMenuSetting($request, $project, $user, $user2);
            $this->duplicateProspectProfileSetting($request, $project, $user, $user2);
            $this->duplicateMenus($request, $project, $user, $user2);
            $this->duplicateQuestionnaires($request, $project, $user, $user2);
            $this->duplicateDocuments($request, $project, $user, $user2);
            $this->duplicateCalendars($request, $project, $user, $user2);
            $this->duplicateProspects($request, $project, $user, $user2);
        }

        return [
        	'message' => "Le profil de l'utilisateur a été bien copié."
    	];
    }
}
