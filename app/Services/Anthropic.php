<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Anthropic
{
    protected string $apiKey;
    protected string $model;
    protected string $version;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key') ?: env('ANTHROPIC_API_KEY', '');
        $this->model = config('services.anthropic.model') ?: env('ANTHROPIC_MODEL', 'claude-sonnet-4-6');
        $this->version = config('services.anthropic.version') ?: env('ANTHROPIC_VERSION', '2023-06-01');
    }

    public function sendMessage(array $messages, int $maxTokens = 1024, ?string $model = null): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('Anthropic API key is not configured.');
            return null;
        }

        $model = $model ?: $this->model;

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->version,
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => $messages,
        ]);

        if ($response->failed()) {
            Log::warning('Anthropic request failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $json = $response->json();

        return data_get($json, 'content.0.text') ?: data_get($json, 'completion');
    }
}
