<?php

namespace App\Services\Archer;

use App\Models\Prospect;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Looks up a prospect's LinkedIn profile through Proxycurl, used both as a
 * phone-verification signal (Proxycurl returns validated phone numbers on
 * the profile when available) and to store the LinkedIn URL itself.
 */
class ProxycurlClient
{
    protected ?string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.proxycurl.key');
        $this->baseUrl = rtrim(config('services.proxycurl.base_url', 'https://nubela.co/proxycurl/api'), '/');
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    /**
     * @return array{linkedin_url: ?string, phone_verified: ?bool, raw: array}|null
     */
    public function lookup(Prospect $prospect): ?array
    {
        if (!$this->isConfigured()) {
            Log::channel('archer')->warning('Proxycurl skipped: API key not configured.', [
                'prospect_id' => $prospect->id,
            ]);
            return null;
        }

        if (blank($prospect->email) && blank($prospect->first_name)) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->get("{$this->baseUrl}/v2/search/person", array_filter([
            'first_name' => $prospect->first_name,
            'last_name' => $prospect->last_name,
            'company' => $prospect->company_name,
            'email' => $prospect->email,
        ]));

        if ($response->failed()) {
            Log::channel('archer')->warning('Proxycurl request failed.', [
                'prospect_id' => $prospect->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $json = $response->json();
        $result = data_get($json, 'results.0.profile', []);

        if (empty($result)) {
            return null;
        }

        return [
            'linkedin_url' => data_get($json, 'results.0.linkedin_profile_url'),
            'phone_verified' => filled(data_get($result, 'phone_numbers')),
            'raw' => $json,
        ];
    }
}
