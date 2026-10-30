<?php

namespace App\Utils\Stat\Builder;

use App\Models\Project;
use App\Services\DashboardFollowups;
use App\Services\DashboardRecordings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moteur de requêtes du Dashboard Builder.
 *
 * Reçoit la configuration JSON d'un widget + les filtres globaux et renvoie des données normalisées :
 *  - charts (bar, hbar, line, multiline, pie, donut, combo, heatmap) :
 *        { kind: "series", labels: [], series: [{ label, data: [], type? }], format }
 *  - kpi    : { kind: "kpi", value, previous, change, format }
 *  - funnel : { kind: "funnel", steps: [{ label, value, rate, rate_prev }] }
 *  - table  : { kind: "table", columns: [], rows: [], total, page, per_page }
 */
class DashboardEngine
{
    public const PERIODS = [
        'today' => "Aujourd'hui",
        'yesterday' => "Hier",
        'last_7_days' => "7 derniers jours",
        'last_30_days' => "30 derniers jours",
        'last_90_days' => "90 derniers jours",
        'this_week' => "Cette semaine",
        'last_week' => "Semaine dernière",
        'this_month' => "Ce mois",
        'last_month' => "Mois dernier",
        'this_year' => "Cette année",
        'custom' => "Personnalisée",
    ];

    public const CONNECTION_STATUSES = ['Connecté', 'Absent', 'Déconnecté'];

    /** Filtres globaux supportés (clé => libellé) */
    public const GLOBAL_FILTERS = [
        'period' => "Période",
        'user' => "Utilisateur",
        'team' => "Équipe",
        'role' => "Rôle",
        'status' => "Statut",
        'source' => "Source",
    ];

    protected $sources;
    protected $userNames = null;

    public function __construct(protected Project $project)
    {
        $this->sources = Sources::all();
    }

    // ---------------------------------------------------------------------
    // Metadata
    // ---------------------------------------------------------------------

    public function meta(): array
    {
        $users = $this->project->users()
            ->select('users.id', 'users.name', 'users.last_name')
            ->orderBy('users.name')
            ->get()
            ->map(fn ($u) => ['value' => $u->id, 'label' => trim($u->name . ' ' . $u->last_name)])
            ->values();

        $roles = DB::table('roles')
            ->where('project_id', $this->project->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($r) => ['value' => $r->id, 'label' => $r->name])
            ->values();

        $teams = DB::table('groups')
            ->where('project_id', $this->project->id)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($g) => ['value' => $g->id, 'label' => $g->name])
            ->values();

        $periods = [];
        foreach (static::PERIODS as $k => $label) {
            $periods[] = ['value' => $k, 'label' => $label];
        }

        return [
            'sources' => Sources::publicCatalog(),
            'periods' => $periods,
            'global_filters' => collect(static::GLOBAL_FILTERS)
                ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])
                ->values(),
            'options' => [
                'user' => $users,
                'role' => $roles,
                'team' => $teams,
                'followup' => (new DashboardFollowups($this->project))->labels()->map(fn ($label) => [
                    'value' => $label->id,
                    'label' => $label->name,
                    'category' => $label->category->name,
                ])->values(),
                'status' => collect(static::CONNECTION_STATUSES)->map(fn ($s) => ['value' => $s, 'label' => $s])->values(),
                'source' => collect(['aircall', 'cloudtalk', 'ringover', 'telephone', 'smsbox', 'ultramsg', 'mtarget', 'brevo', 'whatsapp'])
                    ->map(fn ($s) => ['value' => $s, 'label' => ucfirst($s)])->values(),
            ],
        ];
    }

    // ---------------------------------------------------------------------
    // Entry point
    // ---------------------------------------------------------------------

    public function run(array $config, array $global = [], array $runtime = []): array
    {
        $type = $config['type'] ?? 'bar';
        $source = $config['dataSource'] ?? null;

        if ($type !== 'funnel' && (!$source || !isset($this->sources[$source]))) {
            abort(422, "Source de données inconnue.");
        }

        $period = $this->resolvePeriod($config, $global);

        switch ($type) {
            case 'kpi':
                return $this->kpi($config, $global, $period);
            case 'funnel':
                return $this->funnel($config, $global, $period);
            case 'table':
                return $this->table($config, $global, $period, $runtime);
            default:
                return $this->chart($config, $global, $period);
        }
    }

    // ---------------------------------------------------------------------
    // Period
    // ---------------------------------------------------------------------

    protected function respects(array $config, string $filter): bool
    {
        $list = $config['globalFilters'] ?? ['period', 'user', 'team', 'role', 'status', 'source'];

        return in_array($filter, $list, true);
    }

    protected function resolvePeriod(array $config, array $global): array
    {
        $p = $config['period'] ?? ['preset' => 'last_30_days'];

        if ($this->respects($config, 'period') && !empty($global['period']['preset'])) {
            $p = $global['period'];
        }

        return $this->periodRange($p);
    }

    public function periodRange(array $p): array
    {
        $now = Carbon::now();
        $preset = $p['preset'] ?? 'last_30_days';

        switch ($preset) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'yesterday':
                return [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()];
            case 'last_7_days':
                return [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()];
            case 'last_90_days':
                return [$now->copy()->subDays(89)->startOfDay(), $now->copy()->endOfDay()];
            case 'this_week':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'last_week':
                return [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()];
            case 'this_month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'last_month':
                return [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()];
            case 'this_year':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            case 'custom':
                try {
                    $from = Carbon::parse($p['from'] ?? $now->copy()->subDays(29))->startOfDay();
                    $to = Carbon::parse($p['to'] ?? $now)->endOfDay();
                    if ($to->lt($from)) {
                        [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                    }
                    return [$from, $to];
                } catch (\Throwable $e) {
                    // fallthrough
                }
            default:
                return [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()];
        }
    }

    protected function previousRange(array $range): array
    {
        [$from, $to] = $range;
        $days = max(1, $from->diffInDays($to) + 1);

        return [
            $from->copy()->subDays($days),
            $from->copy()->subSecond(),
        ];
    }

    // ---------------------------------------------------------------------
    // Users scoping from global filters
    // ---------------------------------------------------------------------

    /**
     * Retourne la liste des ids utilisateurs autorisés (null = pas de restriction).
     */
    protected function allowedUserIds(array $config, array $global): ?array
    {
        $allowed = null;
        $intersect = function (array $ids) use (&$allowed) {
            $allowed = $allowed === null ? $ids : array_values(array_intersect($allowed, $ids));
        };

        $vals = function (string $key) use ($config, $global) {
            if (!$this->respects($config, $key)) {
                return [];
            }
            $v = $global[$key] ?? [];

            return array_values(array_filter((array) $v, fn ($x) => $x !== null && $x !== ''));
        };

        if ($users = $vals('user')) {
            $intersect(array_map('intval', $users));
        }

        if ($roles = $vals('role')) {
            $intersect(
                DB::table('model_has_roles')
                    ->where('project_id', $this->project->id)
                    ->whereIn('role_id', $roles)
                    ->pluck('model_id')->map(fn ($i) => (int) $i)->all()
            );
        }

        if ($teams = $vals('team')) {
            $intersect(
                DB::table('user_group')->whereIn('group_id', $teams)
                    ->pluck('user_id')->map(fn ($i) => (int) $i)->all()
            );
        }

        return $allowed;
    }

    // ---------------------------------------------------------------------
    // Series (aggregate)
    // ---------------------------------------------------------------------

    protected function seriesDefs(array $config, array $src): array
    {
        $defs = [];

        foreach (($config['series'] ?? []) as $s) {
            $field = $s['field'] ?? 'records';
            if (!isset($src['measures'][$field])) {
                continue;
            }
            $m = $src['measures'][$field];
            $agg = in_array($s['agg'] ?? '', $m['aggs'], true) ? $s['agg'] : $m['default'];
            $defs[] = [
                'field' => $field,
                'agg' => $agg,
                'label' => !empty($s['label']) ? $s['label'] : $m['label'],
                'type' => $s['type'] ?? null,
                'color' => $s['color'] ?? null,
                'format' => $m['format'] ?? null,
            ];
        }

        if (!$defs) {
            $first = array_key_first($src['measures']);
            $m = $src['measures'][$first];
            $defs[] = ['field' => $first, 'agg' => $m['default'], 'label' => $m['label'], 'type' => null, 'color' => null, 'format' => $m['format'] ?? null];
        }

        return $defs;
    }

    protected function aggSql(array $src, array $def): string
    {
        $m = $src['measures'][$def['field']];
        $expr = $m['expr'] ?? '1';

        if ($def['agg'] === 'count' || !isset($m['expr'])) {
            return 'COUNT(*)';
        }

        $fn = strtoupper($def['agg']);

        return "ROUND({$fn}({$expr}), 2)";
    }

    /**
     * Requête agrégée sur une source "events".
     * @return array<int, array> lignes: dims => [valeurs], vals => [valeurs]
     */
    protected function queryEvents(string $key, array $dims, array $defs, array $range, array $config, array $global, array $widgetFilters = []): array
    {
        $src = $this->sources[$key];
        $date = $src['date'];

        if (!$this->tableExists($src['table'])) {
            return $dims ? [] : [['dims' => [], 'raw' => [], 'vals' => array_fill(0, count($defs), 0.0)]];
        }

        $dimExprs = [];
        foreach ($dims as $d) {
            if (!isset($src['dimensions'][$d])) {
                abort(422, "Dimension inconnue : $d");
            }
            $dimExprs[] = str_replace('{d}', $date, $src['dimensions'][$d]['expr']);
        }

        $select = [];
        foreach ($dimExprs as $i => $e) {
            $select[] = "$e AS d$i";
        }
        foreach ($defs as $i => $def) {
            $select[] = $this->aggSql($src, $def) . " AS s$i";
        }

        $sql = "SELECT " . implode(', ', $select)
            . " FROM {$src['table']} {$src['alias']} {$src['join']}"
            . " WHERE {$src['project_col']} = ?"
            . " AND $date BETWEEN ? AND ?";
        $bind = [$this->project->id, $range[0]->toDateTimeString(), $range[1]->toDateTimeString()];

        if (!empty($src['where'])) {
            $sql .= " AND {$src['where']}";
        }

        // Filtres globaux (utilisateurs / rôles / équipes)
        if (($ids = $this->allowedUserIds($config, $global)) !== null) {
            if (!$ids) {
                return [];
            }
            $sql .= " AND {$src['user_col']} IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
            array_push($bind, ...$ids);
        }

        // Filtre global "source" (le "statut" global ne concerne que le statut de connexion des utilisateurs)
        $vals = $this->respects($config, 'source') ? array_values(array_filter((array) ($global['source'] ?? []))) : [];
        if ($vals && isset($src['dimensions']['source'])) {
            $sql .= " AND {$src['dimensions']['source']['expr']} IN (" . implode(',', array_fill(0, count($vals), '?')) . ")";
            array_push($bind, ...$vals);
        }

        // Filtres du widget
        foreach ($widgetFilters as $f) {
            $dim = $f['field'] ?? null;
            if (!$dim || !isset($src['dimensions'][$dim])) {
                continue;
            }
            $expr = str_replace('{d}', $date, $src['dimensions'][$dim]['expr']);
            $val = $f['value'] ?? null;
            switch ($f['op'] ?? 'eq') {
                case 'eq':
                    $sql .= " AND $expr = ?";
                    $bind[] = $val;
                    break;
                case 'neq':
                    $sql .= " AND ($expr IS NULL OR $expr <> ?)";
                    $bind[] = $val;
                    break;
                case 'contains':
                    $sql .= " AND $expr LIKE ?";
                    $bind[] = '%' . $val . '%';
                    break;
                case 'in':
                    $list = array_values(array_filter(array_map('trim', is_array($val) ? $val : explode(',', (string) $val)), 'strlen'));
                    if ($list) {
                        $sql .= " AND $expr IN (" . implode(',', array_fill(0, count($list), '?')) . ")";
                        array_push($bind, ...$list);
                    }
                    break;
                case 'gt':
                    $sql .= " AND $expr > ?";
                    $bind[] = $val;
                    break;
                case 'lt':
                    $sql .= " AND $expr < ?";
                    $bind[] = $val;
                    break;
            }
        }

        if ($dimExprs) {
            $sql .= " GROUP BY " . implode(', ', array_map(fn ($i) => "d$i", array_keys($dimExprs)));
        }

        $sql .= " LIMIT 5000";

        $rows = $this->safe(fn () => DB::select($sql, $bind), []);

        return array_map(function ($r) use ($dims, $defs, $src) {
            $r = (array) $r;
            $dv = [];
            foreach ($dims as $i => $d) {
                $dv[] = $this->dimLabel($src['dimensions'][$d], $r["d$i"]);
            }
            $vals = [];
            foreach ($defs as $i => $def) {
                $vals[] = (float) ($r["s$i"] ?? 0);
            }

            return ['dims' => $dv, 'raw' => array_map(fn ($i) => $r["d$i"], array_keys($dims)), 'vals' => $vals];
        }, $rows);
    }

    /**
     * Exécute une requête en tolérant une table absente/inutilisable (source optionnelle).
     */
    protected function safe(callable $fn, $default)
    {
        try {
            return $fn();
        } catch (\Illuminate\Database\QueryException $e) {
            if (in_array($e->getCode(), ['42S02', '42S22'], true)) {
                return $default;
            }
            throw $e;
        }
    }

    protected function tableExists(string $table): bool
    {
        static $cache = [];

        try {
            return $cache[$table] ??= Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function dimLabel(array $dim, $value): string
    {
        if ($value === null || $value === '') {
            return "Non défini";
        }

        if (isset($dim['labels'])) {
            return $dim['labels'][(int) $value] ?? (string) $value;
        }

        if (isset($dim['suffix'])) {
            return $value . $dim['suffix'];
        }

        if (($dim['lookup'] ?? null) === 'users') {
            return $this->userName((int) $value);
        }

        return (string) $value;
    }

    protected function userName(int $id): string
    {
        if ($this->userNames === null) {
            $this->userNames = DB::table('users')
                ->join('user_project', 'user_project.user_id', '=', 'users.id')
                ->where('user_project.project_id', $this->project->id)
                ->get(['users.id', 'users.name', 'users.last_name'])
                ->mapWithKeys(fn ($u) => [$u->id => trim($u->name . ' ' . $u->last_name)])
                ->all();
        }

        return $this->userNames[$id] ?? "Utilisateur #$id";
    }

    /**
     * Complète les dimensions temporelles pour éviter les trous.
     */
    protected function fillTime(string $dim, array $rows, array $range, int $seriesCount): array
    {
        $map = [];
        foreach ($rows as $r) {
            $map[$r['dims'][0]] = $r['vals'];
        }

        $zero = array_fill(0, $seriesCount, 0.0);
        $out = [];

        if ($dim === 'day') {
            $d = $range[0]->copy()->startOfDay();
            $guard = 0;
            while ($d->lte($range[1]) && $guard++ < 800) {
                $k = $d->format('Y-m-d');
                $out[] = ['dims' => [$k], 'vals' => $map[$k] ?? $zero];
                $d->addDay();
            }
        } elseif ($dim === 'month') {
            $d = $range[0]->copy()->startOfMonth();
            $guard = 0;
            while ($d->lte($range[1]) && $guard++ < 120) {
                $k = $d->format('Y-m');
                $out[] = ['dims' => [$k], 'vals' => $map[$k] ?? $zero];
                $d->addMonthNoOverflow();
            }
        } elseif ($dim === 'hour') {
            for ($h = 0; $h < 24; $h++) {
                $out[] = ['dims' => ["{$h}h"], 'vals' => $map["{$h}h"] ?? $zero];
            }
        } elseif ($dim === 'weekday') {
            foreach (['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $name) {
                $out[] = ['dims' => [$name], 'vals' => $map[$name] ?? $zero];
            }
        } else {
            ksort($map);
            foreach ($map as $k => $v) {
                $out[] = ['dims' => [$k], 'vals' => $v];
            }
        }

        return $out;
    }

    protected function isTimeDim(?string $dim): bool
    {
        return in_array($dim, ['day', 'week', 'month', 'weekday', 'hour'], true);
    }

    // ---------------------------------------------------------------------
    // Charts
    // ---------------------------------------------------------------------

    protected function chart(array $config, array $global, array $range): array
    {
        $key = $config['dataSource'];
        $src = $this->sources[$key];
        $defs = $this->seriesDefs($config, $src);
        $type = $config['type'];

        // Les camemberts / funnels n'acceptent qu'une série
        if (in_array($type, ['pie', 'donut', 'heatmap'], true)) {
            $defs = [$defs[0]];
        }

        $dim = $config['dimension'] ?? ($src['kind'] === 'users' ? 'name' : 'day');
        if ($type === 'heatmap') {
            $dim = 'day';
        }

        $rows = $this->rows($key, $dim, $defs, $range, $config, $global);
        $rows = $this->sortLimit($rows, $config, $dim);

        $labels = array_map(fn ($r) => $r['dims'][0], $rows);
        $series = [];
        foreach ($defs as $i => $def) {
            $series[] = [
                'label' => $def['label'],
                'type' => $def['type'],
                'color' => $def['color'],
                'data' => array_map(fn ($r) => $r['vals'][$i], $rows),
            ];
        }

        // Comparaison avec la période précédente (courbes / barres, dimension temporelle)
        if (!empty($config['compare']) && in_array($type, ['line', 'multiline', 'bar', 'hbar', 'combo'], true) && $this->isTimeDim($dim) && $src['kind'] === 'events') {
            $prev = $this->rows($key, $dim, [$defs[0]], $this->previousRange($range), $config, $global, $this->previousRange($range));
            $prev = array_values($prev);
            $series[] = [
                'label' => $defs[0]['label'] . " (période précédente)",
                'type' => null,
                'color' => null,
                'dashed' => true,
                'data' => array_map(fn ($i) => $prev[$i]['vals'][0] ?? 0, array_keys($labels)),
            ];
        }

        return [
            'kind' => 'series',
            'labels' => $labels,
            'series' => $series,
            'format' => $defs[0]['format'],
            'period' => [$range[0]->toDateString(), $range[1]->toDateString()],
        ];
    }

    /**
     * Lignes [{dims:[label], vals:[...]}] pour n'importe quelle source.
     */
    protected function rows(string $key, ?string $dim, array $defs, array $range, array $config, array $global, ?array $fillRange = null): array
    {
        $src = $this->sources[$key];

        if ($src['kind'] === 'events') {
            $rows = $this->queryEvents($key, $dim ? [$dim] : [], $defs, $range, $config, $global, $config['filters'] ?? []);

            if ($dim && $this->isTimeDim($dim)) {
                return $this->fillTime($dim, $rows, $fillRange ?? $range, count($defs));
            }

            return $rows;
        }

        // Source "users" : groupement en PHP
        $users = $this->usersRows($config, $global, $range);

        return $this->groupRows($users, $dim, $defs);
    }

    protected function groupRows(array $users, ?string $dim, array $defs): array
    {
        $groups = [];
        foreach ($users as $u) {
            $g = $dim ? (string) ($u[$dim] ?? 'Non défini') : 'Total';
            $groups[$g][] = $u;
        }

        $out = [];
        foreach ($groups as $label => $items) {
            $vals = [];
            foreach ($defs as $def) {
                $vals[] = $this->aggregateValues(
                    $def['field'] === 'records' ? array_fill(0, count($items), 1) : array_map(fn ($u) => (float) ($u[$def['field']] ?? 0), $items),
                    $def['field'] === 'records' ? 'count' : $def['agg']
                );
            }
            $out[] = ['dims' => [$label], 'vals' => $vals];
        }

        return $out;
    }

    protected function aggregateValues(array $values, string $agg): float
    {
        if (!$values) {
            return 0.0;
        }

        switch ($agg) {
            case 'sum':
                return round(array_sum($values), 2);
            case 'avg':
                return round(array_sum($values) / count($values), 2);
            case 'min':
                return (float) min($values);
            case 'max':
                return (float) max($values);
            default:
                return (float) count($values);
        }
    }

    protected function sortLimit(array $rows, array $config, ?string $dim): array
    {
        $sort = $config['sort'] ?? null;
        $by = $sort['by'] ?? ($this->isTimeDim($dim) ? 'dimension' : 'value');
        $dir = ($sort['direction'] ?? ($by === 'value' ? 'desc' : 'asc')) === 'desc' ? -1 : 1;

        if (!($this->isTimeDim($dim) && !isset($sort['by']))) {
            usort($rows, function ($a, $b) use ($by, $dir) {
                return $by === 'dimension'
                    ? $dir * strnatcasecmp($a['dims'][0], $b['dims'][0])
                    : $dir * (($a['vals'][0] ?? 0) <=> ($b['vals'][0] ?? 0));
            });
        }

        $limit = (int) ($config['limit'] ?? 0);

        return $limit > 0 ? array_slice($rows, 0, $limit) : $rows;
    }

    // ---------------------------------------------------------------------
    // KPI
    // ---------------------------------------------------------------------

    protected function scalar(array $config, array $global, array $range): float
    {
        $src = $this->sources[$config['dataSource']];
        $defs = $this->seriesDefs($config, $src);
        $rows = $this->rows($config['dataSource'], null, [$defs[0]], $range, $config, $global);

        return (float) ($rows[0]['vals'][0] ?? 0);
    }

    protected function kpi(array $config, array $global, array $range): array
    {
        $src = $this->sources[$config['dataSource']];
        $defs = $this->seriesDefs($config, $src);
        $value = $this->scalar($config, $global, $range);

        $previous = null;
        $change = null;
        if (($config['comparison'] ?? 'previous_period') !== 'none') {
            $previous = $this->scalar($config, $global, $this->previousRange($range));
            $change = $previous != 0 ? round((($value - $previous) / abs($previous)) * 100, 1) : ($value > 0 ? 100.0 : 0.0);
        }

        return [
            'kind' => 'kpi',
            'value' => $value,
            'previous' => $previous,
            'change' => $change,
            'format' => $defs[0]['format'],
            'period' => [$range[0]->toDateString(), $range[1]->toDateString()],
        ];
    }

    // ---------------------------------------------------------------------
    // Funnel
    // ---------------------------------------------------------------------

    protected function funnel(array $config, array $global, array $range): array
    {
        $steps = [];
        $first = null;
        $prev = null;

        foreach (($config['steps'] ?? []) as $s) {
            $ds = $s['dataSource'] ?? null;
            if (!$ds || !isset($this->sources[$ds]) || $this->sources[$ds]['kind'] !== 'events') {
                continue;
            }
            $stepConfig = [
                'dataSource' => $ds,
                'series' => [['field' => $s['field'] ?? 'records', 'agg' => $s['agg'] ?? 'count']],
                'filters' => $s['filters'] ?? [],
                'globalFilters' => $config['globalFilters'] ?? null,
            ];
            if ($stepConfig['globalFilters'] === null) {
                unset($stepConfig['globalFilters']);
            }
            $value = $this->scalar($stepConfig, $global, $range);
            $first = $first ?? $value;

            $steps[] = [
                'label' => $s['label'] ?? $this->sources[$ds]['label'],
                'value' => $value,
                'rate' => $first ? round($value / $first * 100, 1) : 0,
                'rate_prev' => $prev !== null && $prev ? round($value / $prev * 100, 1) : 100,
            ];
            $prev = $value;
        }

        return ['kind' => 'funnel', 'steps' => $steps, 'period' => [$range[0]->toDateString(), $range[1]->toDateString()]];
    }

    // ---------------------------------------------------------------------
    // Table
    // ---------------------------------------------------------------------

    protected function table(array $config, array $global, array $range, array $runtime): array
    {
        $key = $config['dataSource'];
        $src = $this->sources[$key];

        $columns = array_values(array_filter($config['columns'] ?? [], fn ($c) => !empty($c['field'])));

        if (!$columns) {
            $columns = $src['kind'] === 'users'
                ? array_map(fn ($f) => ['field' => $f], $src['default_columns'])
                : [];
        }

        if ($src['kind'] === 'users') {
            $followupColumns = array_filter($columns, fn ($column) =>
                is_string($column['field']) && str_starts_with($column['field'], 'followup'));
            $followups = new DashboardFollowups($this->project);
            $labels = $followupColumns ? $followups->labels()->keyBy('id') : collect();
            $selected = new \Illuminate\Database\Eloquent\Collection();
            foreach ($followupColumns as $column) {
                abort_unless(preg_match('/^followup_([1-9][0-9]*)$/D', $column['field'], $match), 422, 'Choisissez un suivi pour chaque colonne Suivi.');
                $label = $labels->get($match[1]);
                abort_unless($label && !$selected->has($label->id), 422, 'Suivi inconnu, indisponible ou déjà sélectionné.');
                $selected->put($label->id, $label);
            }
            $rows = $this->usersRows($config, $global, $range);
            $fields = $src['fields'];
            if ($selected->isNotEmpty()) {
                $counts = $followups->counts($selected, array_column($rows, 'id'), $range[0]->toDateTimeString(), $range[1]->toDateTimeString());
                foreach ($selected as $label) {
                    $field = 'followup_' . $label->id;
                    $fields[$field] = ['label' => 'Suivi → ' . $label->name, 'type' => 'number', 'sortable' => true];
                    foreach ($rows as &$row) {
                        $row[$field] = $counts[$row['id']][$field] ?? 0;
                    }
                    unset($row);
                }
            }
        } else {
            $dims = [];
            $defs = [];
            foreach ($columns as $c) {
                if (isset($src['dimensions'][$c['field']])) {
                    $dims[] = $c['field'];
                } elseif (isset($src['measures'][$c['field']])) {
                    $m = $src['measures'][$c['field']];
                    $defs[] = [
                        'field' => $c['field'],
                        'agg' => in_array($c['agg'] ?? '', $m['aggs'], true) ? $c['agg'] : $m['default'],
                        'label' => $m['label'],
                        'format' => $m['format'] ?? null,
                    ];
                }
            }
            if (!$defs) {
                $first = array_key_first($src['measures']);
                $defs[] = ['field' => $first, 'agg' => $src['measures'][$first]['default'], 'label' => $src['measures'][$first]['label'], 'format' => null];
                $columns[] = ['field' => $first];
            }
            $raw = $this->queryEvents($key, $dims, $defs, $range, $config, $global, $config['filters'] ?? []);
            $rows = array_map(function ($r) use ($dims, $defs) {
                $row = [];
                foreach ($dims as $i => $d) {
                    $row[$d] = $r['dims'][$i];
                }
                foreach ($defs as $i => $def) {
                    $row[$def['field']] = $r['vals'][$i];
                }

                return $row;
            }, $raw);
            $fields = [];
            foreach ($src['dimensions'] as $dk => $d) {
                $fields[$dk] = ['label' => $d['label'], 'type' => $d['type'] ?? 'string'];
            }
            foreach ($src['measures'] as $mk => $m) {
                $fields[$mk] = ['label' => $m['label'], 'type' => 'number', 'format' => $m['format'] ?? null];
            }
        }

        // Filtres du widget (comparaison sur les valeurs finales)
        if ($src['kind'] === 'users') {
            $rows = $this->filterRows($rows, $config['filters'] ?? []);
        }

        // Filtre global "statut" (statut de connexion)
        if ($src['kind'] === 'users' && $this->respects($config, 'status') && !empty($global['status'])) {
            $st = (array) $global['status'];
            $rows = array_values(array_filter($rows, fn ($r) => in_array($r['connection_status'] ?? null, $st, true)));
        }

        // Recherche
        $search = trim((string) ($runtime['search'] ?? ''));
        if ($search !== '' && !empty($config['search'] ?? true)) {
            $rows = array_values(array_filter($rows, function ($r) use ($search) {
                foreach ($r as $k => $v) {
                    if (is_string($v) && stripos($v, $search) !== false) {
                        return true;
                    }
                }

                return false;
            }));
        }

        // Tri (runtime > config)
        $sort = $runtime['sort'] ?? $config['sorting'] ?? null;
        if (!empty($sort['field']) && isset($fields[$sort['field']])) {
            $f = $sort['field'];
            $dir = ($sort['direction'] ?? 'asc') === 'desc' ? -1 : 1;
            usort($rows, function ($a, $b) use ($f, $dir) {
                $x = $a[$f] ?? null;
                $y = $b[$f] ?? null;

                return $dir * (is_numeric($x) && is_numeric($y) ? $x <=> $y : strnatcasecmp((string) $x, (string) $y));
            });
        }

        $total = count($rows);

        // Limite + pagination
        $limit = (int) ($config['limit'] ?? 0);
        if ($limit > 0) {
            $rows = array_slice($rows, 0, $limit);
            $total = count($rows);
        }

        $paginate = !empty($config['pagination']);
        $perPage = max(1, min(100, (int) ($runtime['per_page'] ?? $config['perPage'] ?? 10)));
        $page = max(1, (int) ($runtime['page'] ?? 1));
        if ($paginate) {
            $rows = array_slice($rows, ($page - 1) * $perPage, $perPage);
        }

        $outCols = [];
        foreach ($columns as $c) {
            $f = $fields[$c['field']] ?? null;
            if (!$f) {
                continue;
            }
            $outCols[] = [
                'field' => $c['field'],
                'label' => !empty($c['label']) ? $c['label'] : $f['label'],
                'type' => $c['type'] ?? $f['type'] ?? 'string',
                'format' => $c['format'] ?? $f['format'] ?? null,
                'sortable' => $c['sortable'] ?? ($f['sortable'] ?? true),
            ];
        }

        return [
            'kind' => 'table',
            'columns' => $outCols,
            'rows' => $rows,
            'total' => $total,
            'page' => $paginate ? $page : 1,
            'per_page' => $paginate ? $perPage : $total,
            'period' => [$range[0]->toDateString(), $range[1]->toDateString()],
        ];
    }

    protected function filterRows(array $rows, array $filters): array
    {
        foreach ($filters as $f) {
            $field = $f['field'] ?? null;
            if (!$field) {
                continue;
            }
            $val = $f['value'] ?? null;
            $op = $f['op'] ?? 'eq';
            $rows = array_values(array_filter($rows, function ($r) use ($field, $val, $op) {
                $x = $r[$field] ?? null;
                switch ($op) {
                    case 'eq':
                        return (string) $x === (string) $val;
                    case 'neq':
                        return (string) $x !== (string) $val;
                    case 'contains':
                        return stripos((string) $x, (string) $val) !== false;
                    case 'in':
                        $list = array_map('trim', is_array($val) ? $val : explode(',', (string) $val));

                        return in_array((string) $x, $list, true);
                    case 'gt':
                        return is_numeric($x) && $x > (float) $val;
                    case 'lt':
                        return is_numeric($x) && $x < (float) $val;
                }

                return true;
            }));
        }

        return $rows;
    }

    // ---------------------------------------------------------------------
    // Users source
    // ---------------------------------------------------------------------

    protected function usersRows(array $config, array $global, array $range): array
    {
        $pid = $this->project->id;
        $allowed = $this->allowedUserIds($config, $global);
        if ($allowed !== null && !$allowed) {
            return [];
        }

        $users = DB::table('users')
            ->join('user_project', 'user_project.user_id', '=', 'users.id')
            ->where('user_project.project_id', $pid)
            ->when($allowed !== null, fn ($q) => $q->whereIn('users.id', $allowed))
            ->select('users.id', 'users.name', 'users.last_name', 'users.email', 'users.last_activity', 'user_project.last_activity as project_last_activity', 'user_project.prospects_count')
            ->limit(500)
            ->get();

        $ids = $users->pluck('id')->all();
        if (!$ids) {
            return [];
        }

        $roles = DB::table('model_has_roles as m')
            ->join('roles as r', 'r.id', '=', 'm.role_id')
            ->where('m.project_id', $pid)
            ->whereIn('m.model_id', $ids)
            ->get(['m.model_id', 'r.name'])
            ->groupBy('model_id')
            ->map(fn ($g) => $g->pluck('name')->implode(', '));

        $from = $range[0]->toDateTimeString();
        $to = $range[1]->toDateTimeString();

        $calls = DB::table('interactions as t')
            ->join('prospects as p', 'p.id', '=', 't.prospect_id')
            ->where('p.project_id', $pid)
            ->whereIn('t.creator_id', $ids)
            ->whereBetween(DB::raw('COALESCE(t.started_at, t.created_at)'), [$from, $to])
            ->groupBy('t.creator_id')
            ->selectRaw("t.creator_id as uid, COUNT(*) as c, COALESCE(SUM(TIMESTAMPDIFF(SECOND, t.started_at, t.ended_at)), 0) as secs, SUM(CASE WHEN t.status IN ('answered','completed','ended') THEN 1 ELSE 0 END) as ok")
            ->get()->keyBy('uid');

        $sms = $this->safe(fn () => DB::table('sms as t')
            ->join('prospects as p', 'p.id', '=', 't.prospect_id')
            ->where('p.project_id', $pid)
            ->whereIn('t.creator_id', $ids)
            ->whereBetween(DB::raw('COALESCE(t.sent_at, t.created_at)'), [$from, $to])
            ->groupBy('t.creator_id')
            ->selectRaw('t.creator_id as uid, COUNT(*) as c')
            ->pluck('c', 'uid'), collect());

        $orders = $this->safe(fn () => DB::table('orders as t')
            ->join('prospects as p', 'p.id', '=', 't.prospect_id')
            ->where('p.project_id', $pid)
            ->whereNull('t.deleted_at')
            ->whereIn('t.creator_id', $ids)
            ->whereBetween('t.created_at', [$from, $to])
            ->groupBy('t.creator_id')
            ->selectRaw('t.creator_id as uid, COUNT(*) as c')
            ->pluck('c', 'uid'), collect());

        $recordings = (new DashboardRecordings($this->project))->urls($ids, $from, $to);

        $now = Carbon::now();

        return $users->map(function ($u) use ($roles, $calls, $sms, $orders, $recordings, $now) {
            $c = $calls[$u->id] ?? null;
            $last = collect([$u->last_activity, $u->project_last_activity])->filter()->sort()->last();
            $minutes = $last ? $now->diffInMinutes(Carbon::parse($last)) : null;
            $count = $c ? (int) $c->c : 0;

            return [
                'id' => $u->id,
                'name' => trim($u->name . ' ' . $u->last_name),
                'email' => $u->email,
                'role' => $roles[$u->id] ?? '—',
                'last_login' => $last ? Carbon::parse($last)->toIso8601String() : null,
                'connection_status' => $minutes === null || $minutes > 30 ? 'Déconnecté' : ($minutes <= 5 ? 'Connecté' : 'Absent'),
                'calls_count' => $count,
                'call_duration' => $c ? round($c->secs / 60, 4) : 0,
                'sms_count' => (int) ($sms[$u->id] ?? 0),
                'orders_count' => (int) ($orders[$u->id] ?? 0),
                'prospects_count' => (int) $u->prospects_count,
                'recording' => $recordings[$u->id] ?? null,
                'performance' => $count ? round($c->ok / $count * 100) : 0,
            ];
        })->values()->all();
    }
}
