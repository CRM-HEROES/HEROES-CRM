<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\AvailableEvent;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Http\Request;

class AvailableEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        return $prospect
            ->availableEvents;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Prospect $prospect)
    {
        return $prospect
            ->availableEvents()
            ->create(array_merge($request->only(
                'started_at',
                'ended_at'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Prospect $prospect, AvailableEvent $availableEvent)
    {
        abort_unless($prospect->id == $availableEvent->prospect_id, 404);

        return $availableEvent;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Prospect $prospect, AvailableEvent $availableEvent)
    {
        abort_unless($prospect->id == $availableEvent->prospect_id, 404);

        $availableEvent->update(array_merge($request->only(
            'started_at',
            'ended_at'
        ), [
            'creator_id' => auth()->id(),
        ]));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, AvailableEvent $availableEvent)
    {
        abort_unless($prospect->id == $availableEvent->prospect_id, 404);

        $availableEvent->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
