<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\CalendarRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Calendar;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, CalendarRequestFilters $calendarRequestFilters)
    {
        return $project
            ->calendars()
            ->orderBy('relevance', 'desc')
            ->orderBy('name')
            ->filter($calendarRequestFilters)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectCalendarAdd', $project), 404);

        $this->validate($request, [
            'name' => 'required'
        ]);

        $calendar = $project
            ->calendars()
            ->create(array_merge($request->only(
                'name',
                'type',
                'color',
                'bgcolor'
            ), [
                'creator_id' => auth()->id(),
            ]));

        return $calendar;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Calendar $calendar)
    {
        // Calendar associated to the current project
        abort_unless($project->id == $calendar->project_id, 404);

        return $calendar;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Calendar $calendar)
    {
        // Calendar associated to the current project
        abort_unless($project->id == $calendar->project_id, 404);
        abort_unless(auth()->user()->can('projectCalendarUpdate', $project), 404);

        $calendar->update($request->only(
            'name',
            'type',
            'color',
            'bgcolor',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project, Calendar $calendar)
    {
        // Calendar associated to the current project
        abort_unless($project->id == $calendar->project_id, 404);
        abort_unless(auth()->user()->can('projectCalendarDelete', $project), 404);

        if ($request->has('combine')) {
            if ($with = $project->calendars()->find($request->input('combine'))) {
                abort_if($with->id == $calendar->id, 400);
                $this->combine($calendar, $with);
            }
        }

        $calendar->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
    
    /**
     * Combine with another label
     */
    protected function combine(Calendar $calendar, Calendar $with)
    {
        DB::table("events")
            ->where('calendar_id', $calendar->id)
            ->update([
                'calendar_id' => $with->id
            ]);
    }
}
