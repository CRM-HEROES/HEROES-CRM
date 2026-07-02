<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Jobs\VehiclePosition;
use App\Models\Project;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless($project->users()->find($user->id), 404);

        return $user->vehicles()->where('project_id', $project->id)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, User $user)
    {
        abort_unless($project->users()->find($user->id), 404);

        $this->validate($request, [
            'name', 
            'tracker', 
            'data', 
        ]);

        return $user->vehicles()->create(array_merge($request->only('name', 'tracker', 'data'), [
            'project_id' => $project->id,
            'creator_id' => auth()->id(),
        ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, User $user, Vehicle $vehicle)
    {
        abort_unless($project->users()->find($user->id), 404);
        abort_unless($vehicle->user_id == $user->id, 404);
        abort_unless($vehicle->project_id == $project->id, 404);

        try {
            dispatch((new VehiclePosition($vehicle))->onConnection('sync'));
        } catch (\Exception $e) {}

        $vehicle->refresh();

        return $vehicle;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, User $user, Vehicle $vehicle)
    {
        abort_unless($project->users()->find($user->id), 404);
        abort_unless($vehicle->user_id == $user->id, 404);

        $vehicle->update($request->only(
            'name', 
            'tracker', 
            'data', 
            'latitude',
            'longitude',
            'direction'
        ));

        if ($request->has('latitude') || $request->has('longitude')) {
            $vehicle->update(['position_at' => \Carbon\Carbon::now()]);
        }

        if ($request->has('direction')) {
            $vehicle->update(['direction_at' => \Carbon\Carbon::now()]);
        }

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, Vehicle $vehicle)
    {
        abort_unless($project->users()->find($user->id), 404);
        abort_unless($vehicle->user_id == $user->id, 404);

        $vehicle->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

}
