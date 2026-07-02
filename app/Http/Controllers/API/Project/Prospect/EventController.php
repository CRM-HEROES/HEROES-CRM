<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Filters\EventRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(EventRequestFilters $filters, Project $project, Prospect $prospect)
    {
        return $prospect
            ->events()
            ->with('calendar:id,name,color, bgcolor')
            ->with('confirmedBy:id,name')
            ->with('creator:id,name')
            ->with('doneBy:id,name')
            ->with('user:id,name')
            ->when(!auth()->user()->can('prospectEventView', $project), function($query) {
                $query->whereIn('events.creator_id', array_merge(
                    [auth()->id()], 
                    auth()->user()
                        ->assignableUsers()
                        ->get(['id'])
                        ->pluck('id')
                        ->toArray()
                ));
            })
            ->filter($filters)
            ->orderBy('started_at', 'desc')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Prospect $prospect)
    {
        // Calendar associated to the current project
        abort_unless($project->calendars()->find($request->input('calendar_id', 0)), 404, 'Unknown event calendar');
        // Affected user associated to the current project
        abort_unless($project->users()->find($request->input('user_id', 0)), 404, 'Unknown event user');
        // Vehicle associated to the current project
        abort_unless(!$request->input('vehicle_id', null) || $project->vehicles()->find($request->input('vehicle_id')), 404, 'Unknown event vehicle');
        // Order associated to the given prospect
        abort_unless(
            !$request->has('order_id') || 
            !$request->input('order_id') || 
            $prospect->orders()->find($request->input('order_id')), 
            404, 
            'Unknown event order'
        );

        $event = $prospect
            ->events()
            ->create(array_merge($request->only(
                'calendar_id',
                'user_id',
                'vehicle_id',
                'prospect_id',
                'order_id',
                'confirmed',
                'done',
                'name',
                'description',
                'started_at',
                'ended_at', 
                'location',
                'drop_off_location',
                'send_email_to_prospect',
                'meta',
            ), [
                'creator_id' => auth()->id(),
            ]));
            
        // Attach users to the event
        if ($request->has('users')) {
            $this->attachUsers($project, $event, $request->input('users'));
        }

        $event->load('calendar:id,name,color,bgcolor');
        $event->load('confirmedBy:id,name');
        $event->load('creator:id,name');
        $event->load('doneBy:id,name');
        $event->load('prospect:id,first_name,last_name');
        $event->load('order');
        $event->load('user:id,name');
        $event->load('users:id,name');
        $event->load('vehicle:id,name');

        return $event;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Prospect $prospect, Event $event)
    {
        // Event associated to the prospect
        abort_unless($prospect->id == $event->prospect_id, 404, 'Unknown event');
        // Authenticated user has access to view the event
        abort_unless(
            // Project or Super admin
            auth()->user()->can('', $project) || 
            // Affected user
            $event->user_id == auth()->id() || 
            // Creator user
            $event->creator_id == auth()->id() || 
            // Associated users
            $event->users()->where('id', auth()->id())->first(), 
            404
        );

        $event->load('calendar:id,name,color,bgcolor');
        $event->load('confirmedBy:id,name');
        $event->load('creator:id,name');
        $event->load('doneBy:id,name');
        $event->load('prospect:id,first_name,last_name');
        $event->load('order');
        $event->load('user:id,name');
        $event->load('users:id,name');
        $event->load('vehicle:id,name');

        return $event;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Prospect $prospect, Event $event)
    {
        // Event associated to the prospect
        abort_unless($prospect->id == $event->prospect_id, 404);
        // Calendar associated to the current project
        abort_unless(!$request->has('calendar_id') || $project->calendars()->find($request->input('calendar_id', 0)), 404, 'Unknown event calendar');
        // Affected user associated to the current project
        abort_unless(!$request->has('user_id') || $project->users()->find($request->input('user_id', 0)), 404, 'Unknown event user');
        // Vehicle associated to the current project
        abort_unless(!$request->input('vehicle_id', null) || $project->vehicles()->find($request->input('vehicle_id')), 404, 'Unknown event vehicle');
        // Order associated to the given prospect
        abort_unless(
            !isset($prospect) || !$prospect || 
            !$request->has('order_id') || 
            !$request->input('order_id') || 
            $prospect->orders()->find($request->input('order_id')), 
            404, 
            'Unknown event order'
        );
        // Authenticated user can edit the event
        abort_unless(
            // Project or Super admin
            auth()->user()->can('', $project) || 
            // Affected user
            $event->user_id == auth()->id() || 
            // Creator user
            $event->creator_id == auth()->id(), 
            404
        );

        $event->update($request->only(
            'calendar_id',
            'user_id',
            'vehicle_id',
            'order_id',
            'confirmed',
            'done',
            'name',
            'description',
            'started_at',
            'ended_at',
            'location',
            'drop_off_location',
            'meta',
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Event $event)
    {
        // Event associated to the prospect
        abort_unless($prospect->id == $event->prospect_id, 404); 
        // Authenticated user can edit the event
        abort_unless(
            // Project or Super admin
            auth()->user()->can('', $project) || 
            // Affected user
            $event->user_id == auth()->id() || 
            // Creator user
            $event->creator_id == auth()->id(), 
            404
        );

        $event->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Attach users as attendee
     * to the event
     */
    protected function attachUsers(Project $project, Event $event, $users)
    {
        $users = $project
            ->users()
            ->whereIn('id', $users)
            ->get()
            ->pluck('id');

        $event
            ->users()
            ->syncWithoutDetaching($users);

        // Attach users to the event prospect
        if ($event->prospect) {
            $event->prospect->users()->syncWithoutDetaching($users);
        }
        // Attach users to the event calendar
        $event->calendar->users()->syncWithoutDetaching($users);
    }
}
