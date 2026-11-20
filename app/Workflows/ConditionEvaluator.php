<?php

namespace App\Workflows;

use App\Filters\Filters;
use App\Models\WorkflowExecution;
use Illuminate\Database\Eloquent\Builder;

/**
 * Conditions on the subject of a workflow (node "If", filter triggers).
 *
 * They are expressed with the filters the campaigns and the tables already
 * use (ProspectFilters, OrderFilters, EventFilters) and are evaluated in SQL.
 * A condition is never checked by loading data and filtering it in PHP.
 *
 * Format: a tree of groups. A group combines its items with AND ("and",
 * default) or OR ("or"); an item is a group, a set of filters, or a check:
 *
 *   { "op": "and", "items": [
 *       { "filters": { "withUsers": [3], "createdDaysAgo": 2 } },   // all of them
 *       { "check": "work_hours", "days": [1,2,3,4,5], "from": "08:00", "to": "17:00" },
 *       { "field": "rdv_fixe", "is": "oui" },                       // a custom field of the prospect
 *       { "field": "mailing_optout", "is_not": "oui" },
 *       { "field": "centre", "filled": true },                      // "filled": false: empty
 *       { "field": "plus_50", "is": "oui" },                        // computed: 50 years old or more
 *       { "op": "or", "items": [ { "filters": {...} }, { "filters": {...} } ] }
 *   ] }
 *
 * Checks (TimeChecks) do not depend on the subject: they are evaluated once
 * and enter the SQL query as a constant, so a whole tree is ONE query.
 *
 * The first format is still accepted: { "mode": "all" | "any", "filters": {...} }
 * ("any": one matching filter is enough). No condition at all is true.
 */
class ConditionEvaluator
{
    public const ALL = 'all';
    public const ANY = 'any';

    /**
     * What a "field" item can ask: is, is_not (a value), filled (true / false)
     */
    public const FIELD_TESTS = ['is', 'is_not', 'filled'];

    /**
     * Fields that are not stored but computed from the prospect
     */
    public const COMPUTED_FIELDS = ['plus_50'];

    public static function validSlug(mixed $slug): bool
    {
        return is_string($slug) && preg_match('/^[a-z0-9_]{1,60}$/', $slug) === 1;
    }

    /**
     * Nesting limit of the groups
     */
    public const MAX_DEPTH = 5;

    public function evaluate(WorkflowExecution $execution, array $config): bool
    {
        return $this->matches($execution->project_id, $execution->subject_type, $execution->subject_id, $config);
    }

    /**
     * Does one subject satisfy the condition?
     * One "exists" query on its primary key.
     */
    public function matches(int $projectId, string $subjectType, int $subjectId, array $config): bool
    {
        $tree = $this->tree($config);

        if (!$tree) {
            return true;
        }

        $query = WorkflowSubject::query($subjectType, $projectId)
            ->whereKey($subjectId);

        $this->constrain($query, $subjectType, $projectId, $config);

        return $query->exists();
    }

    /**
     * Restrict a query on the subjects (of the project) to the ones that
     * satisfy the condition: for a set of subjects (filter triggers) or for
     * all of them (initialisation of a filter trigger).
     */
    public function constrain(Builder $query, string $subjectType, int $projectId, array $config): void
    {
        $tree = $this->tree($config);

        if ($tree) {
            $this->apply($query, WorkflowSubject::filters($subjectType), $projectId, $tree, 'and');
        }
    }

    /**
     * The condition as a tree, null when there is none.
     */
    public function tree(array $config): ?array
    {
        if (isset($config['items'])) {
            return $config['items'] ? $config : null;
        }

        $filters = $config['filters'] ?? [];

        if (!$filters) {
            return null;
        }

        // First format: one item per filter, combined by the mode
        return [
            'op' => ($config['mode'] ?? self::ALL) === self::ANY ? 'or' : 'and',
            'items' => array_map(
                fn ($name, $value) => ['filters' => [$name => $value]],
                array_keys($filters),
                array_values($filters)
            ),
        ];
    }

    /**
     * A custom field of the prospect (its value is in the "meta" of the record)
     */
    protected function field(Builder $query, array $node): void
    {
        $slug = $node['field'];

        if (!self::validSlug($slug)) {
            throw new \InvalidArgumentException('Unknown field.');
        }

        if ($slug === 'plus_50') {
            // 50 years old or more: born at least 50 years ago (nobody without a birth date)
            $older = array_key_exists('is', $node) ? $node['is'] === 'oui' : ($node['is_not'] ?? null) === 'non';

            $older
                ? $query->whereNotNull('date_of_birth')->where('date_of_birth', '<=', now()->subYears(50)->toDateString())
                : $query->where(fn ($q) => $q->whereNull('date_of_birth')->orWhere('date_of_birth', '>', now()->subYears(50)->toDateString()));

            return;
        }

        $column = "meta->$slug";

        if (array_key_exists('filled', $node)) {
            $node['filled']
                ? $query->whereNotNull($column)->where($column, '!=', '')
                : $query->where(fn ($q) => $q->whereNull($column)->orWhere($column, ''));

            return;
        }

        if (array_key_exists('is', $node)) {
            $query->where($column, (string) $node['is']);

            return;
        }

        // is_not: a field without value is not that value either
        $query->where(fn ($q) => $q->whereNull($column)->orWhere($column, '!=', (string) ($node['is_not'] ?? '')));
    }

    protected function apply(Builder $query, Filters $filter, int $projectId, array $node, string $boolean, int $depth = 1): void
    {
        $where = $boolean === 'or' ? 'orWhere' : 'where';

        if (isset($node['items'])) {
            if ($depth > self::MAX_DEPTH) {
                throw new \InvalidArgumentException('Condition groups are nested too deeply.');
            }

            $op = ($node['op'] ?? 'and') === 'or' ? 'or' : 'and';

            $query->{$where}(function (Builder $group) use ($filter, $projectId, $node, $op, $depth) {
                foreach ($node['items'] as $item) {
                    $this->apply($group, $filter, $projectId, $item, $op, $depth + 1);
                }
            });

            return;
        }

        if (isset($node['check'])) {
            $result = TimeChecks::evaluate($node['check'], $node, $projectId, now());

            $query->{$where . 'Raw'}($result ? '1 = 1' : '1 = 0');

            return;
        }

        if (isset($node['field'])) {
            $query->{$where}(fn (Builder $group) => $this->field($group, $node));

            return;
        }

        $query->{$where}(function (Builder $group) use ($filter, $node) {
            foreach ($node['filters'] ?? [] as $name => $value) {
                $filter->{$name}($group, $value);
            }
        });
    }
}
