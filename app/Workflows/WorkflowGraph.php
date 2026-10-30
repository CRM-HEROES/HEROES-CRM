<?php

namespace App\Workflows;

/**
 * Read access to a published graph:
 *
 *  { "nodes": [{ "id": "n1", "type": "trigger", "config": {...} }, ...],
 *    "edges": [{ "from": "n1", "to": "n2", "branch": "yes" }, ...] }
 *
 * The "branch" of an edge is only used for the outputs of a condition.
 */
class WorkflowGraph
{
    public const TRIGGER = 'trigger';
    public const CONDITION = 'condition';
    public const ACTION = 'action';
    public const WAIT = 'wait';
    // "Wait until the prospect answers / an appointment is booked / ...": branches yes (event) / no (timeout)
    public const WAIT_EVENT = 'wait_event';
    public const END = 'end';

    public const TYPES = [self::TRIGGER, self::CONDITION, self::ACTION, self::WAIT, self::WAIT_EVENT, self::END];

    public const YES = 'yes';
    public const NO = 'no';

    protected array $nodes = [];
    protected array $edges = [];

    public function __construct(array $graph)
    {
        foreach ($graph['nodes'] ?? [] as $node) {
            if (isset($node['id'])) {
                $this->nodes[$node['id']] = $node;
            }
        }

        foreach ($graph['edges'] ?? [] as $edge) {
            $this->edges[$edge['from'] ?? ''][] = $edge;
        }
    }

    /**
     * All the nodes, by id
     */
    public function nodes(): array
    {
        return $this->nodes;
    }

    /**
     * Outgoing edges of a node
     */
    public function edgesFrom(string $nodeId): array
    {
        return $this->edges[$nodeId] ?? [];
    }

    /**
     * Node by id
     */
    public function node(?string $id): ?array
    {
        return $id === null ? null : ($this->nodes[$id] ?? null);
    }

    /**
     * The entry node
     */
    public function triggerNode(): ?array
    {
        foreach ($this->nodes as $node) {
            if (($node['type'] ?? null) === self::TRIGGER) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Id of the node that follows $nodeId, or null when the path ends there.
     * For a condition, $branch selects the edge ("yes" / "no").
     */
    public function next(string $nodeId, ?string $branch = null): ?string
    {
        foreach ($this->edgesFrom($nodeId) as $edge) {
            if (($edge['branch'] ?? null) === $branch) {
                return $edge['to'] ?? null;
            }
        }

        return null;
    }
}
