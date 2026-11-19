<?php

namespace App\Agents\Coverage;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;

/**
 * What the CRM exposes (the routes of its API, by group) against what the AI agents know (help sheets)
 * and can do (tools). The classification of every group is kept in resources/agent-coverage.json.
 */
class RouteInventory
{
    public const STATUS_COVERED = 'couvert';
    public const STATUS_READ_ONLY = 'lecture seule';
    public const STATUS_NO_ACTION = 'sans action';
    public const STATUS_NO_SHEET = 'sans fiche';
    public const STATUS_EXCLUDED = 'exclu';

    /**
     * The groups of routes of the API, with how many routes read and how many write.
     *
     * @return array<string, array{routes: int, read: int, write: int}>
     */
    public function groups(): array
    {
        $groups = [];

        foreach (Route::getRoutes() as $route) {
            /** @var LaravelRoute $route */
            if (!str_starts_with($route->uri(), 'api/')) {
                continue;
            }

            $methods = array_values(array_diff($route->methods(), ['HEAD']));
            $reads = $methods === ['GET'];
            $key = $this->group($route->uri());

            $groups[$key] ??= ['routes' => 0, 'read' => 0, 'write' => 0];
            $groups[$key]['routes']++;
            $groups[$key][$reads ? 'read' : 'write']++;
        }

        ksort($groups);

        return $groups;
    }

    public function group(string $uri): string
    {
        if (preg_match('#^api/project/\{project\}/([a-z0-9-]+)#', $uri, $matches)) {
            return 'project/' . $matches[1];
        }

        if (preg_match('#^api/user/\{user\}/([a-z0-9-]+)#', $uri, $matches)) {
            return 'user/' . $matches[1];
        }

        return preg_match('#^api/([a-z0-9-]+)#', $uri, $matches) ? $matches[1] : $uri;
    }

    /**
     * The classification written by hand.
     *
     * @return array<string, array{label: string, sheets: string[], read_tools: string[], write_tools: string[], excluded?: string, priority?: string}>
     */
    public function classification(): array
    {
        $file = resource_path('agent-coverage.json');

        return json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR)['groups'] ?? [];
    }

    /**
     * One row per group of routes, with its status.
     *
     * @return array<int, array<string, mixed>>
     */
    public function matrix(): array
    {
        $classification = $this->classification();
        $rows = [];

        foreach ($this->groups() as $group => $counts) {
            $entry = $classification[$group] ?? null;

            $rows[] = [
                'group' => $group,
                'label' => $entry['label'] ?? '(non classé)',
                'routes' => $counts['routes'],
                'read' => $counts['read'],
                'write' => $counts['write'],
                'sheets' => $entry['sheets'] ?? [],
                'read_tools' => $entry['read_tools'] ?? [],
                'write_tools' => $entry['write_tools'] ?? [],
                'excluded' => $entry['excluded'] ?? null,
                'priority' => $entry['priority'] ?? null,
                'status' => $entry ? $this->status($entry, $counts) : null,
            ];
        }

        return $rows;
    }

    protected function status(array $entry, array $counts): string
    {
        if (!empty($entry['excluded'])) {
            return self::STATUS_EXCLUDED;
        }

        if (empty($entry['sheets'])) {
            return self::STATUS_NO_SHEET;
        }

        if ($counts['write'] === 0) {
            return self::STATUS_READ_ONLY;
        }

        return empty($entry['write_tools']) ? self::STATUS_NO_ACTION : self::STATUS_COVERED;
    }
}
