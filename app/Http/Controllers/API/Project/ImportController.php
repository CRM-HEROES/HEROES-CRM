<?php
 
namespace App\Http\Controllers\API\Project;

use App\Filters\ImportRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'notify_welcome_sms' => 'sometimes|boolean',
            'welcome_sms_message' => 'sometimes|nullable|string|required_if:notify_welcome_sms,true',
        ]);

        $import->update($request->only(
            'name',
            'headers',
            'mapping',
            'duplicates_fields',
            'is_processing',
            'notify_welcome_sms',
            'welcome_sms_message'
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
     * Download the CSV export of a public Google Sheets URL
     * and store it on the "imports" disk, exactly like an uploaded file.
     */
    protected function storeGoogleSheet(string $url, Project $project)
    {
        if (!preg_match('~docs\.google\.com/spreadsheets/d/([a-zA-Z0-9_-]+)~', $url, $matches)) {
            throw ValidationException::withMessages([
                'url' => "Ce lien Google Sheets n'est pas valide.",
            ]);
        }

        $spreadsheetId = $matches[1];
        $gid = null;
        if (preg_match('~[?#&]gid=(\d+)~', $url, $gidMatches)) {
            $gid = $gidMatches[1];
        }

        // Ne pas ajouter "gid=0" par défaut : ce n'est pas toujours l'identifiant
        // du premier onglet (l'export renvoie alors 400/404). Sans "gid", Google
        // exporte automatiquement le premier onglet du document.
        $exportUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv"
            . ($gid !== null ? "&gid={$gid}" : '');

        try {
            $response = Http::timeout(15)->connectTimeout(5)->get($exportUrl);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw ValidationException::withMessages([
                'url' => "Impossible de contacter Google Sheets (problème réseau). Réessayez.",
            ]);
        }

        if ($response->failed() || Str::contains($response->header('Content-Type', ''), 'text/html')) {
            throw ValidationException::withMessages([
                'url' => "Impossible de récupérer ce fichier Google Sheets. Vérifiez que le lien de partage autorise \"Tous les utilisateurs disposant du lien\" à voir le document.",
            ]);
        }

        $name = Str::random(30) . '.csv';
        $path = $project->slug . '/' . $name;

        Storage::disk('imports')->put($path, $response->body());

        return [
            'path' => $path,
            'size' => strlen($response->body()),
        ];
    }
}