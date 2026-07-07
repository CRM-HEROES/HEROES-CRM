<?php

namespace App\Http\Controllers\API;

use App\Filters\EventRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, EventRequestFilters $filters)
    {
        return $this->getEvents(
            $request, 
            $filters
        );
    }

    /**
     * Get fields
     */
    protected function getFields(Request $request)
    {
        // From params
        if ($request->has('fields')) {
            return explode(',', $request->input('fields'));
        }

        // Default
        return ['calendar'/*, 'confirmedBy', 'doneBy'*/, 'prospect', 'project', 'user', 'users'];
    }

    /**
     * Get events
     */
    protected function getEvents(Request $request, EventRequestFilters $filters)
    {
        // Count
        $count = min($request->input('count', 30), 500);

        // Sort By
        $sortBy = $request->input('sort_by', "id");
        if (!in_array($sortBy, ['name', 'location', 'started_at', 'ended_at'])) {
            $sortBy = "id";
        }

        // Sort Order
        $sortOrder = $request->input('sort_order', "") == "desc" ? "desc" : "asc";

        // Fields
        $fields = $this->getFields(
            $request
        );

        // Result
        $events = auth()->user()
            ->affectedEvents()
            ->when(in_array('calendar', $fields), function($query) {
                $query
                    ->with('calendar:id,name,color,bgcolor,type')
                    ->with('calendar.project:id,name,slug');
            })
            ->when(in_array('confirmedBy', $fields), function($query) {
                $query->with('confirmedBy:id,name');
            })
            ->when(in_array('doneBy', $fields), function($query) {
                $query->with('doneBy:id,name');
            })
            ->when(in_array('prospect', $fields), function($query) {
                $query->with('prospect:id,first_name,last_name,phone_number,mobile_phone_number,latitude,longitude,street,street_bis,postal_code,city,state,county,country,project_id')
                    ->with('prospect.project:id,name,slug');
            })
            ->when(in_array('user', $fields), function($query) {
                $query->with('user:id,name');
            })
            ->when(in_array('users', $fields), function($query) {
                $query->with('users:id,name');
            })
            ->filter($filters)
            ->orderBy($sortBy, $sortOrder)
            ->get();
            //->paginate($count);

        return $events;
    }
}
