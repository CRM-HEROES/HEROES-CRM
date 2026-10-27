<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ClaudeApiKeyValidator
{
    public function check(string $apiKey): array
    {
        if (trim($apiKey) === '' || preg_match('/[\r\n]/', $apiKey)) {
            return ['valid' => false, 'status' => 'invalid'];
        }

        try {
            // Check authentication/model access without generating a message.
            $response = Http::acceptJson()
                ->withHeaders(['x-api-key' => $apiKey, 'anthropic-version' => '2023-06-01'])
                ->connectTimeout(5)->timeout(10)->withoutRedirecting()
                ->get('https://api.anthropic.com/v1/models', ['limit' => 1]);
        } catch (ConnectionException $e) {
            return ['valid' => null, 'status' => 'verification_unavailable'];
        }

        if ($response->status() === 401) {
            return ['valid' => false, 'status' => 'invalid'];
        }
        if ($response->status() === 403) {
            return ['valid' => false, 'status' => 'forbidden'];
        }
        if ($response->status() === 429) {
            return ['valid' => null, 'status' => 'rate_limited'];
        }
        if ($response->status() === 400) {
            return ['valid' => null, 'status' => 'configuration_error'];
        }
        if ($response->status() === 200) {
            $models = $response->json('data');
            if ($models === []) {
                return ['valid' => false, 'status' => 'no_models'];
            }
            if (is_array($models) && is_string(data_get($models, '0.id')) && filled(data_get($models, '0.id'))) {
                return ['valid' => true, 'status' => 'valid'];
            }
        }

        return ['valid' => null, 'status' => 'verification_unavailable'];
    }
}
