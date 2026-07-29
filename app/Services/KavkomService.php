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

                // Backoff exponentiel : 200ms, 400ms
                usleep(pow(2, $attempt) * 100000);
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

                usleep(pow(2, $attempt) * 100000);
            }
        }

        throw $lastException;
    }

    /**
     * Determine if an error is transient (worth retrying).
     *
     * FIX : strpos() est sensible à la casse. Le vrai message cURL est
     * "Connection timed out after 10000 milliseconds" (minuscules), donc
     * l'ancien test sur "Operation timed out" / "Timeout" ne matchait
     * jamais et désactivait le retry sur TOUS les timeouts.
     */
    protected function isTransientError(\Throwable $exception): bool
    {
        $message = $exception->getMessage();

        // cURL error 28 / timeouts réseau (insensible à la casse)
        if (stripos($message, 'timed out') !== false || stripos($message, 'timeout') !== false) {
            return true;
        }

        // Erreurs cURL de connexion (DNS, connexion refusée, etc.)
        if (stripos($message, 'cURL error 6') !== false   // Couldn't resolve host
            || stripos($message, 'cURL error 7') !== false  // Failed to connect
        ) {
            return true;
        }

        // HTTP 5xx (bornes de mots pour éviter les faux positifs, ex: un
        // request_uuid qui contiendrait "500")
        if (preg_match('/\b(500|502|503|504)\b/', $message)) {
            return true;
        }

        // Rate limiting
        if (preg_match('/\b429\b/', $message)) {
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

    public function originateCall(string $apiToken, string $domainUuid, string $src, string $destination, array $options = []): array
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

        // FIX : normalisation du numéro de destination. Kavkom peut échouer
        // silencieusement (ou raccrocher immédiatement le leg destination)
        // si le numéro contient des espaces/points/tirets, ex: "06 18 41 66 33".
        $destination = $this->normalizePhoneNumber($destination);

        $payload = array_merge([
            'domain_uuid' => $domainUuid,
            'src' => $src,
            'destination' => $destination,
        ], $options);

        Log::channel('kavkom')->info('Kavkom originateCall payload envoyé', $payload);

        try {
            $response = $this->makePostRequest($apiToken, '/api/pbx/v1/active_call/call', $payload);

            Log::channel('kavkom')->info('Kavkom originateCall réponse complète', [
                'body' => $response->json(),
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

    /**
     * Retire espaces, points, tirets et parenthèses. Ne fait PAS de
     * conversion E.164 complète (pas d'indicatif pays deviné) pour rester
     * sûr avec des numéros déjà internationaux (+33...) ou courts (901).
     */
    protected function normalizePhoneNumber(string $number): string
    {
        return preg_replace('/[\s.\-()]/', '', trim($number));
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