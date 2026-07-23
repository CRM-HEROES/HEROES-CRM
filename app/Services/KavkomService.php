<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KavkomService
{
    protected const BASE_URL = 'https://api.kavkom.com';
    protected const TIMEOUT_SECONDS = 20;
    protected const MAX_RETRIES = 2;

    /**
     * Make a GET request to Kavkom API with automatic retry on transient errors.
     */
    protected function makeGetRequest(string $apiToken, string $endpoint, array $query = []): \Illuminate\Http\Client\Response
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt <= self::MAX_RETRIES) {
            try {
                $startTime = microtime(true);

                $response = Http::withHeaders([
                    'X-API-TOKEN' => $apiToken,
                    'Accept' => 'application/json',
                ])->timeout(self::TIMEOUT_SECONDS)->get(self::BASE_URL . $endpoint, $query);

                $duration = round((microtime(true) - $startTime) * 1000);
                $statusCode = $response->status();

                Log::channel('kavkom')->info('Kavkom API GET request succeeded', [
                    'endpoint' => $endpoint,
                    'status' => $statusCode,
                    'duration_ms' => $duration,
                    'attempt' => $attempt + 1,
                ]);

                return $response;
            } catch (\Throwable $exception) {
                $lastException = $exception;
                $attempt++;

                // Only retry on transient errors (timeouts, 5xx, 429)
                $shouldRetry = $this->isTransientError($exception);

                Log::channel('kavkom')->warning('Kavkom API GET request failed', [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt,
                    'max_retries' => self::MAX_RETRIES,
                    'error' => $exception->getMessage(),
                    'will_retry' => $shouldRetry && $attempt <= self::MAX_RETRIES,
                ]);

                if (!$shouldRetry || $attempt > self::MAX_RETRIES) {
                    throw $exception;
                }

                // Exponential backoff: 100ms, 300ms
                usleep(pow(2, $attempt - 1) * 100000);
            }
        }

        throw $lastException;
    }

    /**
     * Make a POST request to Kavkom API with automatic retry on transient errors.
     */
    protected function makePostRequest(string $apiToken, string $endpoint, array $data): \Illuminate\Http\Client\Response
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt <= self::MAX_RETRIES) {
            try {
                $startTime = microtime(true);

                $response = Http::withHeaders([
                    'X-API-TOKEN' => $apiToken,
                    'Accept' => 'application/json',
                ])->timeout(self::TIMEOUT_SECONDS)->post(self::BASE_URL . $endpoint, $data);

                $duration = round((microtime(true) - $startTime) * 1000);
                $statusCode = $response->status();

                Log::channel('kavkom')->info('Kavkom API POST request succeeded', [
                    'endpoint' => $endpoint,
                    'status' => $statusCode,
                    'duration_ms' => $duration,
                    'attempt' => $attempt + 1,
                ]);

                return $response;
            } catch (\Throwable $exception) {
                $lastException = $exception;
                $attempt++;

                // Only retry on transient errors
                $shouldRetry = $this->isTransientError($exception);

                Log::channel('kavkom')->warning('Kavkom API POST request failed', [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt,
                    'max_retries' => self::MAX_RETRIES,
                    'error' => $exception->getMessage(),
                    'will_retry' => $shouldRetry && $attempt <= self::MAX_RETRIES,
                ]);

                if (!$shouldRetry || $attempt > self::MAX_RETRIES) {
                    throw $exception;
                }

                // Exponential backoff: 100ms, 300ms
                usleep(pow(2, $attempt - 1) * 100000);
            }
        }

        throw $lastException;
    }

    /**
     * Determine if an error is transient (worth retrying).
     */
    protected function isTransientError(\Throwable $exception): bool
    {
        $message = $exception->getMessage();

        // cURL timeout errors
        if (strpos($message, 'Operation timed out') !== false || strpos($message, 'Timeout') !== false) {
            return true;
        }

        // HTTP 5xx errors (server errors)
        if (strpos($message, '500') !== false || strpos($message, '502') !== false || 
            strpos($message, '503') !== false || strpos($message, '504') !== false) {
            return true;
        }

        // Rate limiting
        if (strpos($message, '429') !== false) {
            return true;
        }

        return false;
    }

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
            $response = $this->makeGetRequest($apiToken, '/api/pbx/v1/extension/list', [
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
            $response = $this->makeGetRequest($apiToken, '/api/pbx/v1/extension/list', [
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
                'password' => (string) data_get($extension, 'password'),
                'user_context' => (string) data_get($extension, 'user_context'),
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
            $response = $this->makePostRequest($apiToken, '/api/pbx/v1/active_call/call', [
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

            // Extract more details from the error response
            $details = [];

            if (!empty($data['message'])) {
                $details[] = $data['message'];
            }

            if (!empty($data['request_uuid'])) {
                $details[] = 'Request UUID: ' . $data['request_uuid'];
            }

            $errorDetail = implode('. ', $details) ?: 'Pas de détail';

            return 'Erreur Kavkom (HTTP ' . $status . ') : ' . $errorDetail;
        }

        return 'Impossible de joindre l\'API Kavkom (' . $exception->getMessage() . ').';
    }
}
