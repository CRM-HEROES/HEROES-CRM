<?php

namespace App\Http\Controllers\API\Project\Import;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\Label;
use App\Models\Project;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Import $import)
    {
        return $project
            ->labels()
            ->whereIn('id', $import->labels ?: [])
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Import $import, Label $label)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_unless($project->id == $label->category->project_id, 404);
        abort_if($import->is_processing, 404);

        $import->update([
            'labels' => array_unique(array_values(array_merge($import->labels ?: [], [$label->id])))
        ]);

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Import $import, Label $label)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_unless($project->id == $label->category->project_id, 404);
        abort_if($import->is_processing, 404);

        $import->update([
            'labels' => array_values(array_filter($import->labels ?: [], function($labelId) use($label) {
                return $labelId != $label->id;
            }))
        ]);

        return ['message' => trans('common.success.deleted_resource')];
    }
}
