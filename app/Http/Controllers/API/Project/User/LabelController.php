<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userCategory', $project), 404);

        return $user
            ->labels()
            ->select('id', 'name', 'color', 'bgcolor')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, Label $label)
    {
        abort_unless(auth()->user()->can('userCategory', $project), 404);
        abort_unless($project->id == $label->category->project_id, 404);

        return $label
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Label $label)
    {
        abort_unless(auth()->user()->can('userCategory', $project), 404);
        abort_unless($project->id == $label->category->project_id, 404);
        abort_unless($label->category->for == "user", 404);

        $user
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
    public function destroy(Project $project, User $user, Label $label)
    {
        abort_unless(auth()->user()->can('userCategory', $project), 404);
        abort_unless($project->id == $label->category->project_id, 404);
        abort_unless($label->category->for == "user", 404);

        $user->labels()->detach($label->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userCategory', $project), 404);
        
        $this->validate($request, [
            'labels' => 'required',
            'users' => 'required',
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
                $this->bulkActionAttach($users, $labels);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $labels);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach labels
     */
    protected function bulkActionAttach(&$users, &$labels) {
        // Remove previous data
        $this->bulkActionDetach($users, $labels);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($label) use($users, $now) {
            return array_map(function($user) use($label, $now) {
                return [
                    'label_id' => $label,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $labels);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_label')->insert($data);
    }

    /**
     * Bulk detach labels
     */
    protected function bulkActionDetach(&$users, &$labels) {
        DB::table('user_label')
            ->whereIn('label_id', $labels)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
