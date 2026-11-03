<?php

namespace App\Agents\Writes;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\EntityRegistry;
use App\Agents\Entities\ReadGateway;
use App\Agents\Tools\AgentToolInterface;
use App\Agents\Tools\ProspectBulkTool;
use App\Agents\Tools\ProspectRecordTool;
use App\Agents\Tools\ToolResult;
use App\Models\Field;
use App\Models\Group;
use App\Models\Label;
use App\Workflows\Providers\ProviderRegistry;
use Illuminate\Support\Facades\DB;

/**
 * What an agent can DO in the CRM, declared as data.
 *
 * Each capability names the route of the CRM it goes through, the ability the
 * owner needs, its risk, and what to check first. To expose a new action, add a
 * definition here (or write a class implementing AgentToolInterface and
 * register it in AppServiceProvider); no engine code changes, and the tool is
 * subject to the same permission gate, approval, audit and limits as the rest.
 *
 * The routes do not all check an ability (several update, delete and bulk
 * routes only rely on the interface hiding the button): the ability of every
 * definition below is therefore the real guard.
 */
class WriteCapabilities
{
    public function __construct(
        protected InternalApi $api,
        protected ReadGateway $reader,
        protected EntityRegistry $entities,
        protected ProviderRegistry $providers,
    ) {
    }

    /**
     * @return AgentToolInterface[]
     */
    public function tools(): array
    {
        return array_merge(
            array_map(fn ($definition) => new ProspectBulkTool($definition, $this->api, $this->reader, $this->entities), $this->bulk()),
            array_map(fn ($definition) => new ProspectRecordTool($definition, $this->api, $this->reader), $this->records()),
        );
    }

    // On a set of prospects

    protected function bulk(): array
    {
        return [
            [
                'key' => 'prospect_set_labels',
                'description' => "Ajoute ou retire des labels (étiquettes, par exemple le statut NRP) sur des prospects. Les ids de labels viennent de project_config. Dans une catégorie « un label par prospect » (statut), ajouter un label remplace l'ancien.",
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectUpdate',
                'editable' => true,
                'schema' => [
                    'action' => ['type' => 'string', 'enum' => ['attach', 'detach'], 'description' => 'attach : ajouter, detach : retirer.'],
                    'label_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => 10, 'description' => 'Labels (project_config, section labels).'],
                ],
                'required' => ['action', 'label_ids'],
                'check' => fn ($context, $input) => $this->idsExist(
                    Label::query()->whereHas('category', fn ($q) => $q->where('project_id', $context->project->id)->where('for', 'prospect'))->whereIn('id', $input['label_ids'])->pluck('id')->all(),
                    $input['label_ids'], 'label', 'project_config'
                ),
                'request' => fn ($input, $ids, $context) => ['PUT', 'prospect/label/bulk', ['action' => $input['action'], 'labels' => $input['label_ids'], 'prospects' => $ids]],
                'describe' => fn ($context, $input) => ($input['action'] === 'attach' ? 'Ajouter' : 'Retirer') . ' le(s) label(s) : ' . $this->names(Label::query(), $input['label_ids']),
            ],
            [
                'key' => 'prospect_set_groups',
                'description' => "Ajoute ou retire des prospects dans des groupes. Les ids de groupes viennent de project_config.",
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectUpdate',
                'editable' => true,
                'schema' => [
                    'action' => ['type' => 'string', 'enum' => ['attach', 'detach']],
                    'group_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => 10],
                ],
                'required' => ['action', 'group_ids'],
                'check' => fn ($context, $input) => $this->idsExist(
                    Group::query()->where('project_id', $context->project->id)->whereIn('id', $input['group_ids'])->pluck('id')->all(),
                    $input['group_ids'], 'groupe', 'project_config'
                ),
                'request' => fn ($input, $ids, $context) => ['PUT', 'prospect/group/bulk', ['action' => $input['action'], 'groups' => $input['group_ids'], 'prospects' => $ids]],
                'describe' => fn ($context, $input) => ($input['action'] === 'attach' ? 'Ajouter aux' : 'Retirer des') . ' groupe(s) : ' . $this->names(Group::query(), $input['group_ids']),
            ],
            [
                'key' => 'prospect_assign_users',
                'description' => "Assigne des prospects à des utilisateurs, ou retire l'assignation. Attention : assigner remplace l'assignation précédente de ces utilisateurs sur ces prospects. Les ids d'utilisateurs viennent de crm_search (entité user).",
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectUpdate',
                'editable' => true,
                'schema' => [
                    'action' => ['type' => 'string', 'enum' => ['attach', 'detach']],
                    'user_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => 10],
                ],
                'required' => ['action', 'user_ids'],
                'check' => function (AgentExecutionContext $context, array $input) {
                    // Only users the owner may see (and therefore assign)
                    $visible = app(ReadGateway::class)->search($context, 'user', ['filters' => [], 'fields' => ['id'], 'limit' => 50]);
                    $known = array_column($visible['rows'], 'id');

                    return $this->idsExist(array_values(array_intersect($input['user_ids'], $known)), $input['user_ids'], 'utilisateur', 'crm_search (entité user)');
                },
                'request' => fn ($input, $ids, $context) => ['PUT', 'prospect/user/bulk', ['action' => $input['action'], 'users' => $input['user_ids'], 'filters' => ['ids' => $ids]]],
                'describe' => fn ($context, $input) => ($input['action'] === 'attach' ? 'Assigner à' : 'Désassigner de') . ' : ' . $this->names(\App\Models\User::query(), $input['user_ids'], 'name'),
            ],
            [
                'key' => 'prospect_set_field',
                'description' => "Met la même valeur dans un champ de plusieurs prospects (ville, statut texte, champ personnalisé...). Les champs possibles viennent de project_config (section fields). Pour modifier plusieurs champs d'un seul prospect, utilise prospect_update.",
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectUpdate',
                'editable' => true,
                'schema' => [
                    'field' => ['type' => 'string', 'description' => 'Slug du champ (project_config).'],
                    'value' => ['type' => 'string', 'description' => 'Nouvelle valeur (non vide).'],
                ],
                'required' => ['field', 'value'],
                'check' => function (AgentExecutionContext $context, array $input) {
                    if (trim($input['value']) === '') {
                        return ToolResult::error('La valeur ne peut pas être vide.', ToolResult::INVALID_INPUT);
                    }

                    $field = $this->field($context, $input['field']);

                    return $field instanceof ToolResult ? $field : null;
                },
                // The route reads custom fields as "meta->slug"
                'request' => fn ($input, $ids, $context) => ['PUT', 'prospect/bulk', [
                    'action' => 'field', 'prospects' => $ids, 'value' => $input['value'],
                    'field' => ($this->field($context, $input['field'])->meta ? 'meta->' : '') . $input['field'],
                ]],
                'describe' => fn ($context, $input) => "Mettre le champ « {$input['field']} » à « {$input['value']} »",
            ],
            [
                'key' => 'prospect_delete',
                'description' => "Supprime des prospects (suppression douce : ils vont à la corbeille du CRM et peuvent y être restaurés). Toujours validée par l'utilisateur.",
                'risk' => AgentToolInterface::DESTRUCTIVE,
                'ability' => 'prospectDelete',
                'editable' => true,
                'request' => fn ($input, $ids, $context) => ['PUT', 'prospect/bulk', ['action' => 'delete', 'prospects' => $ids]],
                'describe' => fn ($context, $input) => 'Supprimer (corbeille)',
            ],
            [
                'key' => 'sms_send_bulk',
                'count_key' => 'count',
                'description' => "Envoie le même SMS à plusieurs prospects (ceux qui n'ont pas de téléphone sont ignorés par le CRM). L'envoi est réel et immédiat. Toujours validé par l'utilisateur, qui voit le nombre de destinataires.",
                'risk' => AgentToolInterface::COMMUNICATION,
                'ability' => 'prospectSmsAdd',
                'editable' => true,
                'schema' => [
                    'provider' => ['type' => 'string', 'description' => 'Référence du canal (project_config > messaging > sms > ref).'],
                    'message' => ['type' => 'string', 'maxLength' => 1000],
                ],
                'required' => ['provider', 'message'],
                'check' => function ($context, $input) {
                    if (trim($input['message']) === '') {
                        return ToolResult::error('Le message est vide.', ToolResult::INVALID_INPUT);
                    }

                    return $this->providers->find($context->project->id, $input['provider'], ProviderRegistry::SMS)
                        ? null
                        : ToolResult::error("Canal SMS inconnu : {$input['provider']}. Voir project_config (messaging).", ToolResult::NOT_FOUND);
                },
                'request' => function ($input, $ids, $context) {
                    $provider = $this->providers->find($context->project->id, $input['provider'], ProviderRegistry::SMS);

                    return ['POST', 'sms/bulk', ['prospects' => $ids, 'source' => $provider['source'] ?? $provider['operator'], 'message' => $input['message']]];
                },
                'describe' => fn ($context, $input) => 'Envoyer un SMS via ' . $input['provider'] . ' : « ' . mb_substr($input['message'], 0, 200) . ' »',
            ],
            [
                'key' => 'email_send_bulk',
                'description' => "Envoie le même e-mail à plusieurs prospects (ceux sans adresse sont ignorés par le CRM). L'envoi est réel. Toujours validé par l'utilisateur, qui voit le nombre de destinataires.",
                'risk' => AgentToolInterface::COMMUNICATION,
                'ability' => 'projectThreadAdd',
                'editable' => true,
                'schema' => [
                    'subject' => ['type' => 'string', 'maxLength' => 255],
                    'body' => ['type' => 'string', 'maxLength' => 20000],
                ],
                'required' => ['subject', 'body'],
                'request' => fn ($input, $ids, $context) => ['POST', 'prospect/email/bulk', ['prospects' => $ids, 'subject' => $input['subject'], 'body' => $input['body']]],
                'describe' => fn ($context, $input) => 'Envoyer l\'e-mail « ' . $input['subject'] . ' » : ' . mb_substr(strip_tags($input['body']), 0, 200),
            ],
        ];
    }

    // On one prospect

    protected function records(): array
    {
        $values = ['type' => 'object', 'description' => 'Champs à renseigner : { "slug du champ": "valeur" }. Slugs possibles dans project_config (section fields) : first_name, last_name, email, phone_number, city... et les champs personnalisés.'];

        return [
            [
                'key' => 'prospect_create',
                'description' => 'Crée un prospect dans le projet.',
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectAdd',
                'schema' => ['values' => $values],
                'required' => ['values'],
                'request' => fn ($context, $input) => $this->fieldsBody($context, $input['values']) instanceof ToolResult
                    ? $this->fieldsBody($context, $input['values'])
                    : ['POST', 'prospect', $this->fieldsBody($context, $input['values'])],
                'describe' => fn ($context, $input) => ['action' => 'Créer un prospect', 'values' => $input['values']],
                'result' => fn ($json) => ['prospect_id' => $json['id'] ?? null],
            ],
            [
                'key' => 'prospect_update',
                'description' => "Modifie les champs d'un prospect. Une fiche déjà traitée ne peut plus être modifiée.",
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectUpdate',
                'prospect' => 'prospect_id',
                'editable' => true,
                'schema' => ['prospect_id' => ['type' => 'integer'], 'values' => $values],
                'required' => ['prospect_id', 'values'],
                'request' => fn ($context, $input, $prospect) => $this->fieldsBody($context, $input['values']) instanceof ToolResult
                    ? $this->fieldsBody($context, $input['values'])
                    : ['PUT', "prospect/{$prospect->id}", $this->fieldsBody($context, $input['values'])],
                'describe' => fn ($context, $input) => ['action' => 'Modifier la fiche', 'values' => $input['values']],
            ],
            [
                'key' => 'appointment_create',
                'description' => "Crée un rendez-vous ou une tâche dans un calendrier pour un prospect, affecté à un utilisateur. Calendriers et utilisateurs : project_config (calendars) et crm_search (user). Dates au format AAAA-MM-JJ HH:MM (heure du projet).",
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectEventAdd',
                'module' => 'appointment',
                'prospect' => 'prospect_id',
                'editable' => true,
                'schema' => [
                    'prospect_id' => ['type' => 'integer'],
                    'calendar_id' => ['type' => 'integer'],
                    'user_id' => ['type' => 'integer', 'description' => 'Utilisateur à qui le rendez-vous est affecté.'],
                    'name' => ['type' => 'string', 'maxLength' => 200],
                    'started_at' => ['type' => 'string', 'description' => 'AAAA-MM-JJ HH:MM'],
                    'ended_at' => ['type' => 'string', 'description' => 'AAAA-MM-JJ HH:MM (par défaut : 30 minutes après le début)'],
                    'description' => ['type' => 'string', 'maxLength' => 2000],
                    'location' => ['type' => 'string', 'maxLength' => 255],
                ],
                'required' => ['prospect_id', 'calendar_id', 'user_id', 'name', 'started_at'],
                'request' => function ($context, $input, $prospect) {
                    try {
                        $start = \Carbon\Carbon::parse($input['started_at']);
                        $end = isset($input['ended_at']) ? \Carbon\Carbon::parse($input['ended_at']) : $start->copy()->addMinutes(30);
                    } catch (\Throwable) {
                        return ToolResult::error('Date invalide : utilise le format AAAA-MM-JJ HH:MM.', ToolResult::INVALID_INPUT);
                    }

                    if ($end->lte($start)) {
                        return ToolResult::error('La fin du rendez-vous doit être après son début.', ToolResult::INVALID_INPUT);
                    }

                    return ['POST', "prospect/{$prospect->id}/event", array_filter([
                        'calendar_id' => $input['calendar_id'],
                        'user_id' => $input['user_id'],
                        'name' => $input['name'],
                        'description' => $input['description'] ?? null,
                        'location' => $input['location'] ?? null,
                        'started_at' => $start->format('Y-m-d H:i:s'),
                        'ended_at' => $end->format('Y-m-d H:i:s'),
                    ], fn ($v) => $v !== null)];
                },
                'describe' => fn ($context, $input) => ['action' => 'Créer un rendez-vous', 'name' => $input['name'], 'quand' => $input['started_at'], 'calendrier' => $input['calendar_id'], 'affecté_à' => $input['user_id']],
                'result' => fn ($json) => ['event_id' => $json['id'] ?? null],
            ],
            [
                'key' => 'sms_send',
                'description' => "Envoie un SMS à un prospect, avec un canal d'envoi du projet (project_config, section messaging). L'envoi est réel et immédiat. Toujours validé par l'utilisateur.",
                'risk' => AgentToolInterface::COMMUNICATION,
                'ability' => 'prospectSmsAdd',
                'module' => 'sms',
                'prospect' => 'prospect_id',
                'editable' => true,
                'schema' => [
                    'prospect_id' => ['type' => 'integer'],
                    'provider' => ['type' => 'string', 'description' => 'Référence du canal (project_config > messaging > sms > ref).'],
                    'message' => ['type' => 'string', 'maxLength' => 1000],
                ],
                'required' => ['prospect_id', 'provider', 'message'],
                'request' => function ($context, $input, $prospect) {
                    $provider = $this->providers->find($context->project->id, $input['provider'], ProviderRegistry::SMS);

                    if (!$provider) {
                        return ToolResult::error("Canal SMS inconnu : {$input['provider']}. Voir project_config (messaging).", ToolResult::NOT_FOUND);
                    }

                    if (trim($input['message']) === '') {
                        return ToolResult::error('Le message est vide.', ToolResult::INVALID_INPUT);
                    }

                    if (!filled($prospect->mobile_phone_number) && !filled($prospect->phone_number)) {
                        return ToolResult::error("Ce prospect n'a aucun numéro de téléphone.", ToolResult::VALIDATION_FAILED);
                    }

                    return ['POST', "prospect/{$prospect->id}/sms", ['source' => $provider['source'] ?? $provider['operator'], 'message' => $input['message']]];
                },
                'describe' => fn ($context, $input) => ['action' => 'Envoyer un SMS', 'canal' => $input['provider'], 'message' => $input['message']],
                'result' => fn ($json) => ['sms_id' => $json['id'] ?? null, 'note' => "Le SMS est créé ; l'opérateur l'envoie aussitôt (en cas d'échec, l'erreur est enregistrée sur le SMS)."],
            ],
            [
                'key' => 'email_send',
                'description' => "Envoie un e-mail à l'adresse d'un prospect (le projet doit avoir son e-mail configuré, voir project_config). L'envoi est réel. Toujours validé par l'utilisateur.",
                'risk' => AgentToolInterface::COMMUNICATION,
                'ability' => 'projectThreadAdd',
                'module' => 'email',
                'prospect' => 'prospect_id',
                'editable' => true,
                'schema' => [
                    'prospect_id' => ['type' => 'integer'],
                    'subject' => ['type' => 'string', 'maxLength' => 255],
                    'body' => ['type' => 'string', 'maxLength' => 20000, 'description' => 'Corps du message (texte ou HTML simple).'],
                ],
                'required' => ['prospect_id', 'subject', 'body'],
                'request' => function ($context, $input, $prospect) {
                    if (!filled($prospect->email)) {
                        return ToolResult::error("Ce prospect n'a pas d'adresse e-mail.", ToolResult::VALIDATION_FAILED);
                    }

                    // The address is the one of the record, never one given by the model
                    return ['POST', "prospect/{$prospect->id}/email", ['to' => $prospect->email, 'subject' => $input['subject'], 'body' => $input['body']]];
                },
                'describe' => fn ($context, $input) => ['action' => 'Envoyer un e-mail', 'objet' => $input['subject'], 'message' => mb_substr($input['body'], 0, 500)],
                'result' => fn ($json) => ['sent' => $json['sent'] ?? true],
            ],
            [
                'key' => 'appointment_update',
                'description' => "Modifie un rendez-vous ou une tâche d'un prospect (titre, date, lieu, affectation, fait / confirmé). L'id du rendez-vous vient de crm_search (entité appointment). Dates au format AAAA-MM-JJ HH:MM.",
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectEventUpdate',
                'module' => 'appointment',
                'prospect' => 'prospect_id',
                'editable' => true,
                'schema' => [
                    'prospect_id' => ['type' => 'integer'],
                    'event_id' => ['type' => 'integer'],
                    'name' => ['type' => 'string', 'maxLength' => 200],
                    'started_at' => ['type' => 'string', 'description' => 'AAAA-MM-JJ HH:MM'],
                    'ended_at' => ['type' => 'string', 'description' => 'AAAA-MM-JJ HH:MM'],
                    'description' => ['type' => 'string', 'maxLength' => 2000],
                    'location' => ['type' => 'string', 'maxLength' => 255],
                    'user_id' => ['type' => 'integer'],
                    'calendar_id' => ['type' => 'integer'],
                    'done' => ['type' => 'boolean'],
                    'confirmed' => ['type' => 'boolean'],
                ],
                'required' => ['prospect_id', 'event_id'],
                'request' => function ($context, $input, $prospect) {
                    $body = array_diff_key($input, ['prospect_id' => 1, 'event_id' => 1]);

                    foreach (['started_at', 'ended_at'] as $date) {
                        if (isset($body[$date])) {
                            try {
                                $body[$date] = \Carbon\Carbon::parse($body[$date])->format('Y-m-d H:i:s');
                            } catch (\Throwable) {
                                return ToolResult::error("Date invalide ($date) : utilise AAAA-MM-JJ HH:MM.", ToolResult::INVALID_INPUT);
                            }
                        }
                    }

                    if (!$body) {
                        return ToolResult::error('Aucune modification demandée.', ToolResult::INVALID_INPUT);
                    }

                    return ['PUT', "prospect/{$prospect->id}/event/{$input['event_id']}", $body];
                },
                'describe' => fn ($context, $input) => ['action' => 'Modifier le rendez-vous #' . $input['event_id'], 'values' => array_diff_key($input, ['prospect_id' => 1, 'event_id' => 1])],
                'result' => fn ($json) => ['event_id' => $json['id'] ?? null],
            ],
            [
                'key' => 'appointment_delete',
                'description' => "Supprime un rendez-vous ou une tâche. Toujours validé par l'utilisateur.",
                'risk' => AgentToolInterface::DESTRUCTIVE,
                'ability' => 'prospectEventDelete',
                'module' => 'appointment',
                'prospect' => 'prospect_id',
                'editable' => true,
                'schema' => ['prospect_id' => ['type' => 'integer'], 'event_id' => ['type' => 'integer']],
                'required' => ['prospect_id', 'event_id'],
                'request' => fn ($context, $input, $prospect) => ['DELETE', "prospect/{$prospect->id}/event/{$input['event_id']}", []],
                'describe' => fn ($context, $input) => ['action' => 'Supprimer le rendez-vous #' . $input['event_id']],
                'result' => fn ($json) => [],
            ],
            [
                'key' => 'interaction_log',
                'description' => "Enregistre un appel passé avec un prospect (résultat, durée, compte rendu). Le champ note est l'équivalent d'une note d'appel dans l'historique du prospect.",
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectInteractionAdd',
                'module' => 'call',
                'prospect' => 'prospect_id',
                'editable' => true,
                'schema' => [
                    'prospect_id' => ['type' => 'integer'],
                    'status' => ['type' => 'string', 'enum' => ['answered', 'no_answer', 'busy', 'voicemail', 'failed'], 'description' => "Résultat de l'appel."],
                    'started_at' => ['type' => 'string', 'description' => 'AAAA-MM-JJ HH:MM (maintenant par défaut)'],
                    'duration_seconds' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 14400],
                    'outgoing' => ['type' => 'boolean', 'description' => 'true : appel sortant (par défaut), false : entrant.'],
                    'note' => ['type' => 'string', 'maxLength' => 2000, 'description' => "Compte rendu de l'appel."],
                ],
                'required' => ['prospect_id', 'status'],
                'request' => function ($context, $input, $prospect) {
                    try {
                        $start = isset($input['started_at']) ? \Carbon\Carbon::parse($input['started_at']) : now();
                    } catch (\Throwable) {
                        return ToolResult::error('Date invalide : utilise AAAA-MM-JJ HH:MM.', ToolResult::INVALID_INPUT);
                    }

                    return ['POST', "prospect/{$prospect->id}/interaction", array_filter([
                        'status' => $input['status'],
                        'source' => 'manual',
                        'from_user' => $input['outgoing'] ?? true,
                        'started_at' => $start->format('Y-m-d H:i:s'),
                        'ended_at' => isset($input['duration_seconds']) ? $start->copy()->addSeconds($input['duration_seconds'])->format('Y-m-d H:i:s') : null,
                        'data' => isset($input['note']) ? ['note' => $input['note']] : null,
                    ], fn ($v) => $v !== null)];
                },
                'describe' => fn ($context, $input) => ['action' => 'Enregistrer un appel', 'résultat' => $input['status'], 'note' => $input['note'] ?? ''],
                'result' => fn ($json) => ['interaction_id' => $json['id'] ?? null],
            ],
            [
                'key' => 'order_create',
                'description' => 'Crée une commande pour un prospect. Les statuts possibles viennent de project_config (order_statuses).',
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectOrderAdd',
                'module' => 'order',
                'prospect' => 'prospect_id',
                'editable' => true,
                'schema' => [
                    'prospect_id' => ['type' => 'integer'],
                    'description' => ['type' => 'string', 'maxLength' => 2000],
                    'status_id' => ['type' => 'integer', 'description' => 'Statut (project_config > order_statuses).'],
                ],
                'required' => ['prospect_id'],
                'request' => fn ($context, $input, $prospect) => ['POST', "prospect/{$prospect->id}/order", array_diff_key($input, ['prospect_id' => 1])],
                'describe' => fn ($context, $input) => ['action' => 'Créer une commande'] + array_diff_key($input, ['prospect_id' => 1]),
                'result' => fn ($json) => ['order_id' => $json['id'] ?? null],
            ],
            [
                'key' => 'order_update',
                'description' => "Modifie une commande (description, statut). Son intitulé est calculé par le CRM à partir de ses produits. L'id de la commande vient de crm_search (entité order).",
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectOrderUpdate',
                'module' => 'order',
                'prospect' => 'prospect_id',
                'editable' => true,
                'schema' => [
                    'prospect_id' => ['type' => 'integer'],
                    'order_id' => ['type' => 'integer'],
                    'description' => ['type' => 'string', 'maxLength' => 2000],
                    'status_id' => ['type' => 'integer'],
                ],
                'required' => ['prospect_id', 'order_id'],
                'request' => fn ($context, $input, $prospect) => count($input) <= 2
                    ? ToolResult::error('Aucune modification demandée.', ToolResult::INVALID_INPUT)
                    : ['PUT', "prospect/{$prospect->id}/order/{$input['order_id']}", array_diff_key($input, ['prospect_id' => 1, 'order_id' => 1])],
                'describe' => fn ($context, $input) => ['action' => 'Modifier la commande #' . $input['order_id']] + array_diff_key($input, ['prospect_id' => 1, 'order_id' => 1]),
                'result' => fn ($json) => ['order_id' => $json['id'] ?? null],
            ],
            [
                'key' => 'workflow_set_status',
                'description' => "Active ou met en pause un workflow (automatisation) du projet. Activer un workflow peut envoyer des SMS, des e-mails ou lancer des appels à de nombreux prospects : toujours validé par l'utilisateur. Les ids de workflows viennent de crm_search (entité workflow).",
                'risk' => AgentToolInterface::COMMUNICATION,
                'ability' => 'projectWorkflowExecute',
                'module' => 'workflow',
                'schema' => [
                    'workflow_id' => ['type' => 'integer'],
                    'status' => ['type' => 'string', 'enum' => ['active', 'paused']],
                ],
                'required' => ['workflow_id', 'status'],
                'request' => fn ($context, $input) => ['POST', "workflow/{$input['workflow_id']}/" . ($input['status'] === 'active' ? 'activate' : 'pause'), []],
                'describe' => function ($context, $input) {
                    $name = \App\Models\Workflow::query()->where('project_id', $context->project->id)->whereKey($input['workflow_id'])->value('name');

                    return ['action' => ($input['status'] === 'active' ? 'Activer' : 'Mettre en pause') . ' le workflow', 'workflow' => $name ?? '#' . $input['workflow_id']];
                },
                'result' => fn ($json) => ['status' => $json['status'] ?? null],
            ],
            [
                'key' => 'order_delete',
                'description' => "Supprime une commande d'un prospect. Toujours validé par l'utilisateur.",
                'risk' => AgentToolInterface::DESTRUCTIVE,
                'ability' => 'prospectOrderDelete',
                'module' => 'order',
                'prospect' => 'prospect_id',
                'editable' => true,
                'schema' => ['prospect_id' => ['type' => 'integer'], 'order_id' => ['type' => 'integer']],
                'required' => ['prospect_id', 'order_id'],
                'request' => fn ($context, $input, $prospect) => ['DELETE', "prospect/{$prospect->id}/order/{$input['order_id']}", []],
                'describe' => fn ($context, $input) => ['action' => 'Supprimer la commande #' . $input['order_id']],
                'result' => fn ($json) => [],
            ],
            [
                'key' => 'workflow_publish',
                'description' => "Publie le brouillon d'un workflow comme nouvelle version (sans l'activer : il ne tourne pas encore). Échoue en listant les problèmes si le brouillon est invalide.",
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'projectWorkflowUpdate',
                'module' => 'workflow',
                'schema' => ['workflow_id' => ['type' => 'integer']],
                'required' => ['workflow_id'],
                'request' => fn ($context, $input) => ['POST', "workflow/{$input['workflow_id']}/publish", []],
                'describe' => function ($context, $input) {
                    $name = \App\Models\Workflow::query()->where('project_id', $context->project->id)->whereKey($input['workflow_id'])->value('name');

                    return ['action' => 'Publier le workflow', 'workflow' => $name ?? '#' . $input['workflow_id']];
                },
                'result' => fn ($json) => ['status' => $json['status'] ?? null, 'next' => "Pour le lancer : workflow_set_status (validé par l'utilisateur)."],
            ],
            [
                'key' => 'workflow_delete',
                'description' => "Supprime un workflow et annule ses exécutions en cours. Toujours validé par l'utilisateur.",
                'risk' => AgentToolInterface::DESTRUCTIVE,
                'ability' => 'projectWorkflowDelete',
                'module' => 'workflow',
                'schema' => ['workflow_id' => ['type' => 'integer']],
                'required' => ['workflow_id'],
                'request' => fn ($context, $input) => ['DELETE', "workflow/{$input['workflow_id']}", []],
                'describe' => function ($context, $input) {
                    $name = \App\Models\Workflow::query()->where('project_id', $context->project->id)->whereKey($input['workflow_id'])->value('name');

                    return ['action' => 'Supprimer le workflow', 'workflow' => $name ?? '#' . $input['workflow_id']];
                },
                'result' => fn ($json) => [],
            ],
            [
                'key' => 'export_prospects',
                'description' => "Prépare un export Excel de prospects et l'envoie par e-mail à l'utilisateur lui-même (jamais à une autre adresse). Cible : prospect_ids ou filters comme crm_search (entité prospect). Seuls les prospects que l'utilisateur voit sont exportés. Toujours validé par l'utilisateur, car des données personnelles quittent le CRM.",
                'risk' => AgentToolInterface::COMMUNICATION,
                'ability' => 'prospectExport',
                'module' => 'export',
                'schema' => [
                    'prospect_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => 200],
                    'filters' => ['type' => 'object', 'description' => 'Filtres de sélection (comme crm_search, entité prospect).'],
                    'fields' => ['type' => 'array', 'items' => ['type' => 'string'], 'maxItems' => 40, 'description' => 'Slugs des champs à exporter (project_config > fields). Par défaut : les colonnes habituelles de l\'utilisateur.'],
                ],
                'request' => function ($context, $input) {
                    $hasIds = !empty($input['prospect_ids']);
                    $hasFilters = !empty($input['filters']);

                    if ($hasIds === $hasFilters) {
                        return ToolResult::error('Donne soit prospect_ids, soit filters (un seul des deux, non vide).', ToolResult::INVALID_INPUT);
                    }

                    try {
                        $ids = $hasIds
                            ? $this->reader->visibleProspectIds($context, $input['prospect_ids'], false)['ids']
                            : $this->reader->selectProspects($context, $input['filters'], (int) config('agents.bulk_max', 20000), false)['ids'];
                    } catch (\App\Agents\Entities\AgentReadException $e) {
                        return ToolResult::error($e->getMessage(), $e->errorCode);
                    }

                    if (!$ids) {
                        return ToolResult::error('Aucun prospect à exporter.', ToolResult::NOT_FOUND);
                    }

                    $query = ['mode' => 'email', 'email' => auth()->user()->email, 'count' => count($ids), 'filters' => json_encode(['ids' => $ids])];

                    if (!empty($input['fields'])) {
                        foreach ($input['fields'] as $slug) {
                            $field = $this->field($context, $slug);

                            if ($field instanceof ToolResult) {
                                return $field;
                            }
                        }

                        $query['fields'] = implode(',', $input['fields']);
                    }

                    return ['GET', 'export/create', $query];
                },
                'describe' => function ($context, $input) {
                    $count = !empty($input['prospect_ids'])
                        ? count($input['prospect_ids'])
                        : ($this->reader->selectProspects($context, $input['filters'] ?? [], (int) config('agents.bulk_max', 20000), false)['total'] ?? 0);

                    return ['action' => "Exporter les prospects par e-mail à " . auth()->user()->email, 'prospects' => $count];
                },
                'result' => fn ($json) => ['message' => "L'export est en préparation ; il arrivera par e-mail à l'utilisateur."],
            ],
            [
                'key' => 'duplicates_detect',
                'description' => "Détecte les doublons de prospects sur un ou plusieurs champs (par exemple email, phone_number) et les marque dans le CRM. N'efface rien. Consulte ensuite les doublons avec crm_search (filtre is_duplicate).",
                'risk' => AgentToolInterface::WRITE,
                'ability' => 'prospectUpdate',
                'module' => 'duplicate',
                'schema' => ['fields' => ['type' => 'array', 'items' => ['type' => 'string'], 'maxItems' => 5, 'description' => 'Slugs des champs de comparaison (project_config > fields).']],
                'required' => ['fields'],
                'request' => function ($context, $input) {
                    // The CRM recomputes the marks of the WHOLE project, whoever looks: reserved to its administrators
                    if (!auth()->user()->is_super_admin && !auth()->user()->is_project_admin) {
                        return ToolResult::error('La détection de doublons recalcule tout le projet : réservée aux administrateurs.', ToolResult::PERMISSION_DENIED);
                    }

                    $ids = [];

                    foreach ($input['fields'] as $slug) {
                        $field = $this->field($context, $slug);

                        if ($field instanceof ToolResult) {
                            return $field;
                        }

                        // The route compares on field ids
                        $ids[] = $field->id;
                    }

                    return ['GET', 'duplicate/find', ['fields' => $ids]];
                },
                'describe' => fn ($context, $input) => ['action' => 'Détecter les doublons', 'champs' => implode(', ', $input['fields'])],
                'result' => fn ($json) => ['message' => 'Détection terminée : consulte les doublons avec crm_search (filtre is_duplicate).'],
            ],
            [
                'key' => 'duplicates_resolve',
                'description' => "Supprime des doublons déjà détectés (duplicates_detect), pour les prospects donnés UNIQUEMENT (jamais « tous »). prospect_ids = prospects marqués comme doublon (crm_search, filtre is_duplicate). Actions : delete_listed supprime ces prospects eux-mêmes et garde la fiche avec laquelle ils font doublon ; delete_matching supprime au contraire la fiche avec laquelle ils font doublon et garde ces prospects. Suppression douce (corbeille). Toujours validé par l'utilisateur.",
                'risk' => AgentToolInterface::DESTRUCTIVE,
                'ability' => 'prospectDelete',
                'module' => 'duplicate',
                'schema' => [
                    'action' => ['type' => 'string', 'enum' => ['delete_listed', 'delete_matching']],
                    'prospect_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => 200],
                ],
                'required' => ['action', 'prospect_ids'],
                'request' => function ($context, $input) {
                    if (!$input['prospect_ids']) {
                        // An empty list means "every duplicate" for the CRM: never sent
                        return ToolResult::error('Donne les prospects à traiter : une liste vide est refusée.', ToolResult::INVALID_INPUT);
                    }

                    $found = $this->reader->visibleProspectIds($context, $input['prospect_ids'], false);

                    if ($found['unknown']) {
                        return ToolResult::error('Certains prospects sont introuvables dans ce projet.', ToolResult::NOT_FOUND);
                    }

                    return ['DELETE', 'duplicate', ['action' => $input['action'] === 'delete_listed' ? 'remove_duplicating' : 'remove_duplicates', 'prospects' => $found['ids']]];
                },
                'describe' => fn ($context, $input) => ['action' => 'Traiter des doublons : ' . $input['action'], 'prospects' => count($input['prospect_ids'])],
                'result' => fn ($json) => [],
            ],
        ];
    }

    // Checks and helpers

    /**
     * @param  int[]  $found
     * @param  int[]  $asked
     */
    protected function idsExist(array $found, array $asked, string $what, string $where): ?ToolResult
    {
        $missing = array_values(array_diff(array_map('intval', $asked), array_map('intval', $found)));

        return $missing
            ? ToolResult::error(ucfirst($what) . ' introuvable dans ce projet : ' . implode(', ', $missing) . ". Les identifiants valides viennent de $where.", ToolResult::NOT_FOUND)
            : null;
    }

    protected function names($query, array $ids, string $column = 'name'): string
    {
        return $query->whereIn('id', $ids)->pluck($column)->implode(', ') ?: implode(', ', $ids);
    }

    /**
     * A field of the prospects of the project (or an error listing the valid ones).
     */
    protected function field(AgentExecutionContext $context, string $slug): Field|ToolResult
    {
        $field = Field::query()->where('project_id', $context->project->id)->where('for', 'prospect')->where('slug', $slug)->first();

        if ($field) {
            return $field;
        }

        $valid = Field::query()->where('project_id', $context->project->id)->where('for', 'prospect')->pluck('slug')->implode(', ');

        return ToolResult::error("Champ inconnu : $slug. Champs possibles : $valid.", ToolResult::INVALID_INPUT);
    }

    /**
     * The body of the prospect routes: the default fields at the top level, the
     * custom ones under "meta" - and only fields that exist in the project.
     */
    protected function fieldsBody(AgentExecutionContext $context, array $values): array|ToolResult
    {
        if (!$values) {
            return ToolResult::error('Aucune valeur à enregistrer.', ToolResult::INVALID_INPUT);
        }

        $body = [];
        $meta = [];

        foreach ($values as $slug => $value) {
            $field = $this->field($context, (string) $slug);

            if ($field instanceof ToolResult) {
                return $field;
            }

            if (!is_scalar($value) && $value !== null) {
                return ToolResult::error("La valeur du champ $slug doit être un texte ou un nombre.", ToolResult::INVALID_INPUT);
            }

            $field->meta ? $meta[$slug] = $value : $body[$slug] = $value;
        }

        return $meta ? $body + ['meta' => $meta] : $body;
    }
}
