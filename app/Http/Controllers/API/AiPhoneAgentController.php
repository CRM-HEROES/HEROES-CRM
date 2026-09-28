<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AiAgent;
use App\Models\Line;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\UserSetting;
use App\Services\KavkomService;
use App\Services\ProspectCallDataMerger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Receives the outcome of a call handled live by the Gemini Live phone
 * agent (see ai-phone-agent/), once the call has ended. This is a
 * machine-to-machine endpoint (the Node bridge, not a logged-in CRM user),
 * authenticated with a shared secret instead of a Sanctum session.
 */
class AiPhoneAgentController extends Controller
{
    public function ingest(Request $request, ProspectCallDataMerger $merger)
    {
        if (!$this->isAuthorized($request)) {
            Log::channel('ai-phone-agent')->warning('Rejected AI phone agent call: invalid secret.');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'call_uuid' => ['nullable', 'string'],
            'agent_id' => ['nullable', 'integer'],
            'prospect_id' => ['nullable', 'integer', 'exists:prospects,id'],
            'caller_number' => ['nullable', 'string', 'required_without:prospect_id'],
            'destination_number' => ['nullable', 'string'],
            'project_slug' => ['nullable', 'string'],
            'summary' => ['nullable', 'string'],
            'transcript' => ['nullable', 'string'],
            'analysis' => ['required', 'array'],
            'test_mode' => ['nullable', 'boolean'],
        ]);

        $summary = (string) ($data['summary'] ?? data_get($data, 'analysis.summary') ?? '');
        $summary = trim($summary) !== '' ? $summary : null;

        if (!empty($data['prospect_id'])) {
            // Outbound AI call: the prospect is already known from the
            // trigger step, no need to guess it from a phone number.
            $prospect = Prospect::withoutGlobalScopes()->find($data['prospect_id']);
        } else {
            $project = $this->resolveProject($data['project_slug'] ?? null, $data['destination_number'] ?? null);
            if (!$project) {
                Log::channel('ai-phone-agent')->warning('AI phone agent call could not be linked to a project.', [
                    'call_uuid' => $data['call_uuid'] ?? null,
                    'project_slug' => $data['project_slug'] ?? null,
                    'destination_number' => $data['destination_number'] ?? null,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "Aucun projet ne correspond au numéro appelé ou au project_slug fourni.",
                ], 200);
            }
            $prospect = $this->resolveOrCreateProspect($project, $data['caller_number']);
        }

        if (!$prospect) {
            return response()->json(['success' => false, 'message' => 'Prospect introuvable.'], 200);
        }

        $analysis = $data['analysis'];
        $updates = $merger->buildProspectUpdates($prospect, $analysis);
        $meta = $merger->buildMeta($prospect, $analysis, 'ai_phone_agent_last_analysis');
        $meta['ai_phone_agent_last_analysis']['call_uuid'] = $data['call_uuid'] ?? null;
        if (!empty($data['test_mode'])) {
            // This CRM has no dedicated prospect-status column. Keep test
            // qualification visibly pending in metadata rather than treating
            // it as a final production update.
            $meta['ai_phone_agent_last_analysis']['status'] = 'a_valider_humain';
            $meta['ai_phone_agent_last_analysis']['test'] = true;
        }
        $updates['meta'] = $meta;
        $prospect->update($updates);

        $interaction = $prospect->interactions()->create([
            'creator_id' => null,
            'from_user' => false,
            'number' => $data['destination_number'] ?? null,
            'from_number' => $data['caller_number'] ?? $prospect->phone_number ?? $prospect->mobile_phone_number,
            'source' => 'ai_phone_agent',
            'status' => 'completed',
            'ended_at' => now(),
            'path' => null,
            'size' => 0,
            'data' => [
                'call_uuid' => $data['call_uuid'] ?? null,
                'agent_id' => $data['agent_id'] ?? null,
                'transcript' => $data['transcript'] ?? null,
                'analysis' => $analysis,
                'summary' => $summary,
            ],
        ]);

        Log::channel('ai-phone-agent')->info('AI phone agent call ingested.', [
            'call_uuid' => $data['call_uuid'] ?? null,
            'agent_id' => $data['agent_id'] ?? null,
            'project_id' => $prospect->project_id,
            'prospect_id' => $prospect->id,
            'interaction_id' => $interaction->id,
        ]);

        return response()->json([
            'success' => true,
            'prospect_id' => $prospect->id,
            'interaction_id' => $interaction->id,
        ], 200);
    }

    /**
     * Triggered by the "Appeler avec l'IA" button on a prospect. Resolves
     * the current user's Kavkom extension (same as the human click-to-call
     * flow, see KavkomController::call()) and asks the ai-phone-agent
     * bridge to set up a 3-way conference: the user's extension, the
     * prospect, and the Gemini Live bot.
     */
    public function trigger(Request $request, KavkomService $kavkomService)
    {
        Log::channel('ai-phone-agent')->info('AI phone call trigger received.', [
            'user_id' => $request->user()?->id,
            'prospect_id' => $request->input('prospect_id'),
        ]);

        $data = $request->validate([
            'prospect_id' => ['required', 'integer', 'exists:prospects,id'],
            'destination' => ['nullable', 'string'],
            'agent_id' => ['nullable', 'integer'],
        ]);

        $prospect = Prospect::findOrFail($data['prospect_id']);
        $agent = $this->resolveAgent(
            $prospect->project_id,
            $data['agent_id'] ?? $prospect->ai_agent_id
        );
        if (!$agent) {
            Log::channel('ai-phone-agent')->warning('AI call rejected: no active agent configured.', [
                'prospect_id' => $prospect->id,
                'project_id' => $prospect->project_id,
                'requested_agent_id' => $data['agent_id'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => "Aucun agent IA actif n'est configuré pour ce projet.",
            ], 200);
        }
        $destination = ($data['destination'] ?? null) ?: ($prospect->mobile_phone_number ?: $prospect->phone_number);
        if (empty($destination)) {
            return response()->json([
                'success' => false,
                'message' => "Ce prospect n'a pas de numéro de téléphone.",
            ], 200);
        }

        $bridgeUrl = config('services.ai_phone_agent.bridge_url');
        $secret = config('services.ai_phone_agent.secret');
        if (empty($bridgeUrl) || empty($secret)) {
            return response()->json([
                'success' => false,
                'message' => "L'agent vocal IA n'est pas configuré (URL du service ou secret manquant).",
            ], 200);
        }

        $kavkomConfig = $agent->kavkom_config ?: [];
        if (empty($kavkomConfig['extension']) || empty($kavkomConfig['password']) || empty($kavkomConfig['user_context'])) {
            return response()->json([
                'success' => false,
                'message' => "La configuration Kavkom de l'agent est incomplète : extension, contexte SIP et mot de passe sont requis.",
            ], 200);
        }

        $userExtension = $this->getUserKavkomExtension($request, $prospect->project_id);
        $extensionSource = 'user_line';

        // In a local test, the dedicated extension configured on the AI
        // agent is not the user extension. Use an explicitly configured
        // Linphone/CRM extension before falling back to the agent extension.
        // Production still requires a line assigned to the current user.
        if (!$userExtension && app()->environment(['local', 'testing'])) {
            $userExtension = (string) (config('services.ai_phone_agent.test_user_extension') ?: $kavkomConfig['extension']);
            $extensionSource = config('services.ai_phone_agent.test_user_extension')
                ? 'configured_local_test_extension'
                : 'agent_extension_local_fallback';
            Log::channel('ai-phone-agent')->warning('No Kavkom line assigned to CRM user; using local test extension for AI call test.', [
                'user_id' => $request->user()?->id,
                'prospect_id' => $prospect->id,
                'agent_id' => $agent->id,
                'extension' => $userExtension,
            ]);
        }

        if (!$userExtension) {
            return response()->json([
                'success' => false,
                'message' => "Configuration Kavkom utilisateur manquante : impossible de joindre l'extension CRM.",
            ], 200);
        }

        try {
            Log::channel('ai-phone-agent')->info('Calling AI bridge.', [
                'prospect_id' => $prospect->id,
                'agent_id' => $agent->id,
                'agent_extension' => $kavkomConfig['extension'],
                'agent_user_context' => $kavkomConfig['user_context'],
                'agent_transport' => $kavkomConfig['transport'] ?? 'tls',
                'agent_sip_port' => (int) ($kavkomConfig['sip_port'] ?? (($kavkomConfig['transport'] ?? 'tls') === 'tls' ? 5061 : 5060)),
                'user_extension' => $userExtension,
                'user_extension_source' => $extensionSource,
                'destination_digits' => preg_replace('/\D+/', '', $destination),
            ]);
            $response = Http::withHeaders(['X-AI-Agent-Secret' => $secret])
                ->timeout(15)
                ->post(rtrim($bridgeUrl, '/').'/calls', [
                    'prospect_id' => $prospect->id,
                    'destination_number' => $destination,
                    'user_extension' => $userExtension,
                    'agent_id' => $agent->id,
                    'kavkom_config' => [
                        'extension' => $kavkomConfig['extension'],
                        'password' => $kavkomConfig['password'],
                        'user_context' => $kavkomConfig['user_context'],
                        'transport' => $kavkomConfig['transport'] ?? 'tls',
                        'sip_port' => (int) ($kavkomConfig['sip_port'] ?? (($kavkomConfig['transport'] ?? 'tls') === 'tls' ? 5061 : 5060)),
                    ],
                    'agent' => [
                        'name' => $agent->name,
                        'script' => $agent->script,
                        'instructions' => $agent->instructions,
                        'config' => $agent->config ?: [],
                    ],
                ]);
        } catch (\Throwable $exception) {
            Log::channel('ai-phone-agent')->warning('AI phone agent bridge unreachable.', ['error' => $exception->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => "Impossible de joindre le service d'agent vocal IA.",
            ], 200);
        }

        if (!$response->successful()) {
            Log::channel('ai-phone-agent')->warning('AI phone agent bridge rejected the call request.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json([
                'success' => false,
                'message' => data_get($response->json(), 'message') ?: "Le service d'agent vocal IA a refusé la demande.",
            ], 200);
        }

        Log::channel('ai-phone-agent')->info('AI bridge accepted call.', [
            'prospect_id' => $prospect->id,
            'agent_id' => $agent->id,
            'bridge_status' => $response->status(),
            'call_uuid' => data_get($response->json(), 'call_uuid'),
        ]);

        return response()->json($response->json() ?: ['success' => true], 200);
    }

    private function getUserKavkomExtension(Request $request, ?int $projectId = null): ?string
    {
        // Kavkom credentials are now managed in Project > Lignes. Resolve
        // the same line as the normal CRM softphone. The old user setting is
        // only retained for backwards compatibility.
        $line = Line::query()
            ->where('operator', 'kavkom')
            ->where('user_id', $request->user()->id)
            ->when($projectId, fn ($query) => $query->where('project_id', $projectId))
            ->first();

        $config = $line ? (array) $line->config : [];

        if (empty($config['api_token']) || empty($config['domain_uuid'])) {
            $setting = UserSetting::query()
                ->whereNull('project_id')
                ->where('user_id', $request->user()->id)
                ->where('key', 'kavkom')
                ->first();
            $config = $setting ? (array) $setting->value : [];
        }

        if (empty($config['api_token']) || empty($config['domain_uuid'])) {
            return null;
        }

        $extension = app(KavkomService::class)->resolveExtension(
            $config['api_token'],
            $config['domain_uuid'],
            $config['extension'] ?? null
        );

        return $extension['success'] ? (string) $extension['extension'] : null;
    }

    private function resolveAgent(int $projectId, ?int $agentId): ?AiAgent
    {
        $query = AiAgent::query()
            ->where('project_id', $projectId)
            ->where('is_active', true);

        return $agentId
            ? $query->whereKey($agentId)->first()
            : $query->latest('id')->first();
    }

    private function isAuthorized(Request $request): bool
    {
        $secret = (string) config('services.ai_phone_agent.secret');
        $provided = (string) $request->header('X-AI-Agent-Secret', '');

        return $secret !== '' && $provided !== '' && hash_equals($secret, $provided);
    }

    private function resolveProject(?string $projectSlug, ?string $destinationNumber): ?Project
    {
        if ($projectSlug) {
            $project = Project::where('slug', $projectSlug)->first();
            if ($project) {
                return $project;
            }
        }

        $digits = $this->digitsOnly($destinationNumber);
        if ($digits === '') {
            return null;
        }

        return Project::whereRaw(
            "REPLACE(REPLACE(REPLACE(REPLACE(phone_number, ' ', ''), '-', ''), '.', ''), '+', '') = ?",
            [$digits]
        )->first();
    }

    private function resolveOrCreateProspect(Project $project, string $callerNumber): Prospect
    {
        $digits = $this->digitsOnly($callerNumber);

        $prospect = $digits !== ''
            ? Prospect::withoutGlobalScopes()
                ->where('project_id', $project->id)
                ->whereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(phone_number, ' ', ''), '-', ''), '.', ''), '+', '') = ?
                     OR REPLACE(REPLACE(REPLACE(REPLACE(mobile_phone_number, ' ', ''), '-', ''), '.', ''), '+', '') = ?",
                    [$digits, $digits]
                )
                ->first()
            : null;

        if ($prospect) {
            return $prospect;
        }

        return Prospect::withoutGlobalScopes()->create([
            'project_id' => $project->id,
            'phone_number' => $callerNumber,
        ]);
    }

    private function digitsOnly(?string $number): string
    {
        return preg_replace('/\D+/', '', (string) $number) ?: '';
    }
}
