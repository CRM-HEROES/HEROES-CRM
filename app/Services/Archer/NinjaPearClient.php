<?php

namespace App\Services\Archer;

use App\Models\Prospect;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Resolves a professional profile for a prospect through NinjaPear (the
 * nubela.co product Proxycurl became after LinkedIn's lawsuit forced it to
 * shut down in 2025 — see https://nubela.co/blog/goodbye-proxycurl/).
 * Same account/API key as the old Proxycurl integration, different product:
 * NinjaPear deliberately does not scrape LinkedIn or any social network, so
 * this only confirms whether a professional profile was matched and
 * surfaces whatever public URL (personal site, X/Twitter) it found — never
 * a LinkedIn URL.
 */
class NinjaPearClient
{
    protected ?string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.ninjapear.key');
        $this->baseUrl = rtrim(config('services.ninjapear.base_url', 'https://nubela.co'), '/');
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    /**
     * @return array{profile_found: bool, external_profile_url: ?string, raw: array}|null
     */
    public function lookup(Prospect $prospect): ?array
    {
        if (!$this->isConfigured()) {
            Log::channel('archer')->warning('NinjaPear skipped: API key not configured.', [
                'prospect_id' => $prospect->id,
            ]);
            return null;
        }

        $params = $this->matchParams($prospect);
        if (!$params) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->get("{$this->baseUrl}/api/v2/employee/profile", $params);

        if ($response->status() === 404) {
            // No match found — a legitimate negative result, not a failure.
            return ['profile_found' => false, 'external_profile_url' => null, 'raw' => $response->json() ?? []];
        }

        if ($response->failed()) {
            Log::channel('archer')->warning('NinjaPear request failed.', [
                'prospect_id' => $prospect->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $json = $response->json();

        return [
            'profile_found' => filled(data_get($json, 'full_name')),
            'external_profile_url' => data_get($json, 'x_profile_url') ?: data_get($json, 'personal_website'),
            'raw' => $json,
        ];
    }

    /**
     * NinjaPear's employee/profile endpoint requires one of: a work email
     * alone, or a first name plus the employer's website. We never have a
     * verified work email going in (that's what Dropcontact is for), so
     * this always uses the name+domain combination, derived from
     * company_name/website_url.
     */
    protected function matchParams(Prospect $prospect): ?array
    {
        if (blank($prospect->first_name) || blank($prospect->website_url)) {
            return null;
        }

        return array_filter([
            'first_name' => $prospect->first_name,
            'last_name' => $prospect->last_name,
            'employer_website' => $prospect->website_url,
            'location' => $prospect->city,
        ]);
    }
}
