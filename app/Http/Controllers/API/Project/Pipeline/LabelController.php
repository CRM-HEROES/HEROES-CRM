<?php

namespace App\Http\Controllers\API\Project\Pipeline;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\Project;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Pipeline $pipeline)
    {
        return $pipeline
            ->labels()
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Pipeline $pipeline, Label $label)
    {
        // Label category associated to the current project
        abort_unless($project->id == $pipeline->project_id, 404);
        abort_unless($project->id == $label->category->project_id, 404);

        $pipeline
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
    public function destroy(Project $project, Pipeline $pipeline, Label $label)
    {
        // Pipeline associated to the current project
        abort_unless($project->id == $pipeline->project_id, 404);
        abort_unless($project->id == $label->category->project_id, 404);

        $pipeline->labels()->detach($label->id);

        return ['message' => trans('common.success.detached_resource')];
    }
    
    /**
     * 
     */
    public function orders(Request $request, Project $project, Pipeline $pipeline) 
    {
        abort_unless($project->id == $pipeline->project_id, 404);

        $this->validate($request, [
            'orders' => 'required',
        ]);

        $labels = $pipeline->labels;

        foreach ($request->input('orders') as $order) {
            $label = $labels->where('id', $order['label'])->first();

            if ($label) {
                $label->pivot->order = $order['order'];
                $label->pivot->update();
            }
        }

        return ['message' => trans('common.success.updated_resource')];
    }
    
    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'labels' => 'required',
            'pipelines' => 'required',
            'action' => 'required',
        ]);

        // Only labels
        // associated to the current project
        $labels = Label::
            whereHas('category', function($query) use($project) {
                $query
                    ->where('project_id', $project->id)
                    ->where('for', "prospect");
            })
            ->whereIn('id', $request->input('labels'))
            ->get(['id'])
            ->map(function($label) {
                return $label->id;
            })
            ->toArray();

        // Only pipelines
        // associated to the current project
        $pipelines = $project
            ->pipelines()
            ->whereIn('id', $request->input('pipelines'))
            ->get(['id'])
            ->map(function($pipeline) {
                return $pipeline->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($pipelines, $labels);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($pipelines, $labels);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach labels
     */
    protected function bulkActionAttach(&$pipelines, &$labels) {
        // Remove previous data
        $this->bulkActionDetach($pipelines, $labels);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($label) use($pipelines, $now) {
            return array_map(function($pipeline, $order) use($label, $now) {
                return [
                    'pipeline_id' => $pipeline,
                    'label_id' => $label,
                    'order' => $order,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $pipelines, array_keys($pipelines));
        }, $labels);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('pipeline_label')->insert($data);
    }

    /**
     * Bulk detach labels
     */
    protected function bulkActionDetach(&$pipelines, &$labels) {
        DB::table('pipeline_label')
            ->whereIn('label_id', $labels)
            ->whereIn('pipeline_id', $pipelines)
            ->delete();

        /*DB::table('pipeline_label')
            ->whereIn('pipeline_id', $pipelines)
            ->whereIn('label_id', $labels)
            ->update(['deleted_at' => DB::raw('NOW()')]);*/
    }
}
