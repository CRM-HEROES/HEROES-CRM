<?php
 
namespace App\Http\Controllers\API\Project;

use App\Filters\ImportRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, ImportRequestFilters $importRequestFilters)
    {
        return $project
            ->imports()
            ->filter($importRequestFilters)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $import = $project
            ->imports()
            ->create(array_merge($request->only(
                'name', 
                'source', 
                'field_delimiter', 
                'field_enclosure'
            ), 
            $this->storeFile($request, $project),
            [
                'creator_id' => auth()->id(),
            ]));

        $import->refresh();

        return $import;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);

        return $import;
    }

    /**
     * Download import file
     */
    public function download(Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_unless($import->source == 'file', 400);

        $disk = Storage::disk('imports');
        
        abort_unless($disk->exists($import->path), 404);

        return response($disk->get($import->path))
            ->header('Content-Type', $disk->mimeType($import->path))
            ->header('Content-Disposition', 'attachment; filename="' . $import->name . '.' . pathinfo($import->path)['extension'] . '"');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);

        $this->validate(request(), [
            'mapping' => 'sometimes|array',
            'duplicates_fields' => 'sometimes|array',
        ]);

        $import->update($request->only(
            'name',
            'headers',
            'mapping',
            'duplicates_fields',
            'is_processing'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);

        if ($request->input('prospects', false)) {
            $import->prospects()->delete();
        }

        if ($request->input('import', true)) {
            $import->delete();
        }

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Store import file
     */
    protected function storeFile(Request $request, Project $project)
    {
        if ($request->input('source') != 'file') {
            return [];
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $name = Str::random(30) . '.' . pathinfo($originalName)['extension'];

        return [
            'path' => $file->storeAs($project->slug, $name, 'imports'),
            'size' => $file->getSize()
        ];
    }
}
