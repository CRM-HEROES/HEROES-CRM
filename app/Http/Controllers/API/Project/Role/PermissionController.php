<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role->getPermissionNames();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, $permission)
    {
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
        $role->revokePermissionTo($permission);

        return ['message' => trans('common.success.detached_resource')];
    }
}
