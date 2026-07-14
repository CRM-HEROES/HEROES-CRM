<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\EventRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, EventRequestFilters $filters, Project $project)
    {
        return $this->getEvents(
            $request, 
            $filters, 
            $project
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Calendar associated to the current project
        abort_unless($project->calendars()->find($request->input('calendar_id', 0)), 404, 'Unknown event calendar');
        // Affected user associated to the current project
        abort_unless($project->users()->find($request->input('user_id', 0)), 404, 'Unknown event user');
        // Prospect associated to the current project
        abort_unless(!$request->has('prospect_id') || ($prospect = $project->prospects()->find($request->input('prospect_id'))), 404, 'Unknown event prospect');
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

        // Create new event
        $event = Event::
            create(array_merge($request->only(
                'calendar_id',
                'prospect_id',
                'order_id',
                'user_id',
                'vehicle_id',
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
    public function show(Project $project, Event $event)
    {
        // Calendar associated to the current project
        abort_unless($project->id == $event->calendar->project_id, 404, 'Unknown event calendar');
        // Authenticated user has access to view the event
        abort_unless(
            // Project or Super admin
            auth()->user()->can('', $project) ||
            // Affected user (event or its prospect)
            $event->isAffectedTo(auth()->user()),
            404
        );

        $event->load('calendar:id,name,color,bgcolor,type');
        $event->load('confirmedBy:id,name');
        $event->load('creator:id,name');
        $event->load('doneBy:id,name');
        $event->load('prospect:id,first_name,last_name,mobile_phone_number,phone_number');
        $event->load('user:id,name');
        $event->load('users:id,name');
        $event->load('order');
        $event->load('googleAccounts');
        $event->load('vehicle:id,name');

        // Whether the current user can edit/delete this event
        $event->setAttribute(
            'editable',
            auth()->user()->can('', $project) || $event->isAffectedTo(auth()->user())
        );

        return $event;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Event $event)
    {
        // Calendar associated to the current project
        abort_unless(!$request->has('calendar_id') || $project->calendars()->find($request->input('calendar_id', 0)), 404, 'Unknown event calendar');
        // Affected user associated to the current project
        abort_unless(!$request->has('user_id') || $project->users()->find($request->input('user_id', 0)), 404, 'Unknown event user');
        // Vehicle associated to the current project
        abort_unless(!$request->input('vehicle_id', null) || $project->vehicles()->find($request->input('vehicle_id')), 404, 'Unknown event vehicle');
        // Prospect associated to the current project
        abort_unless(!$request->has('prospect_id') || ($prospect = $project->prospects()->find($request->input('prospect_id', 0))), 404, 'Unknown event prospect');
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
            // Affected user (event or its prospect)
            $event->isAffectedTo(auth()->user()),
            404
        );

        $event->update($request->only(
            'calendar_id',
            'user_id',
            'vehicle_id',
            'confirmed',
            'done',
            'prospect_id',
            'order_id',
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
    public function destroy(Project $project, Event $event)
    {
        // Calendar associated to the current project
        abort_unless($project->id == $event->calendar->project_id, 404, 'Unknown event calendar');
        // Authenticated user can edit the event
        abort_unless(
            // Project or Super admin
            auth()->user()->can('', $project) ||
            // Affected user (event or its prospect)
            $event->isAffectedTo(auth()->user()),
            404
        );

        $event->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Get fields
     */
    protected function getFields(Request $request, Project $project)
    {
        // From params
        if ($request->has('fields')) {
            return explode(',', $request->input('fields'));
        }

        // Default
        return ['calendar'/*, 'confirmedBy', 'doneBy'*/, 'prospect', 'user', 'users'];
    }

    /**
     * Get events
     */
    protected function getEvents(Request $request, EventRequestFilters $filters, Project $project)
    {
        // Count
        $count = min($request->input('count', 500), 500);

        // Sort By
        $sortBy = $request->input('sort_by', "id");
        if (!in_array($sortBy, ['name', 'location', 'started_at', 'ended_at'])) {
            $sortBy = "id";
        }

        // Sort Order
        $sortOrder = $request->input('sort_order', "") == "desc" ? "desc" : "asc";

        // Fields
        $fields = $this->getFields(
            $request, 
            $project
        );

        // Categories in which we select labels associated to prospects
        $categories = array_map(function($field) {
            return Str::replace('category->', '', $field);
        }, array_filter($fields, function($field) {
            return Str::startsWith($field, 'category->');
        }));

        // Result
        $events = Event
            ::whereHas('calendar', function($query) use($project) {
                $query->where('project_id', $project->id);
            })
            ->when(in_array('calendar', $fields), function($query) {
                $query->with('calendar:id,name,color,bgcolor,type');
            })
            ->when(in_array('confirmedBy', $fields), function($query) {
                $query->with('confirmedBy:id,name');
            })
            ->when(in_array('doneBy', $fields), function($query) {
                $query->with('doneBy:id,name');
            })
            ->when(in_array('prospect', $fields), function($query) {
                $query->with('prospect:id,first_name,last_name,phone_number,mobile_phone_number,latitude,longitude,street,street_bis,postal_code,city,state,county,country');
            })
            // Labels
            ->when(!empty($categories), function($query) use($categories) {
                $query->with(['prospect.labels' => function($query) use($categories) {
                    $query->whereIn('category_id', $categories)->select('id', 'name', 'category_id', 'color', 'bgcolor');
                }]);
            })
            ->when(in_array('user', $fields), function($query) {
                $query->with('user:id,name');
            })
            ->when(in_array('users', $fields), function($query) {
                $query->with('users:id,name');
            })
            ->when(in_array('vehicle', $fields), function($query) {
                $query->with('vehicle:id,name');
            })
            ->filter($filters)
            ->orderBy($sortBy, $sortOrder)
            ->get();
            //->paginate($count);

        if (!auth()->user()->can('', $project)) {
            $events = $events->map(function ($event) {
                if (
                    $event->user_id == auth()->id() || 
                    $event->creator_id == auth()->id() || 
                    ($event->users && $event->users->where(function($user) {
                        return $user->id == auth()->id();
                    })->first())
                ) {
                    return $event;
                }

                if (!$event->prospect) {
                    $event->name = $event->calendar->name;
                    
                    $event->description = 
                    $event->location = 
                    $event->latitude = 
                    $event->longitude = 
                    $event->email_to_prospect = 
                    $event->prospect_id = 
                    $event->prospect = 
                    $event->order_id = 
                    $event->order = 
                    $event->done_by = 
                    $event->confirmed_by = 
                    $event->doneBy = 
                    $event->confirmedBy = 
                    null;
                }

                return $event;
            });
        }

        return $events;
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

        if ($event->prospect) {
            $event->prospect->users()->syncWithoutDetaching($users);
        }
    }
}
