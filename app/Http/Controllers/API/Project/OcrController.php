<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Ocr;
use App\Models\Project;
use App\Services\Mindee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OcrController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function store(Request $request, Project $project, Mindee $mindee)
    {
        $this->validate($request, [
            'file' => 'required',
            'type' => 'required'
        ]);

        return $project->ocrs()->create(array_merge([
                'api' => $request->input('api', "mindee"),
                'type' => $request->input('type'),
                'creator_id' => auth()->id(),
            ], 
            $this->storeFile($request, $project)
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function show(Project $project, Ocr $ocr)
    {
        abort_unless($project->id == $ocr->project_id, 404);
        
        $ocr->load("prospect");

        return $ocr;
    }

    /**
     * Store file
     */
    protected function storeFile(Request $request, Project $project)
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $name = Str::random(30) . '.' . pathinfo($originalName)['extension'];

        return [
            'path' => $file->storeAs($project->slug, $name, 'ocr'),
            'name' => $originalName,
            'size' => $file->getSize()
        ];
    }

}
