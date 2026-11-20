<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Commission;

/**
 * The commissions of the project: for each user, product and action, the type and the amount. It is the pay of colleagues: only for
 * who manages commissions (the right of the screen), and read only; the amounts are changed by a person in the CRM.
 */
class CommissionsTool implements AgentToolInterface
{
    public function key(): string
    {
        return 'commissions';
    }

    public function description(): string
    {
        return "Commissions du projet : pour chaque utilisateur, produit et action de devis, le type (pourcentage ou montant), la valeur et si elle est toutes taxes comprises. "
            . "Données de rémunération : réservé à qui gère les commissions. Lecture seule : les montants se changent dans le CRM par une personne.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'user_id' => ['type' => 'integer', 'description' => 'Limiter à un utilisateur.'],
                'product_id' => ['type' => 'integer', 'description' => 'Limiter à un produit.'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'commission';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return 'projectUserCommissionAdd';
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $projectId = $context->project->id;

        $query = Commission::query()
            ->whereHas('product', fn ($product) => $product->where('project_id', $projectId))
            ->whereHas('action', fn ($action) => $action->where('project_id', $projectId))
            ->with(['user:id,name,last_name', 'product:id,name', 'action:id,name']);

        isset($input['user_id']) && $query->where('user_id', (int) $input['user_id']);
        isset($input['product_id']) && $query->where('product_id', (int) $input['product_id']);

        $total = (clone $query)->count();
        $commissions = $query->orderBy('user_id')->limit(100)->get();

        return ToolResult::ok(
            [
                'total' => $total,
                'commissions' => $commissions->map(fn ($c) => [
                    'user' => $c->user ? trim($c->user->name . ' ' . $c->user->last_name) : null,
                    'product' => mb_substr(strip_tags((string) $c->product?->name), 0, 120),
                    'action' => mb_substr(strip_tags((string) $c->action?->name), 0, 120),
                    'type' => $c->type,
                    'value' => $c->value,
                    'including_tax' => (bool) $c->including_tax,
                ])->all(),
            ],
            'Commissions du projet',
            $commissions->count()
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
