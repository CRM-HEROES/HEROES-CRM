<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Services\ProspectHistory;
use App\Models\Prospect;

/**
 * The timeline of one prospect (calls, SMS, e-mails, appointments, orders,
 * changes), through the service the interface uses for its history cell: the
 * entries shown are the ones the owner is allowed to see there.
 *
 * Personal data stays out: no phone number, no address. Texts are cut.
 */
class ProspectHistoryTool implements AgentToolInterface
{
    public const TYPES = ['sms', 'interaction', 'message'];

    public function __construct(protected ReadGateway $reader, protected ProspectHistory $history)
    {
    }

    public function key(): string
    {
        return 'prospect_history';
    }

    public function description(): string
    {
        return "Historique d'un prospect, du plus récent au plus ancien : appels, SMS, e-mails, rendez-vous, commandes, modifications. "
            . "Utile pour analyser les derniers échanges avant de proposer une action. Filtrable par type (sms, interaction = appels, message = e-mails) et limité en nombre d'entrées.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'prospect_id' => ['type' => 'integer'],
                'type' => ['type' => 'string', 'enum' => self::TYPES, 'description' => "Un seul type d'échange (tous par défaut)."],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'description' => '20 par défaut.'],
            ],
            'required' => ['prospect_id'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'prospect';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $found = $this->reader->visibleProspectIds($context, [$input['prospect_id']], false);

        if (!$found['ids']) {
            return ToolResult::error("Prospect {$input['prospect_id']} introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
        }

        $prospect = Prospect::query()->find($found['ids'][0]);
        $this->history->append($prospect->newCollection([$prospect]), $context->project, $input['type'] ?? null);

        $entries = collect($prospect->history ?? [])->map(fn ($entry) => $this->entry(json_decode(json_encode($entry), true) ?: []));
        $entries = $this->withCallNotes($entries, $prospect->id);
        $limit = (int) ($input['limit'] ?? 20);

        $entries = $entries->sortByDesc('at')->values();

        return ToolResult::ok(
            ['prospect_id' => $prospect->id, 'total' => $entries->count(), 'entries' => $entries->take($limit)->all()],
            'Historique du prospect ' . $prospect->id,
            min($limit, $entries->count())
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    /**
     * The history service does not carry the note of a call: read it for the
     * calls shown, for this prospect only.
     */
    protected function withCallNotes($entries, int $prospectId)
    {
        $ids = $entries->where('type', 'interaction')->pluck('id')->filter()->all();

        if (!$ids) {
            return $entries;
        }

        $notes = \App\Models\Interaction::query()->where('prospect_id', $prospectId)->whereIn('id', $ids)->get(['id', 'data'])
            ->mapWithKeys(fn ($call) => [$call->id => $call->data['note'] ?? null])->filter();

        return $entries->map(function ($entry) use ($notes) {
            if (($entry['type'] ?? null) === 'interaction' && isset($notes[$entry['id']])) {
                $entry['note'] = mb_substr((string) $notes[$entry['id']], 0, 300);
            }

            return $entry;
        });
    }

    /**
     * One entry, reduced to what an analysis needs.
     */
    protected function entry(array $raw): array
    {
        $keep = ['type', 'status', 'source', 'from_user', 'direction', 'name', 'key', 'old_value', 'new_value', 'subject', 'message', 'body', 'plain_text', 'started_at', 'ended_at'];
        $entry = ['type' => $raw['type'] ?? $raw['history_type'] ?? null, 'id' => $raw['id'] ?? null, 'at' => $raw['created_at'] ?? $raw['started_at'] ?? null];

        foreach ($keep as $key) {
            if (isset($raw[$key]) && is_scalar($raw[$key]) && $key !== 'type') {
                $value = $raw[$key];
                $entry[$key] = is_string($value) ? mb_substr(strip_tags($value), 0, 300) : $value;
            }
        }

        if (isset($raw['data']['note'])) {
            $entry['note'] = mb_substr((string) $raw['data']['note'], 0, 300);
        }

        if (isset($raw['creator']['name'])) {
            $entry['by'] = $raw['creator']['name'];
        }

        return $entry;
    }
}
