<?php

namespace App\Services\Archer;

use App\Models\Prospect;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifies/enriches a prospect's email through Dropcontact. The API is
 * asynchronous: POST /v1/enrich/all queues the contact and returns a
 * request_id, then GET /v1/enrich/all/{request_id} must be polled until
 * Dropcontact reports success. Since this only ever runs from the nightly
 * ArcherEnrichProspect queue job (not a user-facing request), polling
 * inline for a few seconds is acceptable — see ArcherEnrichProspect::$timeout.
 */
class DropcontactClient
{
    protected ?string $apiKey;
    protected string $baseUrl;

    protected int $pollAttempts = 4;
    protected int $pollDelaySeconds = 15;

    public function __construct()
    {
        $this->apiKey = config('services.dropcontact.key');
        $this->baseUrl = rtrim(config('services.dropcontact.base_url', 'https://api.dropcontact.com'), '/');
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    /**
     * @return array{email_verified: ?bool, email: ?string, phone: ?string, raw: array}|null
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

        $requestId = $this->submit($prospect);
        if (!$requestId) {
            return null;
        }

        return $this->poll($prospect, $requestId);
    }

    protected function submit(Prospect $prospect): ?string
    {
        $response = Http::withHeaders([
            'X-Access-Token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/v1/enrich/all", [
            'data' => [[
                'email' => $prospect->email,
                'first_name' => $prospect->first_name,
                'last_name' => $prospect->last_name,
                'company' => $prospect->company_name,
            ]],
            'siren' => false,
            'language' => 'fr',
        ]);

        if ($response->failed() || !$response->json('success')) {
            Log::channel('archer')->warning('Dropcontact submit failed.', [
                'prospect_id' => $prospect->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json('request_id');
    }

    protected function poll(Prospect $prospect, string $requestId): ?array
    {
        for ($attempt = 1; $attempt <= $this->pollAttempts; $attempt++) {
            sleep($this->pollDelaySeconds);

            $response = Http::withHeaders([
                'X-Access-Token' => $this->apiKey,
            ])->get("{$this->baseUrl}/v1/enrich/all/{$requestId}");

            if ($response->failed()) {
                Log::channel('archer')->warning('Dropcontact poll failed.', [
                    'prospect_id' => $prospect->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $json = $response->json();
            if ($json['success'] ?? false) {
                return $this->parseResult($prospect, $json);
            }
        }

        Log::channel('archer')->warning('Dropcontact result not ready in time, skipping this run.', [
            'prospect_id' => $prospect->id,
            'request_id' => $requestId,
        ]);
        return null;
    }

    protected function parseResult(Prospect $prospect, array $json): array
    {
        $contact = data_get($json, 'data.0', []);
        $bestEmail = collect(data_get($contact, 'email', []))
            ->first(fn ($entry) => !str_contains(strtolower((string) data_get($entry, 'qualification')), 'invalid'));

        return [
            'email_verified' => $bestEmail !== null,
            'email' => data_get($bestEmail, 'email') ?: $prospect->email,
            'phone' => data_get($contact, 'phone') ?: data_get($contact, 'mobile_phone'),
            'raw' => $json,
        ];
    }
}
