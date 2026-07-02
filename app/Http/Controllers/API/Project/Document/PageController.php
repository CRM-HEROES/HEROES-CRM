<?php

namespace App\Http\Controllers\API\Project\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\Document\PageRequest;
use App\Models\Document;
use App\Models\DocumentPage;
use App\Models\Project;
use App\Utils\Document\DocumentFilePageThumbnail;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);

        return $document->pages()->orderBy('page')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PageRequest $request, Project $project, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($request->input('file_id', null) === null || $document->files()->find($request->input('file_id')), 404);

        return $document
            ->pages()
            ->create(array_merge($request->only(
                'file_id',
                'page',
                'file_page',
                'width',
                'height',
                'orientation'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Document $document, DocumentPage $page)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $page->document_id, 404);

        return $page;
    }

    /**
     * Display page thumbnail
     * Need to install
     * - Imagick
     * - GhostScript
     * To enable to generate thumbail
     */
    public function thumbnail(Project $project, Document $document, DocumentPage $page)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $page->document_id, 404);

        if (!$page->file) {
            return response('Cannot generate thumbnail.', 400);
        }

        $thumbnail = (new DocumentFilePageThumbnail($page->file, $page->file_page, 200))->generate();

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
    public function update(PageRequest $request, Project $project, Document $document, DocumentPage $page)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $page->document_id, 404);
        abort_unless(!$request->has('file_id') || $request->input('file_id') === null || $document->files()->find($request->input('file_id')), 404);

        $page->update($request->only(
            'file_id',
            'page',
            'file_page',
            'width',
            'height',
            'orientation'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Document $document, DocumentPage $page)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $page->document_id, 404);

        $page->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
