<?php

namespace App\Http\Controllers\API\Project\Line;

use App\Http\Controllers\Controller;
use App\Models\Line;
use App\Models\Project;
use App\Services\CloudTalkExportScript;
use Illuminate\Http\Request;

class CloudTalkExportController extends Controller
{
    public function configurations(Project $project)
    {
        $this->authorizeExport($project);

        $configurations = $project->lines()->where('operator', 'cloudtalk')->orderBy('name')->get()
            ->map(fn (Line $line) => [
                'id' => $line->id,
                'name' => $line->name,
                'numero' => $line->numero,
                'agent_name' => $line->cloudtalk_agent_name,
            ])->values();

        return response()->json(['configurations' => $configurations, 'timezone' => 'UTC'])
            ->header('Cache-Control', 'private, no-store');
    }

    public function credentials(Project $project, string $line)
    {
        $this->authorizeExport($project);
        $configuration = $project->lines()->where('operator', 'cloudtalk')->whereKey($line)->firstOrFail();
        $credentials = [];
        foreach (['api_key_id', 'api_key_secret'] as $key) {
            $value = data_get($configuration->config, $key);
            $credentials[$key] = is_string($value) ? $value : '';
        }

        // Credentials stay scoped to the selected line in the authenticated user's project.
        return response()->json($credentials)->header('Cache-Control', 'private, no-store')
            ->header('Pragma', 'no-cache');
    }

    public function download(Request $request, Project $project, CloudTalkExportScript $export)
    {
        $this->authorizeExport($project);
        $data = $request->validate([
            'line_id' => 'nullable|integer',
            'api_key_id' => 'required|string|max:4096',
            'api_key_secret' => 'required|string|max:4096',
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d|after_or_equal:date_from',
            'timezone' => 'required|string|timezone',
            'download_audio' => 'required|boolean',
        ]);

        if (isset($data['line_id'])) {
            $project->lines()->where('operator', 'cloudtalk')->whereKey($data['line_id'])->firstOrFail();
        }

        $script = $export->generate($data, $data['date_from'], $data['date_to'], $data['timezone'], (bool) $data['download_audio']);
        $filename = 'cloudtalk-export-' . $data['date_from'] . '-' . $data['date_to'] . '.ps1';

        // Generate only the small script in memory. No provider calls or export files on the CRM server.
        return response($script, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    protected function authorizeExport(Project $project): void
    {
        abort_unless(auth()->user()?->can('', $project), 403);
    }
}
