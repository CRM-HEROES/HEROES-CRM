<?php

namespace App\Http\Controllers\API\Agent;

use App\Http\Controllers\Controller;
use App\Jobs\SummarizeAgentDocument;
use App\Models\Agent;
use App\Models\AgentDocument;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DocumentController extends Controller
{
    public function index(Request $request, int $agent)
    {
        $agent = Agent::query()->where('user_id', $request->user()->id)->findOrFail($agent);

        return $agent->documents()->orderByDesc('id')->get(['id', 'name', 'mime', 'status', 'summary', 'created_at']);
    }

    public function store(Request $request, int $agent)
    {
        $agent = Agent::query()->where('user_id', $request->user()->id)->findOrFail($agent);
        $data = $request->validate([
            'document' => 'required|file|max:10240',
        ]);

        $file = $data['document'];
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = strtolower((string) $file->getMimeType());
        $allowed = ['pdf' => ['application/pdf'], 'txt' => ['text/plain'], 'md' => ['text/plain', 'text/markdown']];

        if (!isset($allowed[$extension]) || !in_array($mime, $allowed[$extension], true)) {
            throw ValidationException::withMessages([
                'document' => 'Seuls les fichiers PDF, TXT ou MD valides sont acceptés.',
            ]);
        }

        $path = $file->store('agent-' . $agent->id, 'agent_documents');
        if (!$path) {
            throw new \RuntimeException('Le document n’a pas pu être stocké.');
        }

        $document = $agent->documents()->create([
            'name' => basename($file->getClientOriginalName()),
            'mime' => $mime,
            'path' => $path,
            'status' => 'queued',
        ]);

        SummarizeAgentDocument::dispatch($document->id)->onQueue('agents');

        return response([
            'id' => $document->id,
            'name' => $document->name,
            'status' => $document->status,
        ], 202);
    }

    public function show(Request $request, int $agent, int $document)
    {
        $agent = Agent::query()->where('user_id', $request->user()->id)->findOrFail($agent);
        $document = AgentDocument::query()->where('agent_id', $agent->id)->findOrFail($document);

        return [
            'id' => $document->id,
            'name' => $document->name,
            'status' => $document->status,
            'summary' => $document->summary,
        ];
    }
}
