<?php

namespace App\Services;

use App\Models\Line;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class CloudTalkDialer
{
    private const BASE_URL = 'https://my.cloudtalk.io/api';
    private const DIALER_BASE_URL = 'https://api.cloudtalk.io/v1';

    /**
     * Sync only the supplied prospects into a new, agent-assigned parallel campaign.
     */
    public function createParallelCampaign(Line $line, Project $project, Collection $prospects): array
    {
        $config = $line->config ?: [];
        $this->validateConfig($config);

        $selected = [];
        $errors = [];
        $seenNumbers = [];
        $duplicates = 0;

        foreach ($prospects as $prospect) {
            $rawNumber = trim((string) ($prospect->mobile_phone_number ?: $prospect->phone_number));
            $number = $this->formatE164($rawNumber);

            if (!$number) {
                $errors[] = $this->prospectError($prospect, 'Le numéro est absent ou n’est pas au format E.164.');
                continue;
            }

            $numberKey = $this->numberKey($number);
            if (isset($seenNumbers[$numberKey])) {
                $duplicates++;
                $errors[] = $this->prospectError($prospect, 'Numéro en doublon dans la sélection.');
                continue;
            }

            $seenNumbers[$numberKey] = true;
            $selected[] = ['prospect' => $prospect, 'number' => $number];
        }

        if (!$selected) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Aucun numéro E.164 unique ne peut être synchronisé.',
                'synced_count' => 0,
                'duplicate_count' => $duplicates,
                'errors' => $errors,
            ];
        }

        $selectionKey = Str::upper(Str::random(8));
        $projectName = Str::limit(Str::slug((string) $project->slug), 40, '');
        $campaignName = sprintf('[HEROES-CRM] %s %s %s', $projectName ?: 'prospects', now()->format('Ymd-His'), $selectionKey);
        $tagName = sprintf('HEROES-CRM-%s-%s', $projectName ?: 'prospects', $selectionKey);
        $agentId = (string) $config['agent_id'];
        $tagId = null;
        $campaign = null;
        $synced = [];

        try {
            $tag = $this->request($line, 'PUT', '/tags/add.json', ['name' => $tagName]);
            $tagId = data_get($tag, 'responseData.data.id');
            if (!$tagId) {
                $tagId = $this->findTagId($line, $tagName);
            }
            if (!$tagId || !is_numeric($tagId)) {
                throw new RuntimeException('CloudTalk a créé le tag sans retourner son identifiant.', 502);
            }

            foreach ($selected as $item) {
                /** @var Prospect $prospect */
                $prospect = $item['prospect'];

                try {
                    $contact = $this->findOrCreateContact($line, $prospect, $item['number'], $tagName);
                    if (!$contact['tagged']) {
                        $this->request($line, 'PUT', '/contacts/addTags/' . rawurlencode($contact['id']) . '.json', [
                            'tags' => [$tagName],
                        ]);
                    }
                    $synced[] = [
                        'prospect_id' => (string) $prospect->id,
                        'contact_id' => (string) $contact['id'],
                        'number' => $item['number'],
                    ];
                } catch (RuntimeException $e) {
                    if ($this->isGlobalFailure($e)) {
                        throw $e;
                    }
                    $errors[] = $this->prospectError($prospect, $e->getMessage());
                }
            }

            if (!$synced) {
                $this->deleteTag($line, (string) $tagId);

                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'Aucun contact n’a pu être synchronisé dans CloudTalk.',
                    'synced_count' => 0,
                    'duplicate_count' => $duplicates,
                'tag_name' => $tagName,
                    'errors' => $errors,
                ];
            }

            $campaign = $this->request($line, 'POST', '/dialer/campaigns', [
                'name' => $campaignName,
                'mode' => 'parallel',
                'calling_policy' => [
                    'number_selection' => 'automatic',
                    'number_rotation_enabled' => false,
                    'parallel_calls' => 2,
                ],
            ]);
            $campaignId = (string) data_get($campaign, 'id', '');
            if ($campaignId === '') {
                throw new RuntimeException('CloudTalk n’a pas retourné l’identifiant de campagne.', 502);
            }

            $expectedContactIds = array_values(array_unique(array_column($synced, 'contact_id')));
            $campaignPath = '/dialer/campaigns/' . rawurlencode($campaignId);
            $associationsPath = '/dialer/campaigns/' . rawurlencode($campaignId) . '/associations';
            $associations = $this->request($line, 'PUT', $associationsPath, [
                'target_tag' => [(int) $tagId],
                'agent' => [(int) $agentId],
            ]);

            $this->assertAssociations($associations, (string) $tagId, $agentId);
            $this->assertAssociations(
                $this->request($line, 'GET', $associationsPath),
                (string) $tagId,
                $agentId
            );

            $campaign = $this->request($line, 'PATCH', $campaignPath, [
                'status' => 'active',
            ]);
            if (data_get($campaign, 'mode') !== 'parallel' || data_get($campaign, 'status') !== 'active') {
                throw new RuntimeException('La campagne CloudTalk n’est pas active en mode parallel.', 502);
            }

            $this->assertCampaignContacts($line, $campaignId, $expectedContactIds);
        } catch (RuntimeException $e) {
            if ($campaign && data_get($campaign, 'id')) {
                try {
                    $campaignPath = '/dialer/campaigns/' . rawurlencode((string) data_get($campaign, 'id'));
                    if (data_get($campaign, 'status') === 'active') {
                        $campaign = $this->request($line, 'PATCH', $campaignPath, ['status' => 'inactive']);
                    } else {
                        $this->request($line, 'DELETE', $campaignPath);
                        $campaign['status'] = 'deleted';
                    }
                } catch (RuntimeException $ignored) {
                    // Keep the original synchronization error.
                }
            }
            if (!$campaign && $tagId) {
                $this->deleteTag($line, (string) $tagId);
            }

            return [
                'success' => false,
                'status' => $this->httpStatus((int) $e->getCode()),
                'message' => $e->getMessage(),
                'synced_count' => count($synced),
                'duplicate_count' => $duplicates,
                'tag_name' => $tagName,
                'campaign' => $campaign && data_get($campaign, 'id') ? [
                    'id' => (string) data_get($campaign, 'id'),
                    'name' => (string) data_get($campaign, 'name', $campaignName),
                    'status' => (string) data_get($campaign, 'status', 'initializing'),
                ] : null,
                'errors' => $errors,
            ];
        }

        return [
            'success' => true,
            'campaign' => [
                'id' => (string) data_get($campaign, 'id'),
                'name' => (string) data_get($campaign, 'name', $campaignName),
                'mode' => (string) data_get($campaign, 'mode'),
                'status' => (string) data_get($campaign, 'status'),
            ],
            'synced_count' => count($synced),
            'duplicate_count' => $duplicates,
            'tag_name' => $tagName,
            'errors' => $errors,
        ];
    }

    private function findOrCreateContact(Line $line, Prospect $prospect, string $number, string $tagName): array
    {
        $contactIds = $this->findContactIdsByNumber($line, $number);
        if (count($contactIds) > 1) {
            throw new RuntimeException('Ce numéro existe dans plusieurs contacts CloudTalk; dédoublonnez-les avant la campagne.', 409);
        }

        if (!$contactIds) {
            $name = trim(implode(' ', array_filter([
                trim((string) $prospect->first_name),
                trim((string) $prospect->last_name),
            ]))) ?: $number;

            $created = $this->request($line, 'PUT', '/contacts/add.json', [
                'name' => Str::limit($name, 150, ''),
                'company' => Str::limit(trim((string) $prospect->company_name), 100, ''),
                'ContactNumber' => [['public_number' => $number]],
                'ContactsTag' => [['name' => $tagName]],
            ]);
            $id = data_get($created, 'responseData.data.id');
            if (!$id) {
                throw new RuntimeException('CloudTalk n’a pas retourné l’identifiant du contact créé.', 502);
            }

            return ['id' => (string) $id, 'tagged' => true];
        }

        $contactId = $contactIds[0];
        $details = $this->request($line, 'GET', '/contacts/show/' . rawurlencode($contactId) . '.json');
        $numbers = collect(data_get($details, 'responseData.ContactNumber', []))
            ->map(fn ($item) => trim((string) data_get($item, 'public_number', '')))
            ->filter()
            ->values();
        $selected = $numbers->first(fn ($item) => $this->numberKey($item) === $this->numberKey($number));

        if (!$selected) {
            throw new RuntimeException('Le contact CloudTalk correspondant ne contient plus ce numéro.', 409);
        }

        if ($this->numberKey((string) $numbers->first()) !== $this->numberKey($number)) {
            $ordered = collect([$number])
                ->concat($numbers->reject(fn ($item) => $this->numberKey($item) === $this->numberKey($number)))
                ->map(fn ($item) => ['public_number' => $item])
                ->values()
                ->all();

            $this->request($line, 'POST', '/contacts/edit/' . rawurlencode($contactId) . '.json', [
                'ContactNumber' => $ordered,
            ]);
        }

        return ['id' => $contactId, 'tagged' => false];
    }

    private function findContactIdsByNumber(Line $line, string $number): array
    {
        $page = 1;
        $pageCount = 1;
        $ids = [];

        do {
            $response = $this->request($line, 'GET', '/contacts/index.json', [
                'keyword' => $number,
                'limit' => 1000,
                'page' => $page,
            ]);
            $pageCount = max(1, (int) data_get($response, 'responseData.pageCount', 1));

            foreach (data_get($response, 'responseData.data', []) as $row) {
                $contactId = data_get($row, 'Contact.id');
                $candidateNumber = data_get($row, 'ContactNumber.public_number');
                if ($contactId && $candidateNumber && $this->numberKey((string) $candidateNumber) === $this->numberKey($number)) {
                    $ids[(string) $contactId] = true;
                }
            }

            $page++;
        } while ($page <= $pageCount);

        return array_keys($ids);
    }

    private function findTagId(Line $line, string $tagName): ?string
    {
        $page = 1;
        $pageCount = 1;

        do {
            $response = $this->request($line, 'GET', '/tags/index.json', [
                'limit' => 1000,
                'page' => $page,
            ]);
            $pageCount = max(1, (int) data_get($response, 'responseData.pageCount', 1));

            foreach (data_get($response, 'responseData.data', []) as $row) {
                $tag = data_get($row, 'Tag', $row);
                if ((string) data_get($tag, 'name') === $tagName && data_get($tag, 'id')) {
                    return (string) data_get($tag, 'id');
                }
            }

            $page++;
        } while ($page <= $pageCount);

        return null;
    }

    private function assertAssociations(array $associations, string $tagId, string $agentId): void
    {
        $hasTag = collect(data_get($associations, 'target_tag', []))
            ->contains(fn ($tag) => (string) data_get($tag, 'ref_id', $tag) === $tagId);
        $hasAgent = collect(data_get($associations, 'agent', []))
            ->contains(fn ($agent) => (string) data_get($agent, 'ref_id', $agent) === $agentId);

        if (!$hasTag || !$hasAgent) {
            throw new RuntimeException('La campagne CloudTalk n’est pas associée au tag et à l’agent attendus.', 502);
        }
    }

    private function assertCampaignContacts(Line $line, string $campaignId, array $expectedContactIds): void
    {
        $expectedContactIds = array_values(array_unique($expectedContactIds));

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $cursor = null;
            $actualContactIds = [];

            do {
                $params = ['limit' => 100];
                if ($cursor) {
                    $params['cursor'] = $cursor;
                }
                $response = $this->request($line, 'GET', '/dialer/campaigns/' . rawurlencode($campaignId) . '/contacts', $params);
                foreach (data_get($response, 'data', []) as $contact) {
                    if (data_get($contact, 'contact_id')) {
                        $actualContactIds[] = (string) data_get($contact, 'contact_id');
                    }
                }
                $cursor = data_get($response, 'meta.nextCursor');
            } while ($cursor);

            $actualContactIds = array_values(array_unique($actualContactIds));
            $missing = array_diff($expectedContactIds, $actualContactIds);
            $unexpected = array_diff($actualContactIds, $expectedContactIds);
            if (!$missing && !$unexpected) {
                return;
            }

            if ($attempt < 4) {
                usleep(500000);
            }
        }

        throw new RuntimeException('La campagne CloudTalk ne contient pas exactement les contacts sélectionnés.', 502);
    }

    private function request(Line $line, string $method, string $path, array $data = []): array
    {
        $config = $line->config ?: [];
        $baseUrl = str_starts_with($path, '/dialer/')
            ? self::DIALER_BASE_URL
            : self::BASE_URL;
        $url = $baseUrl . $path;

        try {
            $request = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->withBasicAuth($config['api_key_id'], $config['api_key_secret']);

            $response = match (strtoupper($method)) {
                'GET' => $request->get($url, $data),
                'PUT' => $request->put($url, $data),
                'POST' => $request->post($url, $data),
                'PATCH' => $request->patch($url, $data),
                'DELETE' => $request->delete($url, $data),
                default => throw new RuntimeException('Méthode CloudTalk non prise en charge.', 500),
            };
        } catch (ConnectionException $e) {
            throw new RuntimeException('Impossible de joindre l’API CloudTalk.', 503, $e);
        }

        $body = $response->json();
        $apiStatus = (int) data_get($body, 'responseData.status', 0);
        if (!$response->successful() || ($apiStatus && !in_array($apiStatus, [200, 201], true))) {
            $status = $apiStatus ?: $response->status();
            $message = data_get($body, 'message')
                ?: data_get($body, 'responseData.message')
                ?: data_get($body, 'responseData.data.message')
                ?: 'Erreur API CloudTalk (' . $status . ').';

            $details = data_get($body, 'subErrors')
                ?? data_get($body, 'responseData.subErrors')
                ?? data_get($body, 'errors')
                ?? data_get($body, 'responseData.errors')
                ?? data_get($body, 'details')
                ?? data_get($body, 'responseData.details');
            if ($details !== null && $details !== []) {
                $encodedDetails = is_string($details)
                    ? $details
                    : json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($encodedDetails) {
                    $message .= ' — ' . Str::limit($encodedDetails, 1000, '…');
                }
            }

            throw new RuntimeException((string) $message, $status);
        }

        return is_array($body) ? $body : [];
    }

    private function deleteTag(Line $line, string $tagId): void
    {
        try {
            $this->request($line, 'DELETE', '/tags/delete/' . rawurlencode($tagId) . '.json');
        } catch (RuntimeException $ignored) {
            // An empty tag is harmless if CloudTalk refuses cleanup.
        }
    }

    private function formatE164(string $number): ?string
    {
        $number = preg_replace('/[\s().-]+/u', '', trim($number));
        if (str_starts_with($number, '00')) {
            $number = '+' . substr($number, 2);
        }

        return preg_match('/^\+[1-9]\d{1,14}$/', $number) ? $number : null;
    }

    private function numberKey(string $number): string
    {
        return preg_replace('/\D+/', '', $number);
    }

    private function prospectError(Prospect $prospect, string $message): array
    {
        return [
            'prospect_id' => (string) $prospect->id,
            'name' => trim(implode(' ', array_filter([
                trim((string) $prospect->first_name),
                trim((string) $prospect->last_name),
            ]))),
            'message' => $message,
        ];
    }

    private function validateConfig(array $config): void
    {
        foreach (['api_key_id', 'api_key_secret'] as $field) {
            if (empty($config[$field])) {
                throw new RuntimeException('Identifiants API CloudTalk incomplets.', 422);
            }
        }

        if (empty($config['agent_id']) || !is_numeric($config['agent_id'])) {
            throw new RuntimeException('Aucun agent CloudTalk valide n’est associé à votre ligne.', 422);
        }
    }

    private function isGlobalFailure(RuntimeException $exception): bool
    {
        return in_array((int) $exception->getCode(), [401, 403, 429, 500, 502, 503], true);
    }

    private function httpStatus(int $status): int
    {
        if ($status === 401) {
            return 422;
        }

        return in_array($status, [403, 404, 406, 409, 422, 429, 500, 502, 503], true) ? $status : 500;
    }
}
