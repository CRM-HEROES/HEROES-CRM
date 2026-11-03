<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Queue of the agent jobs. A model call is slow: give it its own workers
    | (supervisor/worker.conf) so it never starves the workflows or the SMS.
    |
    */

    'queue' => env('AGENTS_QUEUE', 'agents'),

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | The models an agent can use (id => label). The Claude API key is the one
    | each user saved in the CRM settings (users.claude_api_key): there is no
    | platform key, a run without a key fails with an explicit message.
    |
    */

    'models' => [
        'claude-opus-5-5' => 'Claude Opus 5.5',
        'claude-sonnet-5-5' => 'Claude Sonnet 5.5',
        'claude-haiku-5-5' => 'Claude Haiku 5.5',
    ],

    'default_model' => env('AGENTS_DEFAULT_MODEL', 'claude-opus-5-5'),

    // low | medium | high | xhigh | max (the default of the model is not
    // always the same: it is always sent explicitly)
    'default_effort' => env('AGENTS_DEFAULT_EFFORT', 'medium'),

    /*
    |--------------------------------------------------------------------------
    | Autonomy and risk
    |--------------------------------------------------------------------------
    |
    | read_only  the agent only reads.
    | propose    every write is kept as an action the user approves.
    | auto       the writes allowed by the permissions of the owner are run
    |            at once, except the risks listed in "approval_required_risks".
    |
    | An agent never has more rights than its owner (see PermissionGate).
    |
    */

    'default_autonomy' => 'read_only',

    // Always approved by a human, whatever the autonomy of the agent
    'approval_required_risks' => ['communication', 'destructive'],

    /*
    |--------------------------------------------------------------------------
    | Skills
    |--------------------------------------------------------------------------
    |
    | A skill is a set of tools plus the way to use them (the playbook, added to
    | the instructions of the agents that have it). Skills are declared here, in
    | the code base, and an agent only picks them: a new agent needs no code.
    |
    */

    'skills' => [
        'crm_exploration' => [
            'label' => 'Consulter le CRM',
            'tools' => ['crm_describe', 'crm_search', 'project_config', 'prospect_history'],
            'playbook' => "Pour consulter les données du CRM, appelle d'abord crm_describe (liste des entités, puis une entité pour ses champs et filtres) plutôt que de deviner un nom de champ ou de filtre. Cherche avec crm_search en demandant peu de champs et de lignes ; le total est donné avec chaque réponse. Une donnée personnelle (e-mail, téléphone) n'est renvoyée que si tu la demandes, et seulement si la demande de l'utilisateur en a besoin.",
        ],
        'prospect_management' => [
            'label' => 'Gérer les prospects',
            'tools' => ['project_config', 'crm_search', 'prospect_create', 'prospect_update', 'prospect_set_labels', 'prospect_set_groups', 'prospect_assign_users', 'prospect_set_field'],
            'playbook' => "Pour modifier des prospects : appelle project_config pour obtenir les identifiants (labels, groupes, champs), puis crm_search pour trouver les prospects et vérifier le total AVANT d'agir. Annonce clairement ce que tu vas faire et sur combien de prospects. Une action peut être mise en attente de validation : dis alors qu'elle n'est PAS encore exécutée et attends la décision de l'utilisateur ; n'affirme jamais qu'une action est faite avant d'en avoir le résultat. Les fiches déjà traitées ne sont pas modifiables : indique combien ont été ignorées.",
        ],
        'call_follow_up' => [
            'label' => 'Suivi des appels et des commandes',
            'tools' => ['project_config', 'crm_search', 'prospect_history', 'interaction_log', 'order_create', 'order_update', 'appointment_update'],
            'playbook' => "Avant de conseiller sur un prospect, lis son historique avec prospect_history. Enregistre un appel avec interaction_log en rédigeant un compte rendu factuel. Pour modifier ou annuler un rendez-vous, retrouve son id avec crm_search (appointment).",
        ],
        'data_quality' => [
            'label' => 'Qualité des données (doublons)',
            'tools' => ['project_config', 'crm_search', 'duplicates_detect', 'duplicates_resolve'],
            'playbook' => "Détecte les doublons avec duplicates_detect sur des champs fiables (e-mail, téléphone), regarde-les avec crm_search (is_duplicate) puis propose le traitement prospect par prospect. La suppression de doublons est toujours validée par l'utilisateur.",
        ],
        'automation_control' => [
            'label' => 'Pilotage des automatisations',
            'tools' => ['crm_search', 'workflow_set_status', 'appointment_delete'],
            'playbook' => "Consulte les workflows et campagnes avec crm_search (workflow, campaign). Activer un workflow peut contacter beaucoup de prospects : explique ce qu'il fera avant de le proposer.",
        ],
        'mass_communication' => [
            'label' => 'Envois en masse (SMS, e-mail) et exports',
            'tools' => ['project_config', 'crm_search', 'sms_send_bulk', 'email_send_bulk', 'export_prospects'],
            'playbook' => "Un envoi en masse est réel et irréversible, et toujours validé par l'utilisateur. Annonce le nombre exact de destinataires (crm_search, total), propose le texte avant d'envoyer, et ne cible que ceux qui ont un numéro ou une adresse. Un export est envoyé uniquement à l'utilisateur lui-même.",
        ],
        'workflow_builder' => [
            'label' => 'Créer des workflows',
            'tools' => ['project_config', 'crm_search', 'workflow_catalog', 'workflow_save_draft', 'workflow_publish', 'workflow_set_status', 'workflow_delete'],
            'playbook' => "Pour construire un workflow : lis workflow_catalog, écris le graphe, enregistre-le avec workflow_save_draft, relis les problèmes renvoyés et corrige jusqu'à ce qu'il n'y en ait plus, puis publie (workflow_publish). L'activation est toujours validée par l'utilisateur : explique avant ce que le workflow fera, à qui, et avec quels envois. N'invente jamais un opérateur : prends ceux de workflow_catalog.",
        ],
        'order_cleanup' => [
            'label' => 'Supprimer commandes et rendez-vous',
            'tools' => ['crm_search', 'order_delete', 'appointment_delete'],
            'playbook' => "Les suppressions sont toujours validées par l'utilisateur. Retrouve l'id exact avec crm_search avant de proposer.",
        ],
        'prospect_deletion' => [
            'label' => 'Supprimer des prospects',
            'tools' => ['prospect_delete'],
            'playbook' => "La suppression est toujours soumise à la validation de l'utilisateur. Compte les prospects avec crm_search avant de la proposer.",
        ],
        'appointments' => [
            'label' => 'Rendez-vous et tâches',
            'tools' => ['project_config', 'crm_search', 'appointment_create'],
            'playbook' => "Pour créer un rendez-vous ou une tâche : project_config (calendriers), crm_search sur user (à qui l'affecter) et sur prospect. Les dates sont au format AAAA-MM-JJ HH:MM ; utilise la date du jour donnée dans le contexte de l'opérateur pour résoudre « demain » ou « lundi ».",
        ],
        'communication' => [
            'label' => 'Envoyer des SMS et e-mails',
            'tools' => ['project_config', 'crm_search', 'sms_send', 'email_send'],
            'playbook' => "Un envoi est réel et irréversible : il est toujours soumis à la validation de l'utilisateur. Prépare un message court et professionnel, montre-le, et n'envoie qu'à des prospects qui ont un numéro ou une adresse (vérifie avec crm_search en demandant ces champs).",
        ],
        'reporting' => [
            'label' => 'Rapports et documents',
            'tools' => ['report_create'],
            'playbook' => "Pour un rapport, une analyse ou un plan d'action, enregistre un document avec report_create (Markdown : titres, listes, tableaux) : il s'affiche à côté de la conversation. Mets le détail dans le document et résume en quelques lignes dans ta réponse. Un document ne contient que des chiffres obtenus avec les outils du CRM.",
        ],
        'statistics_analysis' => [
            'label' => "Statistiques et performances de l'équipe",
            'tools' => ['crm_describe', 'crm_aggregate', 'team_performance', 'compare_periods'],
            'playbook' => "Pour un chiffre ou un classement, utilise crm_aggregate ou team_performance et cite les résultats tels quels : ne les recalcule pas de tête. Pour comparer deux périodes (semaine contre semaine, mois contre mois), utilise compare_periods : il renvoie les écarts, les pourcentages et les tendances déjà calculés. Les jours reviennent avec leur jour de la semaine : ne le déduis jamais d'une date de tête. Dis toujours quelle période et quelles personnes sont concernées, et signale qu'un chiffre ne couvre que les utilisateurs visibles pour l'utilisateur. Une personne à zéro appel peut être absente ou ne pas faire d'appels : ne conclus pas sur sa performance sans le dire.",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing
    |--------------------------------------------------------------------------
    |
    | US dollars per million tokens. As a price per million tokens times a
    | number of tokens is a number of millionths of dollar, the cost of a run is
    | kept in micro-dollars (agent_runs.cost_micro). The figures are indicative
    | (price list of 2026-10-06): check them against the current price list.
    | cache_read / cache_write: 0.1x and 1.25x of the input price when not given.
    |
    */

    'pricing' => [
        'claude-opus-5-5' => ['input' => 4.00, 'output' => 20.00, 'cache_read' => 0.20],
        'claude-sonnet-5-5' => ['input' => 2.00, 'output' => 10.00, 'cache_read' => 0.20],
        'claude-haiku-5-5' => ['input' => 0.10, 'output' => 0.50],
    ],

    /*
    |--------------------------------------------------------------------------
    | Run limits
    |--------------------------------------------------------------------------
    */

    // Model calls of one run
    'max_iterations' => 12,

    // Actions on many records
    'bulk_chunk' => 500,            // prospects per call of the CRM
    'bulk_max' => 20000,            // prospects per action
    'bulk_confirm_threshold' => 20, // above it, a human approves, whatever the autonomy
    'action_ttl_hours' => 24,       // an action nobody approved expires

    // Strict tool use makes the API guarantee the arguments of a tool call, but
    // it limits ALL the tools together to 24 optional parameters and refuses
    // numerical, length and size constraints. The engine validates every call
    // itself (InputValidator) and answers the model with what is wrong, so it is
    // off: turn it on only for a small set of tools.
    'strict_tools' => (bool) env('AGENTS_STRICT_TOOLS', false),

    // Rows a read tool returns at most
    'max_rows' => 50,

    // Output tokens of one model call
    'max_output_tokens' => 16000,

    // Characters of a tool result sent to the model at most: beyond it the
    // tool answers that the request must be narrowed
    'max_tool_result_chars' => 30000,

    // Characters of a message of the user
    'max_message_chars' => 8000,

    // A "running" run that was not released after this delay (killed worker)
    // is picked up again by agents:resume
    'stuck_after_minutes' => 15,

    // Days the history of the runs is kept: see agents:prune
    'retention_days' => (int) env('AGENTS_RETENTION_DAYS', 90),

];
