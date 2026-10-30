<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Events\ProspectLabelAttached;
use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\Project;
use App\Models\Prospect;
use App\Services\ProspectLabelHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        return $prospect
            ->labels()
            ->get(['labels.*', 'prospect_label.created_at', 'prospect_label.creator_id']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Prospect $prospect, Label $label)
    {
        // Label category associated to the current project
        abort_unless($project->id == $label->category->project_id, 404);
        // Label category for "prospect"
        abort_unless($label->category->for == "prospect", 404);

        DB::transaction(function () use ($prospect, $label) {
            $history = app(ProspectLabelHistory::class);
            $history->preserve($prospect, $label);
            $oldValue = $history->value($prospect, $label);
            $changedAt = now()->toDateTimeString();

            if (!$prospect->labels()->whereKey($label->id)->exists()) {
                $prospect->labels()->attach($label->id, [
                    'creator_id' => auth()->id(),
                    'created_at' => $changedAt,
                    'updated_at' => $changedAt,
                ]);
            }

            ProspectLabelAttached::dispatch($prospect, $label, $oldValue, $changedAt);
        });

        return [
            'message' => trans('common.success.attached_resource'),
            'label' => $prospect->labels()->whereKey($label->id)
                ->first(['labels.*', 'prospect_label.created_at', 'prospect_label.creator_id']),
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Label $label)
    {
        // Label category associated to the current project
        abort_unless($project->id == $label->category->project_id, 404);
        // Label category for "prospect"
        abort_unless($label->category->for == "prospect", 404);

        DB::transaction(function () use ($prospect, $label) {
            if (!$prospect->labels()->whereKey($label->id)->exists()) {
                return;
            }

            $history = app(ProspectLabelHistory::class);
            $history->preserve($prospect, $label);
            $oldValue = $history->value($prospect, $label);
            $prospect->labels()->detach($label->id);
            $history->record($prospect, $label, $oldValue, $history->value($prospect, $label));
        });

        return ['message' => trans('common.success.detached_resource')];
    }
    
    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'labels' => 'required',
            'prospects' => 'required',
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
            case "attach":
                $this->bulkActionAttach($prospects, $labels);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($prospects, $labels);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach labels
     */
    protected function bulkActionAttach(&$prospects, &$labels) {
        // Remove previous data
        $this->bulkActionDetach($prospects, $labels);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($label) use($prospects, $now) {
            return array_map(function($prospect) use($label, $now) {
                return [
                    'prospect_id' => $prospect,
                    'label_id' => $label,
                    'creator_id' => auth()->id(),
                    'deleted_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $prospects);
        }, $labels);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('prospect_label')->insert($data);
    }

    /**
     * Bulk detach labels
     */
    protected function bulkActionDetach(&$prospects, &$labels) {
        DB::table('prospect_label')
            ->whereIn('label_id', $labels)
            ->whereIn('prospect_id', $prospects)
            ->delete();

        /*DB::table('prospect_label')
            ->whereIn('prospect_id', $prospects)
            ->whereIn('label_id', $labels)
            ->update(['deleted_at' => DB::raw('NOW()')]);*/
    }
}
