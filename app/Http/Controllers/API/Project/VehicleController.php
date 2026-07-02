<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\VehiclePosition;
use App\Models\Project;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project->vehicles()->get();
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(!$request->input('user_id', null) || $project->users()->find($request->input('user_id')), 404);

        $this->validate($request, [
            'name',
        ]);

        return $project->vehicles()->create(array_merge($request->only(
            'name', 
            'tracker', 
            'data', 
            'user_id'
        ), [
            'project_id' => $project->id,
            'creator_id' => auth()->id(),
        ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Vehicle $vehicle)
    {
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
    public function update(Request $request, Project $project, Vehicle $vehicle)
    {
        abort_unless(!$request->input('user_id', null) || $project->users()->find($request->input('user_id')), 404);

        $vehicle->update($request->only(
            'name', 
            'tracker', 
            'data', 
            'latitude',
            'longitude',
            'direction',
            'user_id'
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
    public function destroy(Project $project, Vehicle $vehicle)
    {
        $vehicle->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Display positions.
     */
    public function positions(Project $project, Vehicle $vehicle)
    {
        abort_unless($vehicle->project_id == $project->id, 404);

        return VehiclePosition
            ::where('vehicle_id', $vehicle->id)
            ->where('user_id', $vehicle->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(24)
            ->get(['id', 'latitude', 'longitude', 'created_at']);
    }
}
