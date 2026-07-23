<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class KavkomService
{
    protected const BASE_URL = 'https://api.kavkom.com';

    public function testConnection(string $apiToken, string $domainUuid): array
    {
        if (empty($apiToken)) {
            return [
                'success' => false,
                'message' => 'Le jeton API Kavkom est requis.',
            ];
        }

        if (empty($domainUuid)) {
            return [
                'success' => false,
                'message' => 'Le domain_uuid est requis.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'X-API-TOKEN' => $apiToken,
                'Accept' => 'application/json',
            ])->timeout(10)->get(self::BASE_URL . '/api/pbx/v1/extension/list', [
                'domain_uuid' => $domainUuid,
                'limit' => 1,
            ]);

            if ($response->successful() && data_get($response->json(), 'success') === true) {
                return [
                    'success' => true,
                    'message' => 'Connexion Kavkom réussie. Le jeton et le domain_uuid sont valides.',
                    'details' => [
                        'extensionsCount' => is_array(data_get($response->json(), 'data')) ? count($response->json()['data']) : 0,
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => data_get($response->json(), 'message') ?: 'Réponse inattendue de l\'API Kavkom.',
            ];
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'message' => $this->buildErrorMessage($exception),
            ];
        }
    }

    /**
     * Kavkom requires the "src" of a call to be an extension already
     * registered on the domain (a phone/softphone already logged into
     * that extension) — an arbitrary external phone number is rejected.
     * We auto-detect the first enabled extension of the domain.
     */
    public function resolveExtension(string $apiToken, string $domainUuid): array
    {
        if (empty($apiToken) || empty($domainUuid)) {
            return [
                'success' => false,
                'message' => 'Le jeton API Kavkom et le domain_uuid sont requis.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'X-API-TOKEN' => $apiToken,
                'Accept' => 'application/json',
            ])->timeout(10)->get(self::BASE_URL . '/api/pbx/v1/extension/list', [
                'domain_uuid' => $domainUuid,
                'limit' => 50,
            ]);

            if (!$response->successful() || data_get($response->json(), 'success') !== true) {
                return [
                    'success' => false,
                    'message' => data_get($response->json(), 'message') ?: 'Impossible de récupérer les extensions Kavkom.',
                ];
            }

            $extensions = (array) data_get($response->json(), 'data', []);

            $enabled = collect($extensions)->first(
                fn ($item) => filter_var(data_get($item, 'enabled'), FILTER_VALIDATE_BOOLEAN)
            );

            $extension = $enabled ?: ($extensions[0] ?? null);

            if (!$extension || empty(data_get($extension, 'extension'))) {
                return [
                    'success' => false,
                    'message' => "Aucune extension Kavkom trouvée sur ce domaine. Créez une extension dans Kavkom pour pouvoir passer des appels.",
                ];
            }

            return [
                'success' => true,
                'extension' => (string) data_get($extension, 'extension'),
            ];
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'message' => $this->buildErrorMessage($exception),
            ];
        }
    }

    public function originateCall(string $apiToken, string $domainUuid, string $src, string $destination): array
    {
        if (empty($apiToken) || empty($domainUuid)) {
            return [
                'success' => false,
                'message' => 'Le jeton API Kavkom et le domain_uuid sont requis.',
            ];
        }

        if (empty($src)) {
            return [
                'success' => false,
                'message' => "Aucune extension Kavkom n'a pu être déterminée pour lancer l'appel.",
            ];
        }

        if (empty($destination)) {
            return [
                'success' => false,
                'message' => 'Le numéro à appeler est requis.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'X-API-TOKEN' => $apiToken,
                'Accept' => 'application/json',
            ])->timeout(10)->post(self::BASE_URL . '/api/pbx/v1/active_call/call', [
                'domain_uuid' => $domainUuid,
                'src' => $src,
                'destination' => $destination,
            ]);

            if ($response->successful() && data_get($response->json(), 'success') === true) {
                return [
                    'success' => true,
                    'message' => "L'extension Kavkom {$src} va sonner, elle sera ensuite reliée au {$destination}.",
                    'call_uuid' => data_get($response->json(), 'call_uuid'),
                ];
            }

            return [
                'success' => false,
                'message' => data_get($response->json(), 'message') ?: "Impossible de déclencher l'appel Kavkom.",
            ];
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'message' => $this->buildErrorMessage($exception),
            ];
        }
    }

    protected function buildErrorMessage(\Throwable $exception): string
    {
        if (!method_exists($exception, 'getResponse')) {
            return 'Impossible de joindre l\'API Kavkom (' . $exception->getMessage() . ').';
        }

        $response = $exception->getResponse();

        if ($response instanceof \GuzzleHttp\Psr7\Response) {
            $status = $response->getStatusCode();
            $body = (string) $response->getBody();
            $data = json_decode($body, true) ?: [];

            if (!empty($data['message'])) {
                return 'Erreur Kavkom (HTTP ' . $status . ') : ' . $data['message'];
            }

            return 'Erreur Kavkom (HTTP ' . $status . ').';
        }

        return 'Impossible de joindre l\'API Kavkom (' . $exception->getMessage() . ').';
    }
}
