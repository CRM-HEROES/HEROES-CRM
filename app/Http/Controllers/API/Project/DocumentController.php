<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\DocumentRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Project;
use App\Utils\Document\DocumentFilePageThumbnail;
use App\Utils\Document\DocumentThumbnail;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, DocumentRequestFilters $documentRequestFilters)
    {
        return $project
            ->documents()
            ->filter($documentRequestFilters)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(!$request->input('folder_id', null) || $project->folders()->find($request->input('folder_id')), 404, 'Unknown folder');
        abort_unless(auth()->user()->can('projectDocumentAdd', $project), 404);
        $this->validate($request, [
            'name' => 'required'
        ]);

        return $project
            ->documents()
            ->create(array_merge($request->only(
                'name',
                'description',
                'for',
                'folder_id'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);

        $document->load('files');
        $document->load('fonts');
        $document->load('fields');
        $document->load(['pages' => function($query) {
            $query->orderBy('page');
        }]);

        return $document;
    }

    /**
     * Get PDF file thumbnail
     */
    public function thumbnail(Project $project, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);

        $thumbnail = (new DocumentThumbnail($document))->generate();

        if (!$thumbnail) {
            return response('Cannot generate thumbnail.', 400);
        }
        
        // Show thumbnail
        return response($thumbnail)->withHeaders([
            'Content-Type' => "image/jpeg",
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Document $document)
    {
        abort_unless(!$request->input('folder_id', null) || $project->folders()->find($request->input('folder_id')), 404, 'Unknown folder');
        abort_unless(auth()->user()->can('projectDocumentUpdate', $project), 404);
        abort_unless($project->id == $document->project_id, 404);

        $document->update($request->only(
            'name',
            'description',
            'for',
            'order',
            'folder_id'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Document $document)
    {
        abort_unless(auth()->user()->can('projectDocumentDelete', $project), 404);
        abort_unless($project->id == $document->project_id, 404);

        $document->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
