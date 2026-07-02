<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Pipeline;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PipelineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project
            ->pipelines()
            ->orderBy('name')
            ->with([
                'labels' => function($query) {
                    $query->orderBy('pipeline_label');
                }
            ])
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectPipelineAdd', $project), 404);
        $this->validate($request, [
            'name' => 'required'
        ]);

        return $project
            ->pipelines()
            ->create(array_merge($request->only(
                'name',
                'description',
                'color',
                'bgcolor'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Pipeline $pipeline)
    {
        // Pipeline associated to the current project
        abort_unless($project->id == $pipeline->project_id, 404);

        return Pipeline
            ::where('id', $pipeline->id)
            ->with([
                'labels' => function($query) {
                    $query->orderBy('pipeline_label.order');
                }
            ])
            ->first();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Pipeline $pipeline)
    {
        // Pipeline associated to the current project
        abort_unless($project->id == $pipeline->project_id, 404);
        abort_unless(auth()->user()->can('projectPipelineUpdate', $project), 404);

        $pipeline->update($request->only(
            'name',
            'description',
            'color',
            'bgcolor',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Pipeline $pipeline)
    {
        // Pipeline associated to the current project
        abort_unless($project->id == $pipeline->project_id, 404);
        abort_unless(auth()->user()->can('projectPipelineDelete', $project), 404);

        $pipeline->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
    
    /**
     * Display a listing of the resource.
     */
    public function count(Project $project, Pipeline $pipeline)
    {
        abort_unless($project->id == $pipeline->project_id, 404);

        $count = DB::table('prospect_label')
            ->join('prospects', 'prospects.id', '=', 'prospect_label.prospect_id')
            ->whereIn("label_id", $pipeline->labels()->get(['id'])->pluck('id')->toArray())
            ->whereNull("prospects.deleted_at")
            ->whereNull("prospects.processed_at")
            ->groupBy("label_id")
            ->select('label_id', DB::raw('count(*) as prospects_count'))
            ->get();

        $result = [];

        foreach ($count as $c) {
            $result[$c->label_id] = $c->prospects_count;
        }

        return $result;
    }
}
