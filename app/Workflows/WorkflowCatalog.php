<?php

namespace App\Workflows;

use App\Workflows\Nodes\WaitEventNode;
use App\Workflows\Providers\ProviderRegistry;

/**
 * What a workflow can be made of, described for the builder.
 *
 * The backend is the source of truth: the builder offers exactly this and
 * builds its forms from the "fields" of each item, instead of duplicating the
 * rules. It holds structure only (identifiers and field types); the texts
 * shown to the user ("Quand un nouveau prospect est créé") belong to the
 * front-end translations, keyed by these identifiers.
 *
 * Field types
 *   boolean, number, text, html (rich text with {{variables}}), minutes
 *   user, group, label, thread    pick an item of the project
 *   users, groups, labels         pick several
 *   provider                      an operator of the project (capability given)
 *   condition                     a condition editor (see ConditionEvaluator)
 *   wait                          a duration / time / date (see WaitNode)
 *   event                         one of the awaited events
 */
class WorkflowCatalog
{
    public function build(): array
    {
        return [
            'nodes' => WorkflowGraph::TYPES,
            'triggers' => $this->triggers(),
            'actions' => $this->actions(),
            'events' => WaitEventNode::EVENTS,
            'conditions' => [
                'filters' => $this->filters(),
                'checks' => [
                    ['name' => TimeChecks::WORK_HOURS, 'fields' => [
                        ['key' => 'days', 'type' => 'weekdays'],
                        ['key' => 'from', 'type' => 'time'],
                        ['key' => 'to', 'type' => 'time'],
                    ]],
                    ['name' => TimeChecks::DATE_RANGE, 'fields' => [
                        ['key' => 'from', 'type' => 'date'],
                        ['key' => 'to', 'type' => 'date'],
                    ]],
                ],
                'max_depth' => ConditionEvaluator::MAX_DEPTH,
            ],
            'variables' => VariableResolver::VARIABLES,
            'limits' => [
                'max_wait_days' => config('workflows.max_wait_days'),
                'max_nodes' => WorkflowGraphSanitizer::MAX_NODES,
            ],
        ];
    }

    protected function triggers(): array
    {
        $fields = [
            'prospect.filter_entered' => [
                ['key' => 'condition', 'type' => 'condition', 'required' => true],
                ['key' => 'exit_on_leave', 'type' => 'boolean'],
                ['key' => 'once_per_subject', 'type' => 'boolean'],
            ],
            'prospect.filter_left' => [
                ['key' => 'condition', 'type' => 'condition', 'required' => true],
            ],
            'event.starting_soon' => [
                ['key' => 'minutes_before', 'type' => 'minutes'],
            ],
        ];

        return array_map(fn ($type) => [
            'type' => $type,
            'subject' => WorkflowSubject::fromTrigger($type),
            'fields' => $fields[$type] ?? [],
        ], config('workflows.triggers'));
    }

    protected function actions(): array
    {
        $onError = ['key' => 'on_error', 'type' => 'on_error'];

        return [
            [
                'type' => 'send_email', 'category' => 'communication', 'capability' => ProviderRegistry::EMAIL,
                'fields' => [
                    ['key' => 'provider', 'type' => 'provider', 'capability' => ProviderRegistry::EMAIL, 'required' => true],
                    // Optional: without it the first public channel of the project is used
                    ['key' => 'thread', 'type' => 'thread'],
                    ['key' => 'body', 'type' => 'html', 'required' => true],
                    ['key' => 'user', 'type' => 'user'],
                    $onError,
                ],
            ],
            [
                'type' => 'send_sms', 'category' => 'communication', 'capability' => ProviderRegistry::SMS,
                'fields' => [
                    ['key' => 'provider', 'type' => 'provider', 'capability' => ProviderRegistry::SMS, 'required' => true],
                    ['key' => 'message', 'type' => 'text', 'required' => true],
                    $onError,
                ],
            ],
            [
                'type' => 'make_call', 'category' => 'communication', 'capability' => ProviderRegistry::CALL,
                'fields' => [
                    ['key' => 'provider', 'type' => 'provider', 'capability' => ProviderRegistry::CALL, 'required' => true],
                    $onError,
                ],
            ],
            // The actions of the campaigns, reused as they are (value: their own format)
            ['type' => 'prospect-attach-user', 'category' => 'assignment', 'fields' => [['key' => 'value.user', 'type' => 'user', 'required' => true]]],
            ['type' => 'prospect-detach-user', 'category' => 'assignment', 'fields' => [['key' => 'value.user', 'type' => 'user', 'required' => true]]],
            ['type' => 'prospect-attach-group', 'category' => 'assignment', 'fields' => [['key' => 'value.group', 'type' => 'group', 'required' => true]]],
            ['type' => 'prospect-detach-group', 'category' => 'assignment', 'fields' => [['key' => 'value.group', 'type' => 'group', 'required' => true]]],
            ['type' => 'prospect-attach-label', 'category' => 'label', 'fields' => [['key' => 'value.label', 'type' => 'label', 'required' => true]]],
            ['type' => 'prospect-detach-label', 'category' => 'label', 'fields' => [['key' => 'value.label', 'type' => 'label', 'required' => true]]],
            ['type' => 'prospect-processed', 'category' => 'status', 'fields' => []],
            ['type' => 'prospect-not-processed', 'category' => 'status', 'fields' => []],
            ['type' => 'prospect-delete', 'category' => 'status', 'danger' => true, 'fields' => []],
        ];
    }

    /**
     * The filters the condition editor offers, by subject type. Only criteria
     * whose behaviour is simple and known; "advanced" ones can be added here.
     * (Orders and appointments only get the date / time checks for now.)
     */
    protected function filters(): array
    {
        return [
            WorkflowSubject::PROSPECT => [
                ['name' => 'incomingCommunications', 'type' => 'boolean'],
                ['name' => 'processed', 'type' => 'boolean'],
                ['name' => 'createdDaysAgo', 'type' => 'number'],
                ['name' => 'updatedDaysAgo', 'type' => 'number'],
                ['name' => 'withUsers', 'type' => 'users'],
                ['name' => 'withoutUsers', 'type' => 'users'],
                ['name' => 'withGroups', 'type' => 'groups'],
                ['name' => 'withoutGroups', 'type' => 'groups'],
                ['name' => 'withLabels', 'type' => 'labels'],
                ['name' => 'withoutLabels', 'type' => 'labels'],
            ],
            WorkflowSubject::ORDER => [],
            WorkflowSubject::EVENT => [],
        ];
    }
}
