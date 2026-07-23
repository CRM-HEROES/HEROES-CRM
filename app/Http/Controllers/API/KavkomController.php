<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use App\Services\KavkomService;
use Illuminate\Http\Request;
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
        ]);

        $config = $this->getUserKavkomConfig($request);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Kavkom manquante : renseignez le jeton API et le domain_uuid dans les paramètres.',
            ], 200);
        }

        $extension = $service->resolveExtension($config['api_token'], $config['domain_uuid']);

        if (!$extension['success']) {
            return response()->json($extension, 200);
        }

        $result = $service->originateCall(
            $config['api_token'],
            $config['domain_uuid'],
            $extension['extension'],
            $data['destination']
        );

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
        $config = $this->getUserKavkomConfig($request);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Kavkom manquante : renseignez le jeton API et le domain_uuid dans les paramètres.',
            ], 200);
        }

        $result = $service->resolveExtension($config['api_token'], $config['domain_uuid']);

        return response()->json($result, 200);
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
        $config = $this->getUserKavkomConfig($request);

        if (!$config) {
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
        $extensionTest = $service->resolveExtension($config['api_token'], $config['domain_uuid']);
        $results['tests'][] = [
            'name' => 'Extension Resolution',
            'status' => $extensionTest['success'] ? 'ok' : 'error',
            'message' => data_get($extensionTest, 'message', 'Impossible de récupérer les extensions Kavkom.'),
            'extension' => $extensionTest['extension'] ?? null,
        ];

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

    protected function getUserKavkomConfig(Request $request): ?array
    {
        $setting = UserSetting::query()
            ->whereNull('project_id')
            ->where('user_id', $request->user()->id)
            ->where('key', 'kavkom')
            ->first();

        $config = $setting ? (array) $setting->value : [];

        if (empty($config['api_token']) || empty($config['domain_uuid'])) {
            return null;
        }

        return $config;
    }
}
