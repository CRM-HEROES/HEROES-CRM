<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiLiveService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiVersion;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY', '');
        $this->model = config('services.gemini.live_model') ?: env('GEMINI_LIVE_MODEL', 'models/gemini-2.5-flash-native-audio-preview-09-2025');
        $this->apiVersion = config('services.gemini.api_version') ?: env('GEMINI_API_VERSION', 'v1alpha');
    }

    /**
     * Create a short-lived token the browser uses to open a WebSocket
     * directly to the Gemini Live API. The token is locked to the
     * configured model (bidiGenerateContentSetup + fieldMask) so a leaked
     * token cannot be used to call a different, potentially costlier model.
     */
    public function createEphemeralToken(): array
    {
        if (empty($this->apiKey)) {
            Log::warning('Gemini API key is not configured.');
            return [
                'success' => false,
                'message' => "Clé API Gemini non configurée (GEMINI_API_KEY manquant dans l'environnement).",
            ];
        }

        $now = now();

        $response = Http::withHeaders([
            'x-goog-api-key' => $this->apiKey,
            'content-type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/{$this->apiVersion}/auth_tokens", [
            'uses' => 1,
            'expireTime' => $now->clone()->addMinutes(30)->toIso8601ZuluString(),
            'newSessionExpireTime' => $now->clone()->addMinute()->toIso8601ZuluString(),
            'bidiGenerateContentSetup' => [
                'model' => $this->model,
            ],
            'fieldMask' => 'model',
        ]);

        if ($response->failed()) {
            Log::warning('Gemini ephemeral token request failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => "Impossible d'obtenir un jeton Gemini Live (voir logs serveur).",
            ];
        }

        $token = $response->json('name');

        if (empty($token)) {
            Log::warning('Gemini ephemeral token response missing token name.', [
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Réponse Gemini inattendue : jeton manquant.',
            ];
        }

        return [
            'success' => true,
            'token' => $token,
            'model' => $this->model,
            'expires_at' => $now->clone()->addMinutes(30)->toIso8601ZuluString(),
        ];
    }
}
