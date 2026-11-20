<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Vehicle;
use App\Models\VehiclePosition;

/**
 * The vehicles of the project and, for who manages them, where one was last seen.
 *
 * A position is the movement of a person: the list (name, who drives it, when it last reported) is open, the positions need the
 * right to update vehicles, only the last five are given and the coordinates are rounded to about 100 m. Nothing is written.
 */
class VehiclesTool implements AgentToolInterface
{
    public const POSITIONS = 5;

    public function key(): string
    {
        return 'vehicles';
    }

    public function description(): string
    {
        return "Véhicules du projet. view=list : nom, utilisateur affecté, date de la dernière position (sans coordonnées). view=position avec vehicle_id : les " . self::POSITIONS
            . " dernières positions (latitude et longitude arrondies à environ 100 m), réservé à qui gère les véhicules. Lecture seule : les véhicules se créent et se modifient dans l'interface.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'view' => ['type' => 'string', 'enum' => ['list', 'position']],
                'vehicle_id' => ['type' => 'integer', 'description' => 'view=position : le véhicule.'],
            ],
            'required' => ['view'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'vehicle';
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
        $query = Vehicle::query()->where('project_id', $context->project->id);

        if ($input['view'] === 'list') {
            $vehicles = $query->with('user:id,name,last_name')->orderBy('name')->limit(100)->get();

            return ToolResult::ok(
                ['vehicles' => $vehicles->map(fn ($vehicle) => [
                    'id' => $vehicle->id,
                    'name' => mb_substr(strip_tags((string) $vehicle->name), 0, 120),
                    'user' => $vehicle->user ? trim($vehicle->user->name . ' ' . $vehicle->user->last_name) : null,
                    'last_position_at' => $vehicle->position_at ? \Carbon\Carbon::parse($vehicle->position_at)->toIso8601String() : null,
                ])->all()],
                'Véhicules du projet',
                $vehicles->count()
            );
        }

        if (!$context->owner->can('projectVehicleUpdate', $context->project)) {
            return ToolResult::error("Les positions d'un véhicule demandent le droit de modifier les véhicules, que le propriétaire de l'agent n'a pas dans ce projet.", ToolResult::PERMISSION_DENIED);
        }

        $vehicle = (int) ($input['vehicle_id'] ?? 0) > 0 ? $query->find((int) $input['vehicle_id']) : null;
        if (!$vehicle) {
            return ToolResult::error('Véhicule introuvable dans ce projet.', ToolResult::NOT_FOUND);
        }

        $positions = VehiclePosition::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('user_id', $vehicle->user_id)
            ->orderByDesc('created_at')
            ->limit(self::POSITIONS)
            ->get(['latitude', 'longitude', 'created_at']);

        return ToolResult::ok(
            [
                'vehicle' => ['id' => $vehicle->id, 'name' => mb_substr(strip_tags((string) $vehicle->name), 0, 120)],
                'positions' => $positions->map(fn ($p) => [
                    'latitude' => round((float) $p->latitude, 3),
                    'longitude' => round((float) $p->longitude, 3),
                    'at' => optional($p->created_at)->toIso8601String(),
                ])->all(),
                'note' => 'Coordonnées arrondies à environ 100 m.',
            ],
            "Positions du véhicule {$vehicle->id}",
            $positions->count()
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
