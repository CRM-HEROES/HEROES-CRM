<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuplicateController extends Controller
{
    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param Role $role
     * @param Role $role2
     */
    private function duplicatePermissions(Request $request, Project $project, Role $role, Role $role2)
    {
        if (array_search("permissions", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $permissions = DB::table('role_has_permissions')
            // ->where('project_id', $project->id)
            ->where('role_id', $role->id)
            ->get();
            
        DB::table('role_has_permissions')
            // ->where('project_id', $project->id)
            ->where('role_id', $role2->id)
            ->delete();

        DB::table('role_has_permissions')->insert($permissions->map(function($permission) use($role2) {
            return [
                'permission_id' => $permission->permission_id,
                // 'project_id' => $permission->project_id,
                'role_id' => $role2->id,
            ];
        })->toArray());

        $role2->forgetCachedPermissions();
        $role2->load('permissions');

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param Role $role
     * @param Role $role2
     */
    private function duplicateFolders(Request $request, Project $project, Role $role, Role $role2)
    {
        if (array_search("folders", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $folders =
            $role
                ->folders()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $role2
            ->folders()
            ->detach($role2->folders()->where('project_id', $project->id)->get()->pluck('id'));

        $role2
            ->folders()
            ->syncWithoutDetaching($folders);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param Role $role
     * @param Role $role2
     */
    private function duplicateThreads(Request $request, Project $project, Role $role, Role $role2)
    {
        if (array_search("threads", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $threads =
            $role
                ->threads()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $role2
            ->threads()
            ->detach($role2->threads()->where('project_id', $project->id)->get()->pluck('id'));

        $role2
            ->threads()
            ->syncWithoutDetaching($threads);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param Role $role
     * @param Role $role2
     */
    private function duplicateCategories(Request $request, Project $project, Role $role, Role $role2)
    {
        if (array_search("categories", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $categories = 
            $role
                ->categories()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $role2
            ->categories()
            ->detach($role2->categories()->where('project_id', $project->id)->get()->pluck('id'));

        $role2
            ->categories()
            ->syncWithoutDetaching($categories);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param Role $role
     * @param Role $role2
     */
    private function duplicateQuestionnaires(Request $request, Project $project, Role $role, Role $role2)
    {
        if (array_search("questionnaires", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $questionnaires =
            $role
                ->questionnaires()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $role2
            ->questionnaires()
            ->detach($role2->questionnaires()->where('project_id', $project->id)->get()->pluck('id'));

        $role2
            ->questionnaires()
            ->syncWithoutDetaching($questionnaires);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param Role $role
     * @param Role $role2
     */
    private function duplicateDocuments(Request $request, Project $project, Role $role, Role $role2)
    {
        if (array_search("documents", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $documents =
            $role
                ->documents()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $role2
            ->documents()
            ->detach($role2->documents()->where('project_id', $project->id)->get()->pluck('id'));

        $role2
            ->documents()
            ->syncWithoutDetaching($documents);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param Role $role
     * @param Role $role2
     */
    private function duplicateCalendars(Request $request, Project $project, Role $role, Role $role2)
    {
        if (array_search("calendars", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $calendars =
            $role
                ->calendars()
                ->where('project_id', $project->id)
                ->get()
                ->pluck('id');

        $role2
            ->calendars()
            ->detach($role2->calendars()->where('project_id', $project->id)->get()->pluck('id'));

        $role2
            ->calendars()
            ->syncWithoutDetaching($calendars);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param Role $role
     * @param Role $role2
     */
    private function duplicateMenus(Request $request, Project $project, Role $role, Role $role2)
    {
        return false;
        if (array_search("menus", $request->input('duplicatables')) === FALSE) {
            return false;
        }

        $role2
            ->menus()
            ->detach($role2->menus()->where('project_id', $project->id)->get()->pluck('id'));

        $menus = [];

        foreach ($role2->menus()->where('project_id', $project->id)->get() as $menu) {
            $menus[$menu->id] = [
                'order' => $menu->pivot->order,
            ];
        }

        $role2->menus()->syncWithoutDetaching($menus);

        return true;
    }

    /**
     * Duplicate 
     * 
     * @param Request $request
     * @param Project $project
     * @param Role $role
     * @param Role $role2
     */
    public function duplicate(Request $request, Project $project, Role $role)
    {
        abort_unless($project->roles()->where('id', $role->id)->first(), 401);
        
    	if (!$request->has('roles') || empty($request->input('roles')) || !is_array($request->input('roles'))) {
    		return response('Aucun utilisateur avec un profil à dupliquer n\'a été trouvé.', 400);
    	}

        $roles = $project
            ->roles()
            ->whereIn('id', $request->input('roles'))
            ->get();

    	foreach ($roles as $role2) {
            // $this->duplicateRoles($request, $project, $role, $role2);
            $this->duplicatePermissions($request, $project, $role, $role2);
            $this->duplicateFolders($request, $project, $role, $role2);
            $this->duplicateThreads($request, $project, $role, $role2);
            // $this->duplicateGroups($request, $project, $role, $role2);
            $this->duplicateCategories($request, $project, $role, $role2);
            // $this->duplicateRoles($request, $project, $role, $role2);
            // $this->duplicateProspectsTableSetting($request, $project, $role, $role2);
            // $this->duplicateProspectProfileSetting($request, $project, $role, $role2);
            // $this->duplicateMenus($request, $project, $role, $role2);
            $this->duplicateQuestionnaires($request, $project, $role, $role2);
            $this->duplicateDocuments($request, $project, $role, $role2);
            $this->duplicateCalendars($request, $project, $role, $role2);
            // $this->duplicateProspects($request, $project, $role, $role2);
        }

        return [
        	'message' => "Le profil de l'utilisateur a été bien copié."
    	];
    }
}
