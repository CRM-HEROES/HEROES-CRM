<?php

namespace App\Http\Controllers\API\Project\Import;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\User;
use App\Models\Project;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Import $import)
    {
        return $project
            ->users()
            ->whereIn('id', $import->users ?: [])
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Import $import, User $user)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_if($import->is_processing, 404);

        $import->update([
            'users' => array_unique(array_values(array_merge($import->users ?: [], [$user->id])))
        ]);

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Import $import, User $user)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_if($import->is_processing, 404);

        $import->update([
            'users' => array_values(array_filter($import->users ?: [], function($userId) use($user) {
                return $userId != $user->id;
            }))
        ]);

        return ['message' => trans('common.success.deleted_resource')];
    }
}
