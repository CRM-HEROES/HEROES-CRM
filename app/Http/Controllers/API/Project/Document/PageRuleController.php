<?php

namespace App\Http\Controllers\API\Project\Document;

use App\Http\Controllers\Controller;
use App\Models\QuestionnaireOption;
use App\Models\Project;
use App\Models\DocumentPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, DocumentPage $documentPage)
    {
        return $documentPage
            ->rules()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, DocumentPage $documentPage, QuestionnaireOption $option)
    {
        abort_unless($project->id == $option->project_id, 404);

        $documentPage
            ->rules()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$option->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, DocumentPage $documentPage, QuestionnaireOption $option)
    {
        abort_unless($project->id == $option->project_id, 404);

        $documentPage->rules()->detach($option->id);

        return ['message' => trans('common.success.detached_resource')];
    }
}
