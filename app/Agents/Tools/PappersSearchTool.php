<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\InternalApi;
use App\Models\AgentAction;
use App\Models\Prospect;

/**
 * Searches owners of real estate with Pappers and adds them to the project as prospects, as "Analyser des données grâce
 * à l'IA" of the settings does.
 *
 * It spends money (every result costs a call to a paid service) and creates prospects, some of them natural persons:
 * a human decides every single call, even when the agent is otherwise autonomous. Limits that do not depend on the
 * screen: a geographic filter is required, a page is at most 25 results, one call per turn of the agent, and a daily
 * budget of calls per project. The agent never receives what Pappers returned: it only learns how many prospects were added.
 *
 * The route of the CRM runs the command and answers nothing, whatever happens: the number added is counted here.
 */
class PappersSearchTool implements AgentToolInterface, ChecksBeforeApproval
{
    /** The filters the route of the CRM accepts (see Project\API\PappersController) */
    public const FILTERS = [
        'parcelle_cadastrale', 'code_postal', 'code_commune', 'region', 'departement', 'latitude', 'longitude', 'distance',
        'type_syndic_copropriete', 'siret_syndic_professionnel_copropriete', 'nom_copropriete', 'nombre_lots_copropriete_min', 'nombre_lots_copropriete_max',
        'siren_proprietaire', 'tranche_effectif_proprietaire_min', 'tranche_effectif_proprietaire_max', 'categorie_juridique_proprietaire', 'code_naf_proprietaire',
        'denomination_proprietaire', 'nombre_proprietaires_min', 'nombre_proprietaires_max',
        'siren_occupant', 'tranche_effectif_occupant_min', 'tranche_effectif_occupant_max', 'categorie_juridique_occupant', 'code_naf_occupant', 'denomination_occupant',
        'nature_mutation_vente', 'type_local_vente', 'date_mutation_vente_min', 'date_mutation_vente_max', 'prix_vente_min', 'prix_vente_max',
        'surface_bati_vente_min', 'surface_bati_vente_max', 'surface_terrain_vente_min', 'surface_terrain_vente_max', 'nombre_pieces_vente_min', 'nombre_pieces_vente_max',
        'annee_construction_batiment_min', 'annee_construction_batiment_max', 'nombre_logements_batiment_min', 'nombre_logements_batiment_max',
        'surface_batiment_min', 'surface_batiment_max', 'nature_batiment', 'usage_batiment',
        'classe_bilan_dpe', 'type_installation_chauffage_dpe', 'type_energie_chauffage_dpe',
        'prix_fonds_de_commerce_min', 'prix_fonds_de_commerce_max', 'date_fonds_de_commerce_min', 'date_fonds_de_commerce_max', 'code_naf_fonds_de_commerce',
        'statut_permis', 'date_autorisation_permis_min', 'date_autorisation_permis_max', 'nombre_permis_min', 'nombre_permis_max',
    ];

    /** One of these (or a point and a distance) narrows the search to a place */
    public const PLACE_FILTERS = ['parcelle_cadastrale', 'code_postal', 'code_commune', 'region', 'departement'];

    public function __construct(protected InternalApi $api)
    {
    }

    public function key(): string
    {
        return 'pappers_search';
    }

    public function description(): string
    {
        return "Recherche des propriétaires de biens immobiliers avec Pappers (service PAYANT) et les ajoute comme prospects du projet. "
            . "Il faut un filtre de lieu : code_postal, code_commune, departement, region, parcelle_cadastrale, ou latitude + longitude + distance ; les autres filtres affinent (propriétaire, vente, bâtiment, DPE, permis…). "
            . "Une page donne au plus " . $this->maxPerPage() . " résultats (par_page, 10 par défaut) et chaque résultat coûte un appel : commence petit. "
            . "Les propriétaires déjà présents (même SIREN) sont ignorés. Un seul appel par tour et " . $this->dailyCalls() . " par jour et par projet. L'utilisateur valide toujours. "
            . "Tu ne reçois pas les données : seulement le nombre de prospects ajoutés ; retrouve-les ensuite avec crm_search.";
    }

    public function inputSchema(): array
    {
        $filters = [];
        foreach (self::FILTERS as $filter) {
            $filters[$filter] = ['type' => 'string', 'maxLength' => 100];
        }

        return [
            'type' => 'object',
            'properties' => [
                'filters' => ['type' => 'object', 'properties' => $filters, 'additionalProperties' => false, 'description' => 'Filtres de Pappers (au moins un filtre de lieu).'],
                'page' => ['type' => 'integer', 'minimum' => 1, 'description' => '1 par défaut.'],
                'par_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => $this->maxPerPage(), 'description' => '10 par défaut.'],
            ],
            'required' => ['filters'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'pappers';
    }

    public function risk(): string
    {
        return self::COMMUNICATION;
    }

    public function ability(): ?string
    {
        return 'prospectImport';
    }

    /**
     * Before asking a human: the place, the size of the page, the token and what is left of the budget. The call that is
     * proposed is not counted yet (it is, once it runs), hence the "already" in the budget.
     */
    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        return $this->check($context, $input, false) ?? $this->tokenProblem();
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        if ($error = $this->check($context, $input) ?? $this->tokenProblem()) {
            return $error;
        }

        $filters = $this->filters($input);
        $before = $this->count($context);

        $response = $this->api->call($context, 'POST', 'api/pappers', $filters + [
            'page' => (int) ($input['page'] ?? 1),
            'par_page' => $this->perPage($input),
        ]);

        if (!$response->ok()) {
            return $response->toError();
        }

        $created = max(0, $this->count($context) - $before);

        return ToolResult::ok(
            [
                'prospects_added' => $created,
                'page' => (int) ($input['page'] ?? 1),
                'per_page' => $this->perPage($input),
                'note' => $created === 0
                    ? "Aucun prospect ajouté : aucun résultat, propriétaires déjà présents (même SIREN) ou erreur du service Pappers (le CRM ne la renvoie pas)."
                    : "Les prospects ajoutés se retrouvent avec crm_search (tri par date de création).",
            ],
            "Pappers : $created prospect(s) ajouté(s)",
            $created
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [
            'action' => 'Recherche Pappers (service payant) et ajout des propriétaires comme prospects',
            'filtres' => $this->filters($input),
            'page' => (int) ($input['page'] ?? 1),
            'par_page' => $this->perPage($input),
            'coût' => "jusqu'à " . $this->perPage($input) . " appels de fiche entreprise + 1 recherche",
            'appels_restants_aujourd_hui' => max(0, $this->dailyCalls() - $this->callsToday($context)),
        ];
    }

    /**
     * What stops the call before anything is spent, or null.
     */
    protected function tokenProblem(): ?ToolResult
    {
        return filled(env('PAPPERS_TOKEN'))
            ? null
            : ToolResult::error("Pappers n'est pas configuré sur le serveur (jeton absent) : rien n'a été appelé.", ToolResult::UNAVAILABLE);
    }

    /**
     * @param  bool  $running  the call is running: it is already recorded as executed and counts itself
     */
    protected function check(AgentExecutionContext $context, array $input, bool $running = true): ?ToolResult
    {
        $filters = $this->filters($input);

        if (!$filters) {
            return ToolResult::error('Aucun filtre : donne au moins un filtre de lieu.', ToolResult::VALIDATION_FAILED);
        }
        if (!$this->hasPlace($filters)) {
            return ToolResult::error(
                'Il faut un filtre de lieu (' . implode(', ', self::PLACE_FILTERS) . ', ou latitude + longitude + distance) : sans lui la recherche serait trop large et coûteuse.',
                ToolResult::VALIDATION_FAILED
            );
        }
        if (isset($input['par_page']) && (int) $input['par_page'] > $this->maxPerPage()) {
            return ToolResult::error('Au plus ' . $this->maxPerPage() . ' résultats par page.', ToolResult::LIMIT_EXCEEDED);
        }

        // The approval of this very call is already recorded when it runs: it counts itself
        if ($context->runId && $this->callsInRun($context) > ($running ? 1 : 0)) {
            return ToolResult::error("Un seul appel Pappers par tour : l'utilisateur relance l'agent pour la page suivante.", ToolResult::LIMIT_EXCEEDED);
        }
        if ($this->callsToday($context) > $this->dailyCalls() - ($running ? 0 : 1)) {
            return ToolResult::error("Budget du jour atteint : {$this->dailyCalls()} appel(s) Pappers par jour et par projet.", ToolResult::LIMIT_EXCEEDED);
        }

        return null;
    }

    /** Only the filters Pappers knows, with a value */
    protected function filters(array $input): array
    {
        return array_filter(
            array_intersect_key((array) ($input['filters'] ?? []), array_flip(self::FILTERS)),
            fn ($value) => is_scalar($value) && trim((string) $value) !== ''
        );
    }

    protected function hasPlace(array $filters): bool
    {
        foreach (self::PLACE_FILTERS as $filter) {
            if (isset($filters[$filter])) {
                return true;
            }
        }

        return isset($filters['latitude'], $filters['longitude'], $filters['distance']);
    }

    protected function perPage(array $input): int
    {
        return max(1, min($this->maxPerPage(), (int) ($input['par_page'] ?? 10)));
    }

    protected function maxPerPage(): int
    {
        return max(1, min(100, (int) config('agents.pappers_max_per_page', 25)));
    }

    protected function dailyCalls(): int
    {
        return max(1, (int) config('agents.pappers_daily_calls', 10));
    }

    protected function callsToday(AgentExecutionContext $context): int
    {
        return AgentAction::query()
            ->where('tool_key', $this->key())
            ->where('project_id', $context->project->id)
            ->where('executed_at', '>=', now()->subDay())
            ->count();
    }

    protected function callsInRun(AgentExecutionContext $context): int
    {
        return AgentAction::query()
            ->where('tool_key', $this->key())
            ->where('run_id', $context->runId)
            ->whereNotNull('executed_at')
            ->count();
    }

    /** Prospects of the project, deleted ones included: the command inserts without any scope */
    protected function count(AgentExecutionContext $context): int
    {
        return Prospect::withoutGlobalScopes()->where('project_id', $context->project->id)->count();
    }
}
