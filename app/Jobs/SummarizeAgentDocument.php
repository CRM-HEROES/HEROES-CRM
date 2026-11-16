<?php

namespace App\Jobs;

use App\Models\AgentDocument;
use App\Agents\Claude\ClaudeException;
use App\Agents\AgentDocumentSummarizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SummarizeAgentDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $documentId)
    {
    }

    public function handle(AgentDocumentSummarizer $summarizer): void
    {
        $document = AgentDocument::with('agent.user')->findOrFail($this->documentId);

        try {
            $user = $document->agent?->user;
            if (!$user) {
                throw new \RuntimeException('Le propriétaire du document est introuvable.');
            }

            $bytes = Storage::disk('agent_documents')->get($document->path);
            $summary = $summarizer->summarize($user, $document->name, $document->mime, $bytes);
            $document->update(['summary' => $summary, 'status' => 'ready']);
        } catch (Throwable $e) {
            $document->update(['status' => 'failed']);
            throw $e;
        }
    }
}
