<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        return $prospect
            ->groups()
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Prospect $prospect, Group $group)
    {
        // Group associated to the current project
        abort_unless($project->id == $group->project_id, 404);

        // Attach user to the users group
        $prospect
            ->groups()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$group->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Group $group)
    {
        // Group associated to the current project
        abort_unless($project->id == $group->project_id, 404);

        // Detach user from the users group
        $prospect->groups()->detach($group->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'groups' => 'required',
            'prospects' => 'required',
            'action' => 'required',
        ]);

        // Only groups
        // associated to the current project
        $groups = $project
            ->groups()
            ->whereIn('id', $request->input('groups'))
            ->get(['id'])
            ->map(function($group) {
                return $group->id;
            })
            ->toArray();
            
        // Only prospects
        // associated to the current project
        $prospects = $project
            ->prospects()
            ->whereIn('id', $request->input('prospects'))
            ->whereNull('processed_at')
            ->get(['id'])
            ->map(function($prospect) {
                return $prospect->id;
            })
            ->toArray();

        switch ($request->input('action')) {
            // Attach action
            case "attach":
                $this->bulkActionAttach($prospects, $groups);
                return ['message' => trans('common.success.updated_resource')];            

            // Detach action
            case "detach":
                $this->bulkActionDetach($prospects, $groups);
                return ['message' => trans('common.success.deleted_resource')];

            // Unknown action
            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach groups
     */
    protected function bulkActionAttach(&$prospects, &$groups) {
        // Remove previous data
        $this->bulkActionDetach($prospects, $groups);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($group) use($prospects, $now) {
            return array_map(function($prospect) use($group, $now) {
                return [
                    'group_id' => $group,
                    'prospect_id' => $prospect,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $prospects);
        }, $groups);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('prospect_group')->insert($data);
    }

    /**
     * Bulk detach groups
     */
    protected function bulkActionDetach(&$prospects, &$groups) {
        DB::table('prospect_group')
            ->whereIn('group_id', $groups)
            ->whereIn('prospect_id', $prospects)
            ->delete();
    }
}
