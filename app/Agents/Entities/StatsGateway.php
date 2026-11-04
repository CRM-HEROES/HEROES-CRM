<?php

namespace App\Agents\Entities;

use App\Agents\AgentExecutionContext;
use App\Utils\Stat\Builder\DashboardEngine;
use App\Utils\Stat\Builder\Sources;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Statistics for the agents, on the engine of the dashboard builder
 * (DashboardEngine), so that an agent counts exactly what the dashboards count.
 *
 * The engine is bound to the project but does not know who is asking. What is
 * added here:
 *  - the users whose figures are shown are those the owner can see: an
 *    administrator sees the whole team, the others themselves and the users
 *    they may assign (the engine treats an EMPTY list of users as "no
 *    restriction", so that case is answered here, never passed to it);
 *  - measures, aggregations and dimensions are checked against the catalogue
 *    (the engine silently falls back to its first measure on an unknown one,
 *    which would be a wrong answer given with confidence);
 *  - periods are bounded to one year;
 *  - the arithmetic of a comparison is done here, in PHP, not by the model.
 */
class StatsGateway
{
    /** Metrics of a person, for the team tables */
    public const TEAM_METRICS = [
        'calls_count' => ['label' => "Nombre d'appels", 'unit' => 'appels'],
        'call_duration' => ['label' => "Durée totale des appels", 'unit' => 'minutes'],
        'sms_count' => ['label' => 'Nombre de SMS', 'unit' => 'SMS'],
        'orders_count' => ['label' => 'Commandes créées', 'unit' => 'commandes'],
        'performance' => ['label' => "Taux d'appels aboutis", 'unit' => '%'],
        'prospects_count' => ['label' => 'Prospects assignés (état actuel, indépendant de la période)', 'unit' => 'prospects'],
    ];

    /** Metrics that make sense to compare between two periods */
    public const COMPARABLE = ['calls_count', 'call_duration', 'sms_count', 'orders_count', 'performance'];

    protected const TIME_DIMENSIONS = ['day', 'week', 'month', 'weekday', 'hour'];

    protected const OPERATORS = ['eq', 'neq', 'contains', 'in', 'gt', 'lt'];

    /**
     * The users the owner may look at (null: the whole team).
     *
     * @return int[]|null
     */
    public function visibleUserIds(AgentExecutionContext $context): ?array
    {
        $user = $context->owner;

        if ($user->is_super_admin || $user->is_project_admin) {
            return null;
        }

        $assignable = DB::table('user_assignable_user')
            ->where('user_id', $user->id)
            ->where('project_id', $context->project->id)
            ->pluck('assignable_user_id')
            ->all();

        $members = DB::table('user_project')
            ->where('project_id', $context->project->id)
            ->whereIn('user_id', array_merge([$user->id], $assignable))
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique($members));
    }

    /**
     * The catalogue an agent can aggregate on.
     */
    public function catalogue(): array
    {
        $sources = [];

        foreach (Sources::all() as $key => $source) {
            $sources[$key] = [
                'label' => $source['label'],
                'dimensions' => array_map(fn ($dimension) => $dimension['label'], $source['dimensions']),
                'measures' => array_map(fn ($measure) => ['label' => $measure['label'], 'aggregations' => $measure['aggs'], 'default' => $measure['default']], $source['measures']),
            ];
        }

        return [
            'sources' => $sources,
            'periods' => collect(DashboardEngine::PERIODS)->except('custom')->all() + ['custom' => 'Personnalisée (from et to, 366 jours au plus)'],
            'team_metrics' => self::TEAM_METRICS,
            'notes' => [
                "Les chiffres par utilisateur ne concernent que les utilisateurs que le propriétaire de l'agent peut voir.",
                "Une durée est en minutes ; la performance est le pourcentage d'appels aboutis.",
            ],
        ];
    }

    /**
     * One figure, or one figure per value of a dimension.
     *
     * @param  array{source: string, measure?: string, agg?: string, dimension?: string, period?: array, user_ids?: int[], filters?: array, limit?: int}  $params
     */
    public function aggregate(AgentExecutionContext $context, array $params): array
    {
        $sources = Sources::all();
        $key = $params['source'] ?? null;

        if (!$key || !isset($sources[$key])) {
            throw new AgentReadException('Source inconnue. Sources possibles : ' . implode(', ', array_keys($sources)) . '.');
        }

        $source = $sources[$key];
        $measure = $params['measure'] ?? array_key_first($source['measures']);

        if (!isset($source['measures'][$measure])) {
            throw new AgentReadException("Mesure inconnue pour $key : $measure. Mesures possibles : " . implode(', ', array_keys($source['measures'])) . '.');
        }

        $agg = $params['agg'] ?? $source['measures'][$measure]['default'];

        if (!in_array($agg, $source['measures'][$measure]['aggs'], true)) {
            throw new AgentReadException("Agrégation impossible pour $key.$measure : $agg. Possibles : " . implode(', ', $source['measures'][$measure]['aggs']) . '.');
        }

        $dimension = $params['dimension'] ?? null;

        if ($dimension !== null && !isset($source['dimensions'][$dimension])) {
            throw new AgentReadException("Dimension inconnue pour $key : $dimension. Dimensions possibles : " . implode(', ', array_keys($source['dimensions'])) . '.');
        }

        $filters = $this->widgetFilters($source, $key, $params['filters'] ?? []);
        $period = $this->period($params['period'] ?? null);
        $users = $this->scopeUsers($context, $params['user_ids'] ?? null);
        $range = $this->range($context, $period);

        $base = [
            'source' => $key,
            'measure' => $measure,
            'aggregation' => $agg,
            'dimension' => $dimension,
            'period' => $range,
        ];

        // Nobody the owner may look at: an empty answer, never "everything"
        if ($users === []) {
            return $base + ['rows' => [], 'total' => 0, 'note' => "Aucun utilisateur visible pour cette demande."];
        }

        $config = [
            'dataSource' => $key,
            'series' => [['field' => $measure, 'agg' => $agg]],
            'period' => $period,
            'filters' => $filters,
        ];

        if ($dimension === null) {
            $result = $this->run($context, $config + ['type' => 'kpi', 'comparison' => 'none'], $users);

            return $base + ['rows' => [['label' => 'Total', 'value' => $result['value']]], 'total' => in_array($agg, ['count', 'sum'], true) ? $result['value'] : null];
        }

        $limit = max(1, min((int) ($params['limit'] ?? 50), 120));
        $config += ['type' => 'bar', 'dimension' => $dimension, 'limit' => $limit];

        // A time dimension stays chronological, the others are ranked
        if (!in_array($dimension, self::TIME_DIMENSIONS, true)) {
            $config['sort'] = ['by' => 'value', 'direction' => 'desc'];
        }

        $result = $this->run($context, $config, $users);
        $rows = [];

        foreach ($result['labels'] as $index => $label) {
            $row = ['label' => $label, 'value' => $result['series'][0]['data'][$index] ?? 0];

            // A model that is left to work out the day of the week from a date gets it wrong
            if ($dimension === 'day' && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $label)) {
                $row['weekday'] = Carbon::parse($label)->locale('fr')->isoFormat('dddd');
            }

            $rows[] = $row;
        }

        return $base + [
            'rows' => $rows,
            'total' => in_array($agg, ['count', 'sum'], true) ? round(array_sum(array_column($rows, 'value')), 2) : null,
            'truncated' => count($rows) >= $limit,
        ];
    }

    /**
     * The figures of each person of the team over a period.
     *
     * @return array{period: array, rows: array<int, array>}
     */
    public function team(AgentExecutionContext $context, ?array $period, ?array $userIds = null): array
    {
        $period = $this->period($period);
        $users = $this->scopeUsers($context, $userIds);
        $range = $this->range($context, $period);

        if ($users === []) {
            return ['period' => $range, 'rows' => []];
        }

        $result = $this->run($context, [
            'type' => 'table',
            'dataSource' => 'users',
            'columns' => array_map(fn ($field) => ['field' => $field], array_merge(['name', 'role', 'connection_status'], array_keys(self::TEAM_METRICS))),
            'period' => $period,
            'limit' => 0,
        ], $users);

        $rows = array_map(function ($row) {
            // Only what the agent needs: no e-mail, no link to a recording
            $kept = [
                'user_id' => (int) $row['id'],
                'name' => $row['name'],
                'role' => $row['role'],
                'connection_status' => $row['connection_status'],
            ];

            foreach (array_keys(self::TEAM_METRICS) as $metric) {
                $kept[$metric] = round((float) ($row[$metric] ?? 0), 2);
            }

            return $kept;
        }, $result['rows']);

        return ['period' => $range, 'rows' => $rows];
    }

    /**
     * Two periods side by side, with the differences computed here.
     *
     * @param  string[]|null  $metrics
     */
    public function compare(AgentExecutionContext $context, ?array $period, ?array $compareTo, ?array $metrics = null, ?array $userIds = null): array
    {
        $metrics = $metrics ?: self::COMPARABLE;
        $unknown = array_diff($metrics, self::COMPARABLE);

        if ($unknown) {
            throw new AgentReadException('Indicateur non comparable : ' . implode(', ', $unknown) . '. Possibles : ' . implode(', ', self::COMPARABLE) . '.');
        }

        $current = $this->team($context, $period, $userIds);
        $previous = $this->team($context, $compareTo, $userIds);

        if ($current['period'] === $previous['period']) {
            throw new AgentReadException('Les deux périodes sont identiques : il n\'y a rien à comparer.');
        }

        $before = collect($previous['rows'])->keyBy('user_id');
        $people = [];
        $summary = [];

        foreach ($current['rows'] as $row) {
            $old = $before->get($row['user_id']);
            $person = ['user_id' => $row['user_id'], 'name' => $row['name'], 'metrics' => []];

            foreach ($metrics as $metric) {
                $now = (float) $row[$metric];
                $then = (float) ($old[$metric] ?? 0);
                $delta = round($now - $then, 2);

                $person['metrics'][$metric] = [
                    'current' => $now,
                    'previous' => $then,
                    'delta' => $delta,
                    // Nothing to divide by when the previous figure was zero
                    'delta_percent' => $then != 0.0 ? round($delta / abs($then) * 100, 1) : null,
                    'trend' => $delta > 0 ? 'improved' : ($delta < 0 ? 'declined' : 'stable'),
                ];
            }

            $people[] = $person;
        }

        foreach ($metrics as $metric) {
            $deltas = collect($people)->map(fn ($p) => ['name' => $p['name'], 'delta' => $p['metrics'][$metric]['delta']]);
            $best = $deltas->sortByDesc('delta')->first();
            $worst = $deltas->sortBy('delta')->first();

            $summary[$metric] = [
                'label' => self::TEAM_METRICS[$metric]['label'],
                'unit' => self::TEAM_METRICS[$metric]['unit'],
                'total_current' => round(collect($people)->sum(fn ($p) => $p['metrics'][$metric]['current']), 2),
                'total_previous' => round(collect($people)->sum(fn ($p) => $p['metrics'][$metric]['previous']), 2),
                'improved' => collect($people)->where("metrics.$metric.trend", 'improved')->count(),
                'declined' => collect($people)->where("metrics.$metric.trend", 'declined')->count(),
                'stable' => collect($people)->where("metrics.$metric.trend", 'stable')->count(),
                'biggest_improvement' => $best && $best['delta'] > 0 ? $best : null,
                'biggest_decline' => $worst && $worst['delta'] < 0 ? $worst : null,
            ];
        }

        return [
            'period' => $current['period'],
            'compared_to' => $previous['period'],
            'note' => "delta = période - comparée ; pour tous ces indicateurs, une hausse est une amélioration.",
            'summary' => $summary,
            'people' => $people,
        ];
    }

    // Helpers

    /**
     * Users to count: the requested ones among the ones the owner may see.
     * An empty array means nobody (never "everyone").
     *
     * @return int[]|null null: no restriction
     */
    protected function scopeUsers(AgentExecutionContext $context, ?array $requested): ?array
    {
        $visible = $this->visibleUserIds($context);

        if ($requested === null || $requested === []) {
            return $visible;
        }

        $requested = array_values(array_unique(array_map('intval', $requested)));

        return $visible === null ? $requested : array_values(array_intersect($requested, $visible));
    }

    /**
     * @return array{preset: string, from?: string, to?: string}
     */
    protected function period(?array $period): array
    {
        $preset = $period['preset'] ?? 'last_30_days';

        if (!isset(DashboardEngine::PERIODS[$preset])) {
            throw new AgentReadException("Période inconnue : $preset. Périodes possibles : " . implode(', ', array_keys(DashboardEngine::PERIODS)) . '.');
        }

        if ($preset !== 'custom') {
            return ['preset' => $preset];
        }

        try {
            // Carbon reads an empty string as "now": the dates must be given
            if (empty($period['from']) || empty($period['to'])) {
                throw new \InvalidArgumentException('Missing date.');
            }

            $from = Carbon::parse($period['from']);
            $to = Carbon::parse($period['to']);
        } catch (\Throwable) {
            throw new AgentReadException('Une période personnalisée demande from et to au format AAAA-MM-JJ.');
        }

        if ($to->lt($from)) {
            throw new AgentReadException('La fin de la période est avant son début.');
        }

        if ($from->diffInDays($to) > 365) {
            throw new AgentReadException('Une période personnalisée ne peut pas dépasser 366 jours.');
        }

        return ['preset' => 'custom', 'from' => $from->toDateString(), 'to' => $to->toDateString()];
    }

    /**
     * The dates a period covers, to show them to the model.
     *
     * @return array{from: string, to: string}
     */
    protected function range(AgentExecutionContext $context, array $period): array
    {
        [$from, $to] = (new DashboardEngine($context->project))->periodRange($period);

        return ['from' => $from->toDateString(), 'to' => $to->toDateString()];
    }

    protected function widgetFilters(array $source, string $key, array $filters): array
    {
        if (!$filters) {
            return [];
        }

        if ($source['kind'] !== 'events') {
            throw new AgentReadException("Les filtres ne sont pas disponibles pour la source $key.");
        }

        $checked = [];

        foreach ($filters as $filter) {
            $field = $filter['field'] ?? null;
            $operator = $filter['op'] ?? 'eq';

            if (!isset($source['dimensions'][$field])) {
                throw new AgentReadException("Filtre impossible sur \"$field\" pour $key. Champs possibles : " . implode(', ', array_keys($source['dimensions'])) . '.');
            }

            if (!in_array($operator, self::OPERATORS, true)) {
                throw new AgentReadException("Opérateur inconnu : $operator. Possibles : " . implode(', ', self::OPERATORS) . '.');
            }

            $checked[] = ['field' => $field, 'op' => $operator, 'value' => $filter['value'] ?? null];
        }

        return $checked;
    }

    /**
     * Runs the engine; its refusals (HTTP 422) become readable errors.
     *
     * @param  int[]|null  $users
     */
    protected function run(AgentExecutionContext $context, array $config, ?array $users): array
    {
        try {
            return (new DashboardEngine($context->project))->run($config, $users === null ? [] : ['user' => $users]);
        } catch (HttpException|HttpResponseException $e) {
            throw new AgentReadException($e instanceof HttpException && $e->getMessage() ? $e->getMessage() : 'Demande de statistiques refusée.');
        }
    }
}
