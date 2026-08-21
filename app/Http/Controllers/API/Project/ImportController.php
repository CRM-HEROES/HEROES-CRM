<?php
 
namespace App\Http\Controllers\API\Project;

use App\Filters\ImportRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\Project;
use App\Services\Import\GoogleSheetDownloader;
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
            'url' => 'required_if:source,google_sheets',
            'sync_enabled' => 'sometimes|boolean',
            'sync_interval_minutes' => 'sometimes|nullable|integer|min:5',
        ]);

        $import = $project
            ->imports()
            ->create(array_merge($request->only(
                'name',
                'source',
                'field_delimiter',
                'field_enclosure',
                'sync_enabled',
                'sync_interval_minutes'
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
        abort_unless(in_array($import->source, ['file', 'google_sheets']), 400);

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
            'selected_sheets' => 'sometimes|nullable|array',
            'notify_welcome_sms' => 'sometimes|boolean',
            'welcome_sms_message' => 'sometimes|nullable|string|required_if:notify_welcome_sms,true',
            'welcome_sms_source' => 'sometimes|nullable|in:brevo,smsbox,ultramsg,mtarget',
            'url' => 'sometimes|nullable|url',
            'sync_enabled' => 'sometimes|boolean',
            'sync_interval_minutes' => 'sometimes|nullable|integer|min:5',
        ]);

        $import->update($request->only(
            'name',
            'headers',
            'mapping',
            'duplicates_fields',
            'selected_sheets',
            'is_processing',
            'notify_welcome_sms',
            'welcome_sms_message',
            'welcome_sms_source',
            'url',
            'sync_enabled',
            'sync_interval_minutes'
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
        if ($request->input('source') == 'google_sheets') {
            return $this->storeGoogleSheet($request->input('url'), $project);
        }

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

    /**
     * Download the XLSX export of a public Google Sheets URL (all of its
     * sheets/tabs) and store it on the "imports" disk, exactly like an
     * uploaded file. The URL itself is persisted alongside the file so
     * SyncGoogleSheetImports can re-download it periodically.
     */
    protected function storeGoogleSheet(string $url, Project $project)
    {
        $downloader = app(GoogleSheetDownloader::class);

        $spreadsheetId = $downloader->extractSpreadsheetId($url);

        return array_merge(
            $downloader->download($spreadsheetId, $project->slug),
            ['url' => $url]
        );
    }
}