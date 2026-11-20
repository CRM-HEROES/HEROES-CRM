<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Workflows\Providers\ProviderRegistry;
use App\Workflows\WorkflowCatalog;

/**
 * Everything needed to build a workflow: the format of a graph, the triggers,
 * the actions and their fields, the conditions, the variables, and the
 * operators (SMS, e-mail, call) configured in the project. It is the catalogue
 * the workflow builder of the interface uses: the CRM is the source of truth,
 * the agent reads it instead of guessing.
 */
class WorkflowCatalogTool implements AgentToolInterface
{
    public function __construct(protected WorkflowCatalog $catalog, protected ProviderRegistry $providers)
    {
    }

    public function key(): string
    {
        return 'workflow_catalog';
    }

    public function description(): string
    {
        return "Décrit comment construire un workflow : format du graphe, déclencheurs, actions (SMS, e-mail, appel...), conditions, variables {{...}} et opérateurs du projet. À lire AVANT workflow_save_draft.";
    }

    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => new \stdClass(), 'additionalProperties' => false];
    }

    public function module(): string
    {
        return 'workflow';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return 'projectWorkflowView';
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $operators = array_map(
            fn ($p) => ['ref' => $p['ref'], 'label' => $p['label'], 'capabilities' => $p['capabilities']],
            $this->providers->forProject($context->project->id)
        );

        return ToolResult::ok([
            'graph_format' => [
                'summary' => 'Un graphe = des étapes (nodes) reliées (edges). Il commence par UN nœud trigger et chaque chemin finit par un nœud end. Pas de boucle.',
                'node' => '{ "id": "n1", "type": "trigger|condition|action|wait|wait_event|end", "name": "libellé facultatif", "config": {...} }',
                'edge' => '{ "from": "n1", "to": "n2", "branch": "yes|no" }  (branch seulement en sortie d\'une condition ou d\'un wait_event)',
                'trigger' => 'config: { "trigger": "<type de la liste triggers>", ...champs du déclencheur }. Le nœud trigger de départ s\'appelle "trigger".',
                'action' => 'config: { "action": "<type de la liste actions>", "provider": "<ref d\'un opérateur>", ...champs de l\'action }',
                'wait' => 'config: { "minutes": 0, "hours": 0, "days": 2 } ou { "until_date": "AAAA-MM-JJ" } ou { "until_time": "HH:MM", "days": [1..7] } '
                    . 'ou RELATIF À UN RENDEZ-VOUS : { "relative_to": "next_event|last_event|created", "offset_days": 7, "direction": "before|after", "at": "09:00", "if_past": "end|skip", "check_only": false }. '
                    . 'Ex. « 7 jours avant le bilan » = { "relative_to": "next_event", "offset_days": 7, "direction": "before", "at": "09:00" } ; next_event = le prochain rendez-vous du prospect, last_event = le dernier passé, created = la création du prospect ; direction after sur next_event = « N jours après le rendez-vous ». '
                    . 'Si le moment est déjà passé (ou s\'il n\'y a aucun rendez-vous), l\'automatisation s\'arrête sans rien envoyer (if_past "skip" : elle continue). check_only: true n\'attend pas, il vérifie seulement que le moment est encore à venir.',
                'wait_event' => 'config: { "event": "<un des events>", "days"/"hours"/"minutes": délai maximal } ; branche yes si l\'événement arrive, no si le délai passe',
                'condition' => 'config = l\'arbre de conditions lui-même : { "op": "and|or", "items": [ ... ] } ; branches yes / no. Un item est : { "filters": { "withLabels": [12] } } (un filtre du catalogue conditions.filters), '
                    . '{ "check": "work_hours", ... } (un horaire), { "field": "rdv_fixe", "is": "oui" } ou { "field": "joint", "is_not": "oui" } ou { "field": "centre", "filled": true|false } (un CHAMP PERSONNALISÉ du prospect, par son slug en minuscules ; le champ calculé plus_50 se teste avec is: "oui"/"non"), '
                    . 'ou un sous-groupe { "op": ..., "items": [...] }. Les mêmes items servent d\'audience au déclencheur prospect.filter_entered (config.items) : { "trigger": "prospect.filter_entered", "op": "and", "items": [ { "filters": { "withLabels": [ID] } }, { "field": "rdv_fixe", "is_not": "oui" } ], "exit_on_leave": true, "once_per_subject": true }. '
                    . 'Avec exit_on_leave, le prospect qui ne remplit plus ces conditions sort de l\'automatisation même pendant une attente : c\'est ainsi qu\'on exprime « la séquence s\'arrête quand un rendez-vous est fixé ».',
                'schedule' => 'Déclencheur "prospect.scheduled" (envoi à date fixe, ex. une newsletter) : { "trigger": "prospect.scheduled", "months": [1], "days": [8], "weekdays": [], "at": "11:00", "window_minutes": 60, "op": "and", "items": [ audience ] }. '
                    . 'days = jours du mois (ou weekdays = 1 lundi .. 7 dimanche), months vide = tous les mois ; les prospects de l\'audience partent à partir de "at", étalés sur window_minutes ; un seul envoi par prospect et par jour.',
                'example' => [
                    'nodes' => [
                        ['id' => 'trigger', 'type' => 'trigger', 'config' => ['trigger' => 'prospect.created']],
                        ['id' => 'w1', 'type' => 'wait', 'config' => ['days' => 1]],
                        ['id' => 's1', 'type' => 'action', 'config' => ['action' => 'send_sms', 'provider' => '<ref>', 'message' => 'Bonjour {{prospect.firstname}}']],
                        ['id' => 'end1', 'type' => 'end', 'config' => []],
                    ],
                    'edges' => [['from' => 'trigger', 'to' => 'w1'], ['from' => 'w1', 'to' => 's1'], ['from' => 's1', 'to' => 'end1']],
                ],
            ],
            'catalog' => $this->catalog->build(),
            'operators' => $operators,
            'lifecycle' => "workflow_save_draft enregistre un brouillon (il renvoie les problèmes à corriger) ; workflow_publish le fige en version ; workflow_set_status l'active (validé par l'utilisateur).",
        ], 'Catalogue des workflows', 1);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
