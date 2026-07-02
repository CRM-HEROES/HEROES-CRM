<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->labels()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated roles.
     */
    public function roles(Project $project, Label $label)
    {
        abort_unless($project->id == $label->project_id, 404);

        return $label
            ->roles()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, Label $label)
    {
        abort_unless($project->id == $label->category->project_id, 404);
        abort_unless($label->category->for == "role", 404);

        $role
            ->labels()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$label->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Role $role, Label $label)
    {
        abort_unless($project->id == $label->category->project_id, 404);
        abort_unless($label->category->for == "role", 404);

        $role->labels()->detach($label->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'labels' => 'required',
            'roles' => 'required',
            'action' => 'required',
        ]);

        // Only labels
        // associated to the current project
        $labels = Label::
            whereHas('category', function($query) use($project) {
                $query->where('project_id', $project->id);
            })
            ->whereIn('id', $request->input('labels'))
            ->get(['id'])
            ->map(function($label) {
                return $label->id;
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
                $this->bulkActionAttach($roles, $labels);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($roles, $labels);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach labels
     */
    protected function bulkActionAttach(&$roles, &$labels) {
        // Remove previous data
        $this->bulkActionDetach($roles, $labels);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($label) use($roles, $now) {
            return array_map(function($role) use($label, $now) {
                return [
                    'label_id' => $label,
                    'role_id' => $role,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $labels);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_label')->insert($data);
    }

    /**
     * Bulk detach labels
     */
    protected function bulkActionDetach(&$roles, &$labels) {
        DB::table('role_label')
            ->whereIn('label_id', $labels)
            ->whereIn('role_id', $roles)
            ->delete();
    }
}
