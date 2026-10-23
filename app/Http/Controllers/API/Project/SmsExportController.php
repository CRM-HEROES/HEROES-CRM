<?php

namespace App\Http\Controllers\API\Project;

use App\Exports\SmsDownload;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class SmsExportController extends Controller
{
    public function create(Request $request, Project $project, SmsDownload $download)
    {
        abort_unless(auth()->user()?->can('', $project), 403);
        $data = $request->validate([
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d|after_or_equal:date_from',
            'timezone' => 'required|string|timezone',
            'count' => 'sometimes|integer|min:0',
        ]);
        $data['count'] = (int) ($data['count'] ?? 0);
        $id = $download->enqueue($project, auth()->user(), $data);

        return response()->json([
            'message' => 'Votre export SMS est en cours de préparation…',
            'status_url' => route('api.project.sms.export.status', [$project, $id]),
            'download_url' => route('api.project.sms.export.download', [$project, $id]),
        ], 202)->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function status(Project $project, string $exportId, SmsDownload $download)
    {
        $state = $this->authorizeDownload($project, $exportId, $download);

        return response()->json([
            'status' => $state['status'],
            'rows' => $state['rows'] ?? null,
        ])->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function download(Project $project, string $exportId, SmsDownload $download)
    {
        $this->authorizeDownload($project, $exportId, $download);

        return $download->preparedFile($exportId);
    }

    protected function authorizeDownload(Project $project, string $id, SmsDownload $download): array
    {
        abort_unless(auth()->user()?->can('', $project), 403);
        $state = $download->status($id);
        abort_unless(($state['project_id'] ?? null) == $project->id, 404);
        abort_unless(($state['creator_id'] ?? null) == auth()->id(), 403);

        return $state;
    }
}
