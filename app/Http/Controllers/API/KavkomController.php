<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Interaction;
use App\Models\Line;
use App\Models\KavkomCall;
use App\Models\Prospect;
use App\Services\KavkomService;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KavkomController extends Controller
{
    public function test(Request $request, KavkomService $service)
    {
        $data = $request->validate([
            'api_token' => ['required', 'string'],
            'domain_uuid' => ['required', 'string'],
        ]);

        $result = $service->testConnection($data['api_token'], $data['domain_uuid']);

        return response()->json($result, 200);
    }

    public function call(Request $request, KavkomService $service)
    {
        $data = $request->validate([
            'destination' => ['required', 'string'],
            'prospect_id' => ['nullable', 'integer', 'exists:prospects,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);

        $projectId = $data['project_id'] ?? ($data['prospect_id']
            ? Prospect::query()->whereKey($data['prospect_id'])->value('project_id')
            : null);
        $config = $this->getUserKavkomConfig($request, $projectId);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Kavkom manquante : renseignez le jeton API et le domain_uuid dans les paramètres.',
            ], 200);
        }

        $callerId = preg_replace('/\D+/', '', (string) ($config['phone_number'] ?? ''));
        if (strlen($callerId) < 8 || preg_match('/^0+$/', $callerId)) {
            return response()->json([
                'success' => false,
                'message' => 'Configurez un numéro sortant Kavkom (DID autorisé) dans les paramètres avant de lancer un appel. Le PBX utilise actuellement 0000000000, ce qui bloque les appels sortants.',
            ], 200);
        }

        $extension = $service->resolveExtension(
            $config['api_token'],
            $config['domain_uuid'],
            $config['extension'] ?? null
        );

        if (!$extension['success']) {
            return response()->json($extension, 200);
        }

        $effectiveCallerId = preg_replace(
            '/\D+/',
            '',
            (string) ($extension['effective_caller_id_number'] ?? '')
        );

        // Kavkom's originate endpoint uses src_cid_number only for the leg
        // that rings the agent. The caller ID presented to the prospect is
        // bound to the extension in Kavkom (effective_caller_id_number).
        if ($effectiveCallerId !== '' && $effectiveCallerId !== $callerId) {
            return response()->json([
                'success' => false,
                'message' => "Le DID sortant de l'extension Kavkom {$extension['extension']} est {$effectiveCallerId}, alors que le CRM est configuré avec {$callerId}. Dans Kavkom, attribuez {$callerId} comme numéro sortant à l'extension {$extension['extension']}, puis réessayez.",
            ], 200);
        }

        $result = $service->originateCall(
            $config['api_token'],
            $config['domain_uuid'],
            $extension['extension'],
            $data['destination'],
            ['src_cid_number' => $callerId]
        );

        if ($result['success'] && !empty($result['call_uuid'])) {
            // The CDR webhook may arrive before this API response. Complete
            // that record so its recording can still be transcribed.
            $call = KavkomCall::firstOrNew(['call_uuid' => $result['call_uuid']]);
            $call->fill([
                'prospect_id' => $call->prospect_id ?: ($data['prospect_id'] ?? null),
                'user_id' => $call->user_id ?: $request->user()->id,
                'domain_uuid' => $call->domain_uuid ?: $config['domain_uuid'],
                'direction' => $call->direction ?: 'outbound',
                'destination' => $call->destination ?: $data['destination'],
                'status' => $call->status ?: 'initiated',
            ])->save();

            Log::channel('kavkom')->info('Kavkom call linked to CRM prospect.', [
                'call_uuid' => $result['call_uuid'],
                'prospect_id' => $data['prospect_id'] ?? null,
                'user_id' => $request->user()->id,
            ]);
        } elseif ($result['success']) {
            Log::channel('kavkom')->warning('Kavkom call started without call_uuid; automatic transcription will not be linked.', [
                'prospect_id' => $data['prospect_id'] ?? null,
                'user_id' => $request->user()->id,
            ]);
        }

        return response()->json($result, 200);
    }

    /**
     * SIP credentials (extension, password, user_context) needed by the
     * browser to register a WebRTC softphone. The API token itself is
     * never sent to the browser, only these derived, extension-scoped
     * credentials.
     */
    public function credentials(Request $request, KavkomService $service)
    {
        $data = $request->validate([
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);
        $config = $this->getUserKavkomConfig($request, $data['project_id'] ?? null);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Kavkom manquante : renseignez le jeton API et le domain_uuid dans les paramètres.',
            ], 200);
        }

        $result = $service->resolveExtension(
            $config['api_token'],
            $config['domain_uuid'],
            $config['extension'] ?? null
        );

        return response()->json($result, 200);
    }

    /**
     * Log an inbound call in the prospect history.
     *
     * Called by the global softphone on ringing, answer and hangup. The
     * interaction is opened on the first event and reused for the following
     * ones (the browser sends back the id it received), so a single call
     * produces a single interaction whose status follows the call.
     */
    public function incoming(Request $request)
    {
        $data = $request->validate([
            'number' => ['required', 'string'],
            'status' => ['required', 'string', 'in:ringing,answered,hangup,missed'],
            'interaction_id' => ['nullable', 'integer', 'exists:interactions,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);

        $line = $this->getUserKavkomLine($request, $data['project_id'] ?? null);
        $projectId = $line?->project_id ?? ($data['project_id'] ?? null);
        $prospect = $this->findProspectByNumber($data['number'], $projectId);

        // An unknown caller can still be answered: there is simply no
        // prospect file to attach the interaction to.
        if (!$prospect) {
            return response()->json([
                'success' => true,
                'prospect' => null,
                'interaction_id' => null,
                'message' => "Aucun prospect ne correspond à ce numéro : l'appel entrant n'est pas historisé.",
            ], 200);
        }

        if (!$request->user()->can('prospectInteractionAdd', $prospect->project)) {
            return response()->json([
                'success' => false,
                'prospect' => $this->prospectPayload($prospect),
                'interaction_id' => null,
                'message' => "Vous n'avez pas la permission d'historiser cet appel entrant.",
            ], 200);
        }

        $number = PhoneNumber::digits($data['number']);
        $ended = in_array($data['status'], ['hangup', 'missed'], true);

        $interaction = null;
        if (!empty($data['interaction_id'])) {
            $interaction = Interaction::query()
                ->whereKey($data['interaction_id'])
                ->where('prospect_id', $prospect->id)
                ->first();
        }

        // Retried request, or the answer/hangup that follows the initial
        // ringing: continue the interaction already opened for this call
        // instead of stacking a new one per SIP event.
        $interaction = $interaction ?: $this->findOpenInboundInteraction($prospect->id, $number);

        if (!$interaction) {
            $interaction = new Interaction();
            $interaction->prospect_id = $prospect->id;
        }

        $callerId = preg_replace('/\D+/', '', (string) data_get($line?->config, 'phone_number', ''));

        $interaction->fill([
            'creator_id' => $interaction->creator_id ?: $request->user()->id,
            'from_user' => false,
            'number' => $number,
            'from_number' => $interaction->from_number ?: ($callerId !== '' ? $callerId : null),
            'source' => 'kavkom',
            'status' => $data['status'],
            'started_at' => $interaction->started_at ?: now(),
            'ended_at' => $ended ? now() : null,
        ]);
        $interaction->data = array_merge(
            (array) $interaction->data,
            ['direction' => 'inbound']
        );
        $interaction->save();

        Log::channel('kavkom')->info('Kavkom inbound call interaction saved.', [
            'interaction_id' => $interaction->id,
            'prospect_id' => $prospect->id,
            'status' => $data['status'],
        ]);

        return response()->json([
            'success' => true,
            'prospect' => $this->prospectPayload($prospect),
            'project' => $this->projectPayload($prospect),
            'interaction_id' => $interaction->id,
        ], 200);
    }

    /** Lightweight, authenticated status endpoint used by the call UI debug log. */
    public function callStatus(Request $request, string $callUuid)
    {
        $call = KavkomCall::query()
            ->where('call_uuid', $callUuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'call_uuid' => $call->call_uuid,
            'status' => $call->status,
            'error' => $call->error,
            'has_recording' => (bool) $call->recording_url,
            'interaction_id' => $call->interaction_id,
            'processed_at' => optional($call->processed_at)->toIso8601String(),
        ]);
    }

    /**
     * Comprehensive test that checks:
     * 1. REST API connectivity
     * 2. Available extensions
     * 3. SIP credentials
     * 4. Recommended troubleshooting steps based on errors
     */
    public function testFull(Request $request, KavkomService $service)
    {
        // Test the values currently visible in the modal. Previously this
        // endpoint silently ignored them and diagnosed an older saved setup,
        // which could report a different extension and SIP domain.
        $input = $request->validate([
            'api_token' => ['nullable', 'string'],
            'domain_uuid' => ['nullable', 'string'],
            'extension' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
        ]);

        $config = array_merge(
            $this->getUserKavkomConfig($request) ?: [],
            array_filter($input, fn ($value) => $value !== null && $value !== '')
        );

        if (empty($config['api_token']) || empty($config['domain_uuid'])) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Kavkom manquante : renseignez le jeton API et le domain_uuid dans les paramètres.',
                'steps' => [
                    'Allez dans les paramètres du profil',
                    'Entrez votre jeton API Kavkom',
                    'Entrez votre domain UUID Kavkom',
                ],
            ], 200);
        }

        $results = [
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'tests' => [],
            'recommendations' => [],
        ];

        // Test 1: REST API Connectivity
        $apiTest = $service->testConnection($config['api_token'], $config['domain_uuid']);
        $results['tests'][] = [
            'name' => 'REST API Connectivity',
            'status' => $apiTest['success'] ? 'ok' : 'error',
            'message' => data_get($apiTest, 'message', 'Réponse inattendue de l\'API Kavkom.'),
            'details' => $apiTest['details'] ?? null,
        ];

        if (!$apiTest['success']) {
            $results['success'] = false;
            $results['recommendations'][] = [
                'severity' => 'critical',
                'title' => 'API REST inaccessible',
                'steps' => [
                    'Vérifiez que le jeton API est correct (format et contenu)',
                    'Vérifiez que le domain_uuid est correct',
                    'Testez la connectivité vers https://api.kavkom.com',
                    'Vérifiez que votre firewall n\'a pas bloqué les connexions HTTPS sortantes',
                    'Vérifiez l\'état du serveur Kavkom (peut être en maintenance)',
                    'Attendez quelques minutes et réessayez',
                ],
            ];

            return response()->json($results, 200);
        }

        // Test 2: Extension Resolution
        $extensionTest = $service->resolveExtension(
            $config['api_token'],
            $config['domain_uuid'],
            $config['extension'] ?? null
        );
        $results['tests'][] = [
            'name' => 'Extension Resolution',
            'status' => $extensionTest['success'] ? 'ok' : 'error',
            'message' => data_get(
                $extensionTest,
                'message',
                $extensionTest['success']
                    ? "Extension Kavkom {$extensionTest['extension']} trouvée et activée."
                    : 'Impossible de récupérer les extensions Kavkom.'
            ),
            'extension' => $extensionTest['extension'] ?? null,
            'effective_caller_id_number' => $extensionTest['effective_caller_id_number'] ?? null,
        ];

        $configuredCallerId = preg_replace('/\D+/', '', (string) ($config['phone_number'] ?? ''));
        $effectiveCallerId = preg_replace('/\D+/', '', (string) ($extensionTest['effective_caller_id_number'] ?? ''));

        if ($configuredCallerId !== '' && $effectiveCallerId !== '' && $configuredCallerId !== $effectiveCallerId) {
            $results['success'] = false;
            $results['recommendations'][] = [
                'severity' => 'critical',
                'title' => 'DID sortant différent de celui de l’extension',
                'steps' => [
                    "Le CRM demande {$configuredCallerId}, mais l’extension {$extensionTest['extension']} présente {$effectiveCallerId} aux contacts.",
                    "Dans Kavkom, attribuez {$configuredCallerId} comme DID sortant à l’extension {$extensionTest['extension']}.",
                    'Enregistrez la modification dans Kavkom, puis relancez ce diagnostic.',
                ],
            ];
        }

        if (!$extensionTest['success']) {
            $results['success'] = false;
            $results['recommendations'][] = [
                'severity' => 'critical',
                'title' => 'Aucune extension trouvée',
                'steps' => [
                    'Vérifiez que le domain_uuid est correct',
                    'Créez une extension dans votre configuration Kavkom',
                    'Assurez-vous que l\'extension est activée',
                    'Attendez quelques minutes que la configuration se synchronise',
                ],
            ];

            return response()->json($results, 200);
        }

        // Test 3: SIP Credentials
        $results['tests'][] = [
            'name' => 'SIP Credentials',
            'status' => 'ok',
            'extension' => $extensionTest['extension'],
            'user_context' => $extensionTest['user_context'],
            'password_masked' => str_repeat('*', strlen($extensionTest['password'] ?? '')),
            'message' => 'Les identifiants SIP ont été générés avec succès',
        ];

        // Test 4: WSS Connectivity (optional, informational)
        $wssTest = $this->testWssConnectivity($extensionTest['user_context']);
        $wssUrl = 'wss://' . $extensionTest['user_context'];
        $results['tests'][] = [
            'name' => 'WebSocket Server Connectivity',
            'status' => $wssTest ? 'ok' : 'warning',
            'wss_url' => $wssUrl,
            'message' => $wssTest
                ? 'Serveur WebSocket accessible'
                : 'Vérification du serveur WebSocket (consultez la console du navigateur pour le résultat exact)',
        ];

        if (!$wssTest) {
            $results['recommendations'][] = [
                'severity' => 'critical',
                'title' => 'Serveur WebSocket inaccessible - C\'est LA cause de USER_NOT_REGISTERED',
                'steps' => [
                    'Vérifiez que le domain_uuid est EXACTEMENT correct (copie depuis Kavkom)',
                    'Vérifiez que vous pouvez accéder à ' . $wssUrl . ' depuis votre navigateur',
                    'Vérifiez les certificats SSL du serveur (peut-être auto-signé ou expiré)',
                    'Vérifiez que votre proxy/firewall ne bloque pas les connexions WebSocket (WSS)',
                    'Testez depuis un autre réseau ou appareil pour isoler le problème',
                    'Contactez le support Kavkom avec le domain_uuid "' . $extensionTest['user_context'] . '"',
                ],
            ];
        }

        $results['recommendations'][] = [
            'severity' => 'info',
            'title' => 'Prochaines étapes',
            'steps' => [
                'Les tests sont terminés avec succès',
                'Le softphone devrait maintenant fonctionner dans l\'interface',
                'Si vous avez encore des problèmes, vérifiez la console du navigateur pour les erreurs détaillées',
                'Consultez les logs serveur: storage/logs/kavkom.log',
            ],
        ];

        return response()->json($results, 200);
    }

    /**
     * Test WebSocket connectivity via HTTP HEAD request to detect issues
     */
    protected function testWssConnectivity(string $userContext): bool
{
    try {
        $response = Http::withoutVerifying()
            ->timeout(5)
            ->withHeaders(['User-Agent' => 'Heroes-CRM-Kavkom-Test'])
            ->get('https://' . $userContext);

        // Un serveur WS répond typiquement en 400/426 à une requête HTTP
        // classique (pas d'Upgrade), ce qui prouve qu'il est bien joignable.
        // Seule une vraie erreur réseau (timeout, DNS, connexion refusée)
        // doit être considérée comme un échec.
        return true;
    } catch (\Illuminate\Http\Client\ConnectionException $exception) {
        Log::channel('kavkom')->warning('WSS connectivity check failed', [
            'user_context' => $userContext,
            'error' => $exception->getMessage(),
        ]);

        return false;
    } catch (\Throwable $exception) {
        // Autre erreur inattendue : on ne bloque pas le diagnostic pour ça,
        // mais on le trace.
        Log::channel('kavkom')->warning('WSS connectivity check error', [
            'user_context' => $userContext,
            'error' => $exception->getMessage(),
        ]);

        return true;
    }
}

    /**
     * A Kavkom "Line" (project setting > Lignes) assigned to this agent
     * holds their SIP identity. An agent has one Kavkom identity regardless
     * of which project they're calling from, so this is intentionally not
     * scoped by project — matching the previous per-user setting it replaces.
     */
    protected function getUserKavkomConfig(Request $request, ?int $projectId = null): ?array
    {
        $line = $this->getUserKavkomLine($request, $projectId);

        return $line ? (array) $line->config : null;
    }

    /**
     * The user's Kavkom line, i.e. their SIP identity and (for the inbound
     * call history) the project that DID belongs to.
     */
    protected function getUserKavkomLine(Request $request, ?int $projectId = null): ?Line
    {
        $query = Line::query()
            ->where('operator', 'kavkom')
            ->where('user_id', $request->user()->id);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $line = $query->first();

        $config = $line ? (array) $line->config : [];

        if (empty($config['api_token']) || empty($config['domain_uuid'])) {
            return null;
        }

        return $line;
    }

    /**
     * Match a caller number to a prospect. Both the local (0688753390) and
     * international (33688753390) forms of a number are tried, since the
     * PBX and the CRM do not store phone numbers the same way.
     */
    protected function findProspectByNumber(string $number, ?int $projectId = null): ?Prospect
    {
        $candidates = PhoneNumber::candidates($number);

        if (empty($candidates)) {
            return null;
        }

        $query = Prospect::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->where(function ($query) use ($candidates) {
                foreach (['phone_number', 'mobile_phone_number'] as $column) {
                    $query->orWhereIn(
                        DB::raw(PhoneNumber::digitsExpression($column)),
                        $candidates
                    );
                }
            });

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->orderBy('id')->first();
    }

    /** The inbound interaction already opened for this caller, if any. */
    protected function findOpenInboundInteraction(int $prospectId, string $number): ?Interaction
    {
        return Interaction::query()
            ->where('prospect_id', $prospectId)
            ->where('source', 'kavkom')
            ->whereIn('status', ['ringing', 'answered'])
            ->where('created_at', '>=', now()->subMinutes(30))
            ->where('data->direction', 'inbound')
            ->whereIn('number', PhoneNumber::candidates($number))
            ->latest('id')
            ->first();
    }

    protected function prospectPayload(Prospect $prospect): array
    {
        return [
            'id' => $prospect->id,
            'full_name' => $prospect->full_name,
            'first_name' => $prospect->first_name,
            'last_name' => $prospect->last_name,
            'phone_number' => $prospect->phone_number,
            'mobile_phone_number' => $prospect->mobile_phone_number,
        ];
    }

    protected function projectPayload(Prospect $prospect): ?array
    {
        $project = $prospect->project;

        return $project ? [
            'id' => $project->id,
            'slug' => $project->slug,
            'name' => $project->name,
        ] : null;
    }
}
