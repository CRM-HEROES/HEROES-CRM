<?php

namespace App\Workflows;

use App\Workflows\Actions\ActionRegistry;
use App\Workflows\Actions\ConfigurableAction;
use App\Workflows\Nodes\WaitEventNode;
use App\Workflows\Nodes\WaitNode;

/**
 * Checks a graph before it is published or activated, and explains
 * what is wrong in plain language (one message per problem).
 *
 * What it guarantees to the engine: exactly one known trigger, valid
 * edges, every node reachable from the trigger (no orphan), no cycle
 * (so an execution always ends), usable node configurations.
 *
 * With a project, the environment is checked too: the operators used by the
 * communication actions must still be configured in that project, the
 * discussion channels must exist, the {{variables}} must be known (see
 * ConfigurableAction). Without it, only the structure is.
 */
class WorkflowValidator
{
    public function __construct(protected ActionRegistry $actions)
    {
    }

    /**
     * @param  int|null  $projectId  project the workflow belongs to
     * @return string[] the problems found (empty: the graph is valid)
     */
    public function validate(array $graph, ?int $projectId = null): array
    {
        return array_column($this->diagnose($graph, $projectId), 'message');
    }

    /**
     * The same problems, each with the step it is about (null: the workflow as
     * a whole), so that the builder can lead the user to it.
     *
     * @return array<int, array{message: string, node: ?string}>
     */
    public function diagnose(array $graph, ?int $projectId = null): array
    {
        $nodes = $graph['nodes'] ?? null;
        $edges = $graph['edges'] ?? [];

        if (!is_array($nodes) || !$nodes) {
            return [['message' => 'Le workflow est vide : ajoutez un déclencheur.', 'node' => null]];
        }

        $errors = [];
        $owners = [];
        $byId = [];

        foreach ($nodes as $node) {
            $id = $node['id'] ?? null;

            if (!is_string($id) || $id === '' || strlen($id) > 60) {
                $errors[] = 'Une étape n\'a pas d\'identifiant valide.';
                continue;
            }

            if (isset($byId[$id])) {
                $errors[] = "L'identifiant d'étape \"$id\" est utilisé plusieurs fois.";
                continue;
            }

            if (!in_array($node['type'] ?? null, WorkflowGraph::TYPES, true)) {
                $errors[] = "L'étape \"{$this->label($node)}\" a un type inconnu.";
                continue;
            }

            $byId[$id] = $node;
        }

        $triggers = array_filter($byId, fn ($node) => $node['type'] === WorkflowGraph::TRIGGER);

        if (count($triggers) !== 1) {
            $errors[] = 'Le workflow doit avoir exactement un déclencheur.';
        }

        $subjectType = null;

        foreach ($triggers as $trigger) {
            $type = $trigger['config']['trigger'] ?? null;

            if (!in_array($type, config('workflows.triggers'), true)) {
                $errors[] = 'Le déclencheur choisi n\'existe pas.';
            } else {
                $subjectType = WorkflowSubject::fromTrigger($type);
                $found = $this->validateTrigger($type, $trigger['config'], $subjectType);
                $errors = array_merge($errors, $found);

                foreach ($found as $message) {
                    $owners[$message] = $trigger['id'];
                }
            }
        }

        $errors = array_merge($errors, $this->validateEdges($byId, $edges));

        // Reachability and cycles only make sense on a sound structure
        if (!$errors) {
            $errors = array_merge($errors, $this->validateShape($byId, $edges, array_key_first($triggers)));
        }

        foreach ($byId as $node) {
            $found = $this->validateNode($node, $subjectType, $projectId);
            $errors = array_merge($errors, $found);

            foreach ($found as $message) {
                $owners[$message] = $node['id'];
            }
        }

        return array_map(
            fn ($message) => ['message' => $message, 'node' => $owners[$message] ?? $this->mentionedNode($message, array_keys($byId))],
            array_values(array_unique($errors))
        );
    }

    protected function validateEdges(array $byId, array $edges): array
    {
        $errors = [];
        $outgoing = [];

        foreach ($edges as $edge) {
            $from = $edge['from'] ?? null;
            $to = $edge['to'] ?? null;

            if (!isset($byId[$from]) || !isset($byId[$to])) {
                $errors[] = 'Une liaison pointe vers une étape qui n\'existe pas.';
                continue;
            }

            if ($byId[$to]['type'] === WorkflowGraph::TRIGGER) {
                $errors[] = 'Aucune étape ne peut mener au déclencheur.';
            }

            $branch = $edge['branch'] ?? null;

            if ($byId[$from]['type'] === WorkflowGraph::CONDITION) {
                if (!in_array($branch, [WorkflowGraph::YES, WorkflowGraph::NO], true)) {
                    $errors[] = "La condition \"{$this->label($byId[$from])}\" doit avoir une sortie OUI et une sortie NON.";
                }
            } elseif ($byId[$from]['type'] === WorkflowGraph::WAIT_EVENT) {
                if (!in_array($branch, [WorkflowGraph::YES, WorkflowGraph::NO], true)) {
                    $errors[] = "L'attente \"{$this->label($byId[$from])}\" doit avoir une sortie OUI (l'événement a eu lieu) et une sortie NON (le délai est dépassé).";
                }
            } elseif ($branch !== null) {
                $errors[] = "L'étape \"{$this->label($byId[$from])}\" ne peut pas avoir de sortie OUI / NON.";
            }

            if ($byId[$from]['type'] === WorkflowGraph::END) {
                $errors[] = 'Une étape "Terminer" ne peut pas avoir de suite.';
            }

            $outgoing[$from][$branch ?? ''] = ($outgoing[$from][$branch ?? ''] ?? 0) + 1;
        }

        foreach ($outgoing as $from => $branches) {
            foreach ($branches as $count) {
                if ($count > 1) {
                    $errors[] = "L'étape \"{$this->label($byId[$from])}\" a plusieurs suites sur la même sortie.";
                }
            }
        }

        return $errors;
    }

    protected function validateShape(array $byId, array $edges, ?string $triggerId): array
    {
        if ($triggerId === null) {
            return [];
        }

        $graph = new WorkflowGraph(['nodes' => array_values($byId), 'edges' => $edges]);
        $errors = [];

        // Orphans: nodes that cannot be reached from the trigger
        $reached = [];
        $stack = [$triggerId];

        while ($stack) {
            $id = array_pop($stack);

            if (isset($reached[$id])) {
                continue;
            }

            $reached[$id] = true;

            foreach ($graph->edgesFrom($id) as $edge) {
                $stack[] = $edge['to'];
            }
        }

        foreach ($byId as $id => $node) {
            if (!isset($reached[$id])) {
                $errors[] = "L'étape \"{$this->label($node)}\" n'est reliée à aucune autre : connectez-la ou supprimez-la.";
            }
        }

        // Cycles: an execution must always end
        $state = [];
        $hasCycle = function (string $id) use (&$hasCycle, &$state, $graph): bool {
            $state[$id] = 1;

            foreach ($graph->edgesFrom($id) as $edge) {
                $to = $edge['to'];

                if (($state[$to] ?? 0) === 1 || (($state[$to] ?? 0) === 0 && $hasCycle($to))) {
                    return true;
                }
            }

            $state[$id] = 2;

            return false;
        };

        if ($hasCycle($triggerId)) {
            $errors[] = 'Le workflow contient une boucle : une étape ramène à une étape précédente.';
        }

        return $errors;
    }

    protected function validateNode(array $node, ?string $subjectType, ?int $projectId = null): array
    {
        $config = $node['config'] ?? [];
        $label = $this->label($node);

        switch ($node['type']) {
            case WorkflowGraph::WAIT:
                return $this->validateWait($config, $label);

            case WorkflowGraph::WAIT_EVENT:
                if ($subjectType !== null && $subjectType !== WorkflowSubject::PROSPECT) {
                    return ["L'attente \"$label\" (événement) n'est disponible que pour les workflows qui portent sur un prospect."];
                }

                if (!in_array($config['event'] ?? null, WaitEventNode::EVENTS, true)) {
                    return ["L'attente \"$label\" n'a pas d'événement valide à attendre."];
                }

                $seconds = WaitNode::seconds($config);

                if ($seconds <= 0) {
                    return ["L'attente \"$label\" doit avoir un délai maximum (au moins une minute) : un workflow n'attend jamais indéfiniment."];
                }

                if ($seconds > config('workflows.max_wait_days') * 86400) {
                    return ["L'attente \"$label\" ne peut pas dépasser " . config('workflows.max_wait_days') . ' jours.'];
                }
                break;

            case WorkflowGraph::ACTION:
                $action = $config['action'] ?? '';

                if ($action === '') {
                    return ["L'action \"$label\" n'est pas configurée."];
                }

                if ($subjectType && !$this->actions->has($action, $subjectType)) {
                    return ["L'action \"$label\" n'est pas disponible pour ce type de déclencheur."];
                }

                $handler = $this->actions->get($action);

                if ($handler instanceof ConfigurableAction) {
                    return $handler->validateConfig($config, $projectId, $label);
                }
                break;

            case WorkflowGraph::CONDITION:
                return $this->validateCondition($config, "La condition \"$label\"", $subjectType);
        }

        return [];
    }

    /**
     * "Wait": a duration, the next occurrence of a time, or a date (see WaitNode)
     */
    protected function validateWait(array $config, string $label): array
    {
        $timezone = $config['timezone'] ?? null;

        if ($timezone !== null && !(is_string($timezone) && ProjectTimezone::valid($timezone))) {
            return ["L'attente \"$label\" utilise un fuseau horaire inconnu."];
        }

        $mode = WaitNode::mode($config);

        if ($mode === null) {
            return ["L'attente \"$label\" doit durer au moins une minute (ou aller jusqu'à une heure ou une date)."];
        }

        if ($mode === WaitNode::DURATION && WaitNode::seconds($config) > config('workflows.max_wait_days') * 86400) {
            return ["L'attente \"$label\" ne peut pas dépasser " . config('workflows.max_wait_days') . ' jours.'];
        }

        if ($mode === WaitNode::TIME) {
            $days = $config['days'] ?? [1, 2, 3, 4, 5, 6, 7];

            if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) $config['until_time'])) {
                return ["L'attente \"$label\" : l'heure est invalide (format HH:MM)."];
            }

            if (!is_array($days) || !$days || array_diff($days, [1, 2, 3, 4, 5, 6, 7])) {
                return ["L'attente \"$label\" : les jours sont invalides (1 = lundi ... 7 = dimanche)."];
            }
        }

        if ($mode === WaitNode::DATE) {
            $until = WaitNode::resumeAt($config, $timezone ?? config('app.timezone'), now());

            if (!$until) {
                return ["L'attente \"$label\" : la date est invalide (format AAAA-MM-JJ HH:MM)."];
            }

            if ($until->gt(now()->addDays(config('workflows.max_wait_days')))) {
                return ["L'attente \"$label\" ne peut pas dépasser " . config('workflows.max_wait_days') . ' jours.'];
            }
        }

        return [];
    }

    /**
     * Configuration of the trigger node
     */
    protected function validateTrigger(string $type, array $config, string $subjectType): array
    {
        $label = 'Le déclencheur';

        if (str_contains($type, '.filter_')) {
            if (!(new ConditionEvaluator())->tree($config)) {
                return ["$label \"entre dans / sort d'un filtre\" doit définir le filtre à surveiller."];
            }

            return $this->validateCondition($config, $label, $subjectType);
        }

        if ($type === 'event.starting_soon') {
            $minutes = $config['minutes_before'] ?? 0;

            if (!is_int($minutes) || $minutes < 0 || $minutes > 10080) {
                return ["$label \"RDV approchant\" doit avoir un délai entre 0 minute et 7 jours."];
            }
        }

        return [];
    }

    /**
     * A condition: groups of filters and time checks (see ConditionEvaluator)
     */
    protected function validateCondition(array $config, string $label, ?string $subjectType): array
    {
        if (isset($config['items'])) {
            return $this->validateGroup($config, $label, $subjectType, 1);
        }

        if (!in_array($config['mode'] ?? ConditionEvaluator::ALL, [ConditionEvaluator::ALL, ConditionEvaluator::ANY], true)) {
            return ["$label a un mode invalide."];
        }

        return $this->validateFilters($config['filters'] ?? [], $label, $subjectType);
    }

    protected function validateGroup(array $group, string $label, ?string $subjectType, int $depth): array
    {
        if ($depth > ConditionEvaluator::MAX_DEPTH) {
            return ["$label est trop imbriquée (" . ConditionEvaluator::MAX_DEPTH . ' niveaux au maximum).'];
        }

        if (!in_array($group['op'] ?? 'and', ['and', 'or'], true) || !is_array($group['items'])) {
            return ["$label est mal configurée."];
        }

        $errors = [];

        foreach ($group['items'] as $item) {
            if (!is_array($item)) {
                $errors[] = "$label est mal configurée.";
            } elseif (isset($item['items'])) {
                $errors = array_merge($errors, $this->validateGroup($item, $label, $subjectType, $depth + 1));
            } elseif (isset($item['check'])) {
                $errors = array_merge($errors, $this->validateCheck($item, $label));
            } elseif (!empty($item['filters'])) {
                $errors = array_merge($errors, $this->validateFilters($item['filters'], $label, $subjectType));
            } else {
                $errors[] = "$label contient un critère vide.";
            }
        }

        return $errors;
    }

    protected function validateFilters($filters, string $label, ?string $subjectType): array
    {
        if (!is_array($filters)) {
            return ["$label est mal configurée."];
        }

        if ($subjectType) {
            $known = WorkflowSubject::filters($subjectType);

            foreach (array_keys($filters) as $name) {
                if (!is_string($name) || !method_exists($known, $name)) {
                    return ["$label utilise un critère inconnu (\"$name\")."];
                }
            }
        }

        return [];
    }

    protected function validateCheck(array $check, string $label): array
    {
        if (!in_array($check['check'], TimeChecks::CHECKS, true)) {
            return ["$label utilise une vérification inconnue."];
        }

        if (isset($check['timezone']) && !(is_string($check['timezone']) && ProjectTimezone::valid($check['timezone']))) {
            return ["$label utilise un fuseau horaire inconnu."];
        }

        if ($check['check'] === TimeChecks::WORK_HOURS) {
            $days = $check['days'] ?? [1, 2, 3, 4, 5, 6, 7];
            $from = TimeChecks::minutes($check['from'] ?? '00:00');
            $to = TimeChecks::minutes($check['to'] ?? '24:00');

            if (!is_array($days) || !$days || array_diff($days, [1, 2, 3, 4, 5, 6, 7])) {
                return ["$label : les jours des heures de travail sont invalides (1 = lundi ... 7 = dimanche)."];
            }

            if ($from === null || $to === null || $from === $to) {
                return ["$label : les horaires de travail sont invalides (format HH:MM, début différent de la fin)."];
            }
        } else {
            foreach (['from', 'to'] as $bound) {
                if (isset($check[$bound]) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $check[$bound])) {
                    return ["$label : les dates de la période sont invalides (format AAAA-MM-JJ)."];
                }
            }
        }

        return [];
    }

    /**
     * The step a message names between quotes ("action1"), when it names one
     */
    protected function mentionedNode(string $message, array $ids): ?string
    {
        foreach ($ids as $id) {
            if (str_contains($message, '"' . $id . '"')) {
                return (string) $id;
            }
        }

        return null;
    }

    protected function label(array $node): string
    {
        return $node['name'] ?? $node['id'] ?? '?';
    }
}
