<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Prospect;
use App\Services\Anthropic;
use App\Utils\Document\ProspectPDFRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Drafts an AI-written quote/proposal for a prospect and stores it as a
 * PDF in their document folder — see GenerateAiQuoteOnLabelAttachedListener
 * for the trigger. Never sends anything: a human decides separately,
 * through the CRM's existing document-sending features, whether and when
 * to actually send this draft to the prospect.
 */
class GenerateAiQuoteDraft implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    public function __construct(public int $prospectId, public ?int $userId) {}

    public function handle(Anthropic $anthropic): void
    {
        $prospect = Prospect::withoutGlobalScopes()->with('project')->find($this->prospectId);
        if (!$prospect || !$prospect->project) {
            Log::channel('ai-quote')->warning('AI quote generation skipped: prospect or project not found.', [
                'prospect_id' => $this->prospectId,
            ]);
            return;
        }

        $templateName = config('services.ai_quote.document_template_name');
        $document = Document::withoutGlobalScopes()
            ->where('project_id', $prospect->project_id)
            ->where('for', 'prospect')
            ->where('name', $templateName)
            ->first();

        if (!$document) {
            Log::channel('ai-quote')->warning('AI quote generation skipped: document template not found. Create it first.', [
                'prospect_id' => $prospect->id,
                'project_id' => $prospect->project_id,
                'expected_template_name' => $templateName,
            ]);
            return;
        }

        // $document->project lazily resolves through Project's own global
        // scope, which can come back empty outside a normal web request.
        // We already have the right Project instance from $prospect
        // (loaded above without scopes) — reuse it instead of letting the
        // relation reload and fail.
        $document->setRelation('project', $prospect->project);

        $content = $this->draftContent($anthropic, $prospect);
        if (!$content) {
            throw new \RuntimeException('Claude returned no quote content.');
        }

        $meta = $prospect->meta ?: [];
        $meta['ai_quote_content'] = $content;
        $meta['ai_quote_generated_at'] = now()->toIso8601String();
        $prospect->meta = $meta;
        $prospect->save();

        // Auth::onceUsingId()/login() are SessionGuard-only and don't exist
        // on the RequestGuard this app's "sanctum" guard resolves to.
        // setUser() is part of the base Guard contract every guard
        // implements, so it works regardless of guard type — and since
        // this only affects this request/job's in-memory guard instance,
        // it's naturally scoped to this job run, same intent as "once".
        if ($this->userId && ($actingUser = \App\Models\User::find($this->userId))) {
            Auth::setUser($actingUser);
        }

        $outputFile = (new ProspectPDFRenderer($document, $prospect))->render();

        $this->storeInFolder($prospect, $document, $outputFile);

        Log::channel('ai-quote')->info('AI quote generated.', ['prospect_id' => $prospect->id]);
    }

    private function draftContent(Anthropic $anthropic, Prospect $prospect): ?string
    {
        $context = [
            'Prénom' => $prospect->first_name,
            'Nom' => $prospect->last_name,
            'Société' => $prospect->company_name,
            'Ville' => $prospect->city,
        ];

        // Reuse whatever a previous AI call (phone agent or post-call
        // analysis) already learned about this prospect's need, instead
        // of asking Claude to guess — this is the "sans ressaisie" part
        // of the brief.
        $priorAnalysis = data_get($prospect->meta, 'ai_phone_agent_last_analysis')
            ?? data_get($prospect->meta, 'kavkom_last_analysis');
        if ($priorAnalysis) {
            $context['Besoin exprimé'] = data_get($priorAnalysis, 'project');
            $context['Budget évoqué'] = data_get($priorAnalysis, 'budget');
            $context['Résumé du dernier échange'] = data_get($priorAnalysis, 'summary');
            $context['Besoins identifiés'] = implode(', ', (array) data_get($priorAnalysis, 'needs', []));
        }

        $context = array_filter($context, fn ($value) => filled($value));

        $prompt = <<<'PROMPT'
Rédige le texte d'un devis/proposition commerciale pour ce prospect, à partir des informations fournies ci-dessous. Ne déduis ni n'invente aucune information absente. Réponds uniquement avec le contenu HTML du devis (pas de ```html```, pas de <html>/<body>), en utilisant uniquement des balises <p>, <strong>, <br>, <ul>/<li> — pas de <table>, pas de styles inline. Structure : une phrase d'introduction personnalisée, un paragraphe qui reprend le besoin exprimé, un paragraphe de proposition/prochaines étapes. Reste bref (moins de 200 mots), professionnel et en français.

INFORMATIONS DISPONIBLES :
PROMPT;

        $prompt .= "\n" . collect($context)->map(fn ($value, $key) => "{$key}: {$value}")->implode("\n");

        $raw = $anthropic->sendMessage([['role' => 'user', 'content' => $prompt]], 900);

        return $raw ? trim($raw) : null;
    }

    /** Mirrors DocumentController::show()'s storage logic for prospect documents. */
    private function storeInFolder(Prospect $prospect, Document $document, string $outputFile): void
    {
        if (!$document->folder_id) {
            Log::channel('ai-quote')->warning('AI quote document template has no folder_id, PDF generated but not stored.', [
                'prospect_id' => $prospect->id,
                'document_id' => $document->id,
            ]);
            return;
        }

        $documentDisk = Storage::disk('documents');
        $folderDisk = Storage::disk('folders');

        $timestamp = now()->format('Ymd_His');
        $path = $prospect->project->slug . '/' . $prospect->id . '/' . $document->folder_id
            . '/devis_ia_' . $timestamp . '_' . Str::random(8) . '.pdf';

        $pathinfo = pathinfo($outputFile);
        $stream = $documentDisk->readStream($prospect->project->slug . '/' . $pathinfo['basename']);
        $folderDisk->writeStream($path, $stream);

        $file = $prospect->files()->create([
            'folder_id' => $document->folder_id,
            'from_user' => 1,
            'creator_id' => $this->userId,
            'path' => $path,
            'name' => $document->name . ' - ' . $timestamp,
            'size' => filesize($outputFile),
        ]);

        // Files are only visible in listings to users explicitly shared on
        // the folder or the file (see FileController::index). Without this,
        // the generated quote would be retrievable by direct link but never
        // show up in the prospect's file list for the user who triggered it.
        if ($this->userId) {
            $file->users()->attach($this->userId);
        }
    }
}
