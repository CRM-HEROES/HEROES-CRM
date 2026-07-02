<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        abort_unless(auth()->user(), 401);

        $userPermissions = auth()->user()->permissions()->pluck('name')->toArray();

        foreach (auth()->user()->roles()->where('roles.project_id', $project->id)->get() as $role) {
            $userPermissions = array_merge($userPermissions, $role->permissions()->pluck('name')->toArray());
        }

        return $userPermissions;
    }
}
