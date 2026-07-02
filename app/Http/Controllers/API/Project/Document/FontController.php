<?php

namespace App\Http\Controllers\API\Project\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\Document\FontRequest;
use App\Models\Document;
use App\Models\DocumentFont;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FontController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);

        return $document->fonts;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FontRequest $request, Project $project, Document $document)
    {
        abort_unless($project->id == $document->project_id, 404);

        return $document
            ->fonts()
            ->create(array_merge(
                $this->storeFile($request, $project), $request->only(
                    'name',
                    'style',
                    'weight',
                ),
                ['creator_id' => auth()->id()]
            ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Document $document, DocumentFont $font)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $font->document_id, 404);

        $disk = Storage::disk('fonts');
        
        abort_unless($disk->exists($font->path), 404);

        return response($disk->get($font->path))->withHeaders([
            'Content-Type', $disk->mimeType($font->path),
            'Content-Disposition', 'attachment; filename="' . $font->name . '"',
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FontRequest $request, Project $project, Document $document, DocumentFont $font)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $font->document_id, 404);

        $font->update($request->only(
            'name',
            'style',
            'weight',
        ));
        
        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Document $document, DocumentFont $font)
    {
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->id == $font->document_id, 404);

        $font->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Store font font
     */
    protected function storeFile(Request $request, Project $project)
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $name = Str::random(30) . '.' . pathinfo($originalName)['extension'];

        return [
            'path' => $file->storeAs($project->slug, $name, 'fonts'),
            'name' => pathinfo($originalName)['filename'],
            'size' => $file->getSize()
        ];
    }
}
