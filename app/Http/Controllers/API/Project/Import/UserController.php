<?php

namespace App\Http\Controllers\API\Project\Import;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

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

        // The "Relations" step fires one PUT per user checkbox ticked, often
        // near-simultaneously. Reading $import->users from the model already
        // loaded for this request and writing it back is a lost-update race:
        // two concurrent requests both read the array before either commits,
        // so only the last write survives — e.g. ticking users 1, 2 and 3
        // could end up saving just [3]. Locking the row for the read+write
        // serializes concurrent requests on the same import.
        DB::transaction(function () use ($import, $user) {
            $current = Import::whereKey($import->id)->lockForUpdate()->value('users') ?: [];

            Import::whereKey($import->id)->update([
                'users' => array_unique(array_values(array_merge($current, [$user->id]))),
            ]);
        });

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Import $import, User $user)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_if($import->is_processing, 404);

        DB::transaction(function () use ($import, $user) {
            $current = Import::whereKey($import->id)->lockForUpdate()->value('users') ?: [];

            Import::whereKey($import->id)->update([
                'users' => array_values(array_filter($current, function ($userId) use ($user) {
                    return $userId != $user->id;
                })),
            ]);
        });

        return ['message' => trans('common.success.deleted_resource')];
    }
}
