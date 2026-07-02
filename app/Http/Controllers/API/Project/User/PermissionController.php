<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Project;
use App\Models\User;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('', $project) || $user->id == auth()->id(), 404);
        return $user->getPermissionNames();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, $permission)
    {
        abort_unless(auth()->user()->can('', $project), 404);

        if (!Permission::where('name', $permission)->first()) {
        	Permission::create([
				'name' => $permission, 
				'guard_name' => "web"
    		]);

    		app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $user->givePermissionTo($permission);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, $permission)
    {
        abort_unless(auth()->user()->can('', $project), 404);
        
        $user->revokePermissionTo($permission);

        return ['message' => trans('common.success.detached_resource')];
    }
}
