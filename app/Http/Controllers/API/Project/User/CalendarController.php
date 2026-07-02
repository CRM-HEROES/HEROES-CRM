<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userCalendar', $project), 404);
        
        return $user
            ->calendars()
            ->select('id', 'name', 'color', 'bgcolor')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, Calendar $calendar)
    {
        abort_unless(auth()->user()->can('userCalendar', $project), 404);
        abort_unless($project->id == $calendar->project_id, 404);

        return $calendar
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Calendar $calendar)
    {
        abort_unless(auth()->user()->can('userCalendar', $project), 404);
        abort_unless($project->id == $calendar->project_id, 404);

        $user
            ->calendars()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$calendar->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, Calendar $calendar)
    {
        abort_unless(auth()->user()->can('userCalendar', $project), 404);
        abort_unless($project->id == $calendar->project_id, 404);

        $user->calendars()->detach($calendar->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userCalendar', $project), 404);

        $this->validate($request, [
            'calendars' => 'required',
            'users' => 'required',
            'action' => 'required',
        ]);

        // Only calendars
        // associated to the current project
        $calendars = $project
            ->calendars()
            ->whereIn('id', $request->input('calendars'))
            ->get(['id'])
            ->map(function($calendar) {
                return $calendar->id;
            })
            ->toArray();
            
        // Only users
        // associated to the current project
        $users = $project
            ->users()
            ->whereIn('id', $request->input('users'))
            ->get(['id'])
            ->map(function($user) {
                return $user->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($users, $calendars);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $calendars);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach calendars
     */
    protected function bulkActionAttach(&$users, &$calendars) {
        // Remove previous data
        $this->bulkActionDetach($users, $calendars);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($calendar) use($users, $now) {
            return array_map(function($user) use($calendar, $now) {
                return [
                    'calendar_id' => $calendar,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $calendars);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_calendar')->insert($data);
    }

    /**
     * Bulk detach calendars
     */
    protected function bulkActionDetach(&$users, &$calendars) {
        DB::table('user_calendar')
            ->whereIn('calendar_id', $calendars)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
