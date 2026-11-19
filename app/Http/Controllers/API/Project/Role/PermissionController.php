<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Policies\ProjectPolicy;

/**
 * The permissions of a role of the project.
 *
 * Who may do what, as the role screen of the interface does: seeing and changing the permissions of a role needs the
 * right to update roles ("Modifier un rôle"), and only for a role of this project. Nobody can give a role a permission
 * he does not hold himself (otherwise a user allowed to edit roles could give himself every right), except the
 * super administrators and the creator of the project.
 */
class PermissionController extends Controller
{
    /**
     * A permission is a path of lowercase keys, like "all.project.role.update" (see utils/role-permissions.js).
     */
    public const NAME_PATTERN = '/\Aall(\.[a-z0-9_-]+)*\z/';

    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        $this->authorizeRole($project, $role);

        return $role->getPermissionNames();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, $permission)
    {
        $this->authorizeRole($project, $role);
        abort_unless(preg_match(self::NAME_PATTERN, (string) $permission) === 1, 422, 'Unknown permission.');
        abort_unless($this->mayGrant($project, $permission), 403, 'You cannot give a permission you do not have.');

        if (!Permission::where('name', $permission)->first()) {
        	Permission::create([
				'name' => $permission,
				'guard_name' => "web"
    		]);

    		app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $role->givePermissionTo($permission);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Role $role, $permission)
    {
        $this->authorizeRole($project, $role);
        abort_unless(preg_match(self::NAME_PATTERN, (string) $permission) === 1, 422, 'Unknown permission.');

        // A permission that does not exist is not attached: nothing to revoke (revokePermissionTo would fail)
        if (Permission::where('name', $permission)->exists()) {
            $role->revokePermissionTo($permission);
        }

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * The role is one of this project, and the user may update roles.
     */
    protected function authorizeRole(Project $project, Role $role): void
    {
        abort_unless($project->id == $role->project_id, 404);
        abort_unless(auth()->user()->can('projectRoleUpdate', $project), 404);
    }

    /**
     * May the user give this permission? Administrators of the project may give any; the others only what they hold.
     */
    protected function mayGrant(Project $project, string $permission): bool
    {
        $user = auth()->user();

        if ($user->is_super_admin || $project->creator_id == $user->id || $user->can('', $project)) {
            return true;
        }

        return app(ProjectPolicy::class)->can($user, $permission);
    }
}
