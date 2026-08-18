<?php

namespace App\Services\Archer;

use App\Models\Prospect;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifies/enriches a prospect's email through Dropcontact. Mirrors the
 * config-driven, Http::-facade style of App\Services\Anthropic rather than
 * the legacy raw-cURL style of App\Services\Pappers.
 */
class DropcontactClient
{
    protected ?string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.dropcontact.key');
        $this->baseUrl = rtrim(config('services.dropcontact.base_url', 'https://api.dropcontact.io'), '/');
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    /**
     * @return array{email_verified: ?bool, email: ?string, raw: array}|null
     */
    public function verify(Prospect $prospect): ?array
    {
        if (!$this->isConfigured()) {
            Log::channel('archer')->warning('Dropcontact skipped: API key not configured.', [
                'prospect_id' => $prospect->id,
            ]);
            return null;
        }

        if (blank($prospect->email)) {
            return null;
        }

        $response = Http::withHeaders([
            'X-Access-Token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/batch", [
            'data' => [[
                'email' => $prospect->email,
                'first_name' => $prospect->first_name,
                'last_name' => $prospect->last_name,
                'company' => $prospect->company_name,
            ]],
        ]);

        if ($response->failed()) {
            Log::channel('archer')->warning('Dropcontact request failed.', [
                'prospect_id' => $prospect->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $json = $response->json();
        $result = data_get($json, 'data.0', []);

        return [
            'email_verified' => filter_var(data_get($result, 'email_verified'), FILTER_VALIDATE_BOOLEAN),
            'email' => data_get($result, 'email') ?: $prospect->email,
            'raw' => $json,
        ];
    }
}
