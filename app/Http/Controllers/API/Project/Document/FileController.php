<?php

namespace App\Http\Controllers\API\Project\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\Document\FileRequest;
use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\Project;
use App\Utils\Document\DocumentFilePageThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);

        return $document->files;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FileRequest $request, Project $project, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);

        return $document
            ->files()
            ->create(array_merge(
                $this->storeFile($request, $project), 
                [
                    'creator_id' => auth()->id()
                ]
            ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FileRequest $request, Project $project, Document $document, DocumentFile $file)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $file->document_id, 404);

        $file->update($this->storeFile($request, $project));
        
        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Document $document, DocumentFile $file)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $file->document_id, 404);

        $disk = Storage::disk('documents');
        
        abort_unless($disk->exists($file->path), 404);

        return response($disk->get($file->path))->withHeaders([
            'Content-Type', $disk->mimeType($file->path),
            'Content-Disposition', 'attachment; filename="' . $file->name . '"',
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
            'Last-Modified' => gmdate('D, d M Y H:i:s', filemtime($disk->path($file->path))) . ' GMT',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Document $document, DocumentFile $file)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $file->document_id, 404);

        $file->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Get PDF file thumbnail
     */
    public function thumbnail(Project $project, Document $document, DocumentFile $file, $page)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $file->document_id, 404);

        $thumbnail = (new DocumentFilePageThumbnail($file, $page))->generate();

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
     * Store file file
     */
    protected function storeFile(Request $request, Project $project)
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $name = Str::random(30) . '.' . pathinfo($originalName)['extension'];

        return [
            'path' => $file->storeAs($project->slug, $name, 'documents'),
            'name' => $originalName,
            'size' => $file->getSize()
        ];
    }
}
