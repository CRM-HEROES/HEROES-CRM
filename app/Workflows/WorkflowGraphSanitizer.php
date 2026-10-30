<?php

namespace App\Workflows;

/**
 * Cleans a graph sent by the builder before it is stored as a draft.
 *
 * It keeps only the shape the engine and the builder know (nodes: id, type,
 * name, config, position; edges: from, to, branch), cuts the sizes, and
 * ignores anything else, so a draft can neither carry junk nor grow without
 * limit. It does NOT judge whether the workflow is correct: a draft may be
 * incomplete (that is what WorkflowValidator is for, when publishing).
 */
class WorkflowGraphSanitizer
{
    public const MAX_NODES = 100;
    public const MAX_EDGES = 300;

    public static function clean(array $graph): array
    {
        $nodes = [];

        foreach (array_slice((array) ($graph['nodes'] ?? []), 0, self::MAX_NODES) as $node) {
            if (!is_array($node) || !isset($node['id'], $node['type']) || !is_scalar($node['id']) || !is_scalar($node['type'])) {
                continue;
            }

            $position = is_array($node['position'] ?? null) ? $node['position'] : [];

            $nodes[] = [
                'id' => mb_substr((string) $node['id'], 0, 60),
                'type' => mb_substr((string) $node['type'], 0, 40),
                'name' => isset($node['name']) && is_scalar($node['name']) ? mb_substr((string) $node['name'], 0, 120) : null,
                'config' => is_array($node['config'] ?? null) ? $node['config'] : [],
                'position' => [
                    'x' => (int) ($position['x'] ?? 0),
                    'y' => (int) ($position['y'] ?? 0),
                ],
            ];
        }

        $edges = [];

        foreach (array_slice((array) ($graph['edges'] ?? []), 0, self::MAX_EDGES) as $edge) {
            if (!is_array($edge) || !isset($edge['from'], $edge['to']) || !is_scalar($edge['from']) || !is_scalar($edge['to'])) {
                continue;
            }

            $clean = ['from' => mb_substr((string) $edge['from'], 0, 60), 'to' => mb_substr((string) $edge['to'], 0, 60)];

            if (isset($edge['branch']) && is_scalar($edge['branch'])) {
                $clean['branch'] = mb_substr((string) $edge['branch'], 0, 20);
            }

            $edges[] = $clean;
        }

        return ['nodes' => $nodes, 'edges' => $edges];
    }

    /**
     * A new workflow starts with its trigger
     */
    public static function blank(): array
    {
        return [
            'nodes' => [[
                'id' => 'trigger',
                'type' => WorkflowGraph::TRIGGER,
                'name' => null,
                'config' => ['trigger' => 'prospect.created'],
                'position' => ['x' => 80, 'y' => 60],
            ]],
            'edges' => [],
        ];
    }
}
