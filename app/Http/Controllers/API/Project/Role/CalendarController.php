<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->calendars()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated roles.
     */
    public function roles(Project $project, Calendar $calendar)
    {
        abort_unless($project->id == $calendar->project_id, 404);

        return $calendar
            ->roles()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, Calendar $calendar)
    {
        abort_unless($project->id == $calendar->project_id, 404);

        $role
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
    public function destroy(Project $project, Role $role, Calendar $calendar)
    {
        abort_unless($project->id == $calendar->project_id, 404);

        $role->calendars()->detach($calendar->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'calendars' => 'required',
            'roles' => 'required',
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
            
        // Only roles
        // associated to the current project
        $roles = $project
            ->roles()
            ->whereIn('id', $request->input('roles'))
            ->get(['id'])
            ->map(function($role) {
                return $role->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($roles, $calendars);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($roles, $calendars);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach calendars
     */
    protected function bulkActionAttach(&$roles, &$calendars) {
        // Remove previous data
        $this->bulkActionDetach($roles, $calendars);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($calendar) use($roles, $now) {
            return array_map(function($role) use($calendar, $now) {
                return [
                    'calendar_id' => $calendar,
                    'role_id' => $role,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $calendars);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_calendar')->insert($data);
    }

    /**
     * Bulk detach calendars
     */
    protected function bulkActionDetach(&$roles, &$calendars) {
        DB::table('role_calendar')
            ->whereIn('calendar_id', $calendars)
            ->whereIn('role_id', $roles)
            ->delete();
    }
}
