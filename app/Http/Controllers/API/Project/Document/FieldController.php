<?php

namespace App\Http\Controllers\API\Project\Document;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentField;
use App\Models\Project;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);

        return $document->fields;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless(!$request->input('page_id', null) || $document->pages()->find($request->input('page_id')), 404);

        return $document
            ->fields()
            ->create(array_merge($request->only(
                'page_id',
                'type',
                'content',
                'style',
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Document $document, DocumentField $field)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $field->document_id, 404);

        return $field;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Document $document, DocumentField $field)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $field->document_id, 404);
        abort_unless(!$request->input('page_id', null) || $document->pages()->find($request->input('page_id')), 404);

        $field->update($request->only(
            'page_id',
            'type',
            'content',
            'style',
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Document $document, DocumentField $field)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $field->document_id, 404);

        $field->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
