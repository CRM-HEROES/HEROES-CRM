<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Event;
use App\Models\Order;
use App\Models\Prospect;
use Illuminate\Database\Eloquent\Builder;

/**
 * What the trash of the CRM holds that the agents may look at and restore: the prospects, their orders and their
 * appointments. Everything is read through the models of the CRM, so the usual access rules apply (a prospect the
 * owner may not see is not in the list) and only the project of the run is reached.
 */
class TrashItems
{
    /** type => the right needed to bring it back: the one needed to delete it */
    public const TYPES = [
        'prospect' => ['route' => 'prospects', 'right' => 'prospectDelete', 'label' => 'prospect'],
        'order' => ['route' => 'orders', 'right' => 'prospectOrderDelete', 'label' => 'commande'],
        'event' => ['route' => 'events', 'right' => 'prospectEventDelete', 'label' => 'rendez-vous'],
    ];

    public static function types(): array
    {
        return array_keys(self::TYPES);
    }

    /**
     * The deleted items of a type, most recently deleted first.
     */
    public function query(AgentExecutionContext $context, string $type): Builder
    {
        $projectId = $context->project->id;

        return match ($type) {
            'prospect' => Prospect::query()->onlyTrashed()->where('prospects.project_id', $projectId),
            // As in the trash of the interface: an order or an appointment of a deleted prospect stays out
            'order' => Order::query()->onlyTrashed()
                ->whereHas('prospect', fn ($prospect) => $prospect->where('project_id', $projectId)),
            'event' => Event::query()->onlyTrashed()
                ->whereHas('calendar', fn ($calendar) => $calendar->where('project_id', $projectId)),
        };
    }

    /**
     * One deleted item, reduced to what lets a human recognise it. No phone number, no address.
     */
    public function describe(string $type, $item): array
    {
        $base = ['id' => $item->id, 'deleted_at' => optional($item->deleted_at)->toIso8601String()];

        return match ($type) {
            'prospect' => $base + ['name' => $this->prospectName($item)],
            'order' => $base + [
                'prospect_id' => $item->prospect_id,
                'prospect' => $item->prospect ? $this->prospectName($item->prospect) : null,
                'name' => $this->text($item->getAttribute('name')),
            ],
            'event' => $base + [
                'prospect_id' => $item->prospect_id,
                'name' => $this->text($item->name),
                'starts_at' => optional($item->started_at)->toIso8601String(),
            ],
        };
    }

    protected function prospectName($prospect): string
    {
        $name = trim("{$prospect->first_name} {$prospect->last_name}");

        return $name !== '' ? $name : (string) ($prospect->company_name ?: 'Sans nom');
    }

    protected function text(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? mb_substr(strip_tags($value), 0, 120) : null;
    }
}
