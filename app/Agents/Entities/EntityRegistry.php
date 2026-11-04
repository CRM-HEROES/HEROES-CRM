<?php

namespace App\Agents\Entities;

/**
 * Semantic layer of the CRM for the agents.
 *
 * It describes, for each entity an agent may read, what it is, how it is tied
 * to a project, which fields can be exposed and which filters and sorts exist.
 * The agents never see tables or SQL: they ask for entities and fields from
 * this registry, and the columns that are not listed here (passwords, tokens,
 * secrets of the operators, phone numbers of the calls) simply do not exist for
 * them. ReadGateway is the only code that turns it into queries.
 *
 * `scope` tells how the isolation by project is made, as the database audit
 * found it. `ability` is the CRM permission the owner needs to read the entity
 * (null: no permission of its own, the visibility rules of the CRM apply).
 *
 * Fields: `default` fields are returned when the model asks for none; the
 * others (personal data such as e-mail or phone) only when it asks for them.
 */
class EntityRegistry
{
    /**
     * @return array<string, array>
     */
    public function all(): array
    {
        return [
            'prospect' => [
                'label' => 'Prospect',
                'description' => "Fiche d'un contact ou lead du projet. C'est le centre du CRM : appels, SMS, e-mails, rendez-vous, commandes et étiquettes s'y rattachent.",
                'scope' => 'direct (le prospect appartient à un projet)',
                'ability' => null,
                'visibility' => "Sans le droit de voir tous les prospects, l'utilisateur ne voit que ceux qu'il a créés, qui lui sont assignés ou qui sont dans l'un de ses groupes.",
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'Identifiant', 'default' => true],
                    'first_name' => ['type' => 'string', 'label' => 'Prénom', 'default' => true],
                    'last_name' => ['type' => 'string', 'label' => 'Nom', 'default' => true],
                    'company_name' => ['type' => 'string', 'label' => 'Société', 'default' => true],
                    'city' => ['type' => 'string', 'label' => 'Ville', 'default' => true],
                    'processed_at' => ['type' => 'datetime', 'label' => 'Date de traitement (vide : fiche pas encore traitée)', 'default' => true],
                    'created_at' => ['type' => 'datetime', 'label' => 'Date de création', 'default' => true],
                    'job_title' => ['type' => 'string', 'label' => 'Fonction'],
                    'email' => ['type' => 'string', 'label' => 'E-mail (donnée personnelle)'],
                    'phone_number' => ['type' => 'string', 'label' => 'Téléphone (donnée personnelle)'],
                    'mobile_phone_number' => ['type' => 'string', 'label' => 'Mobile (donnée personnelle)'],
                    'postal_code' => ['type' => 'string', 'label' => 'Code postal'],
                    'country' => ['type' => 'string', 'label' => 'Pays'],
                    'updated_at' => ['type' => 'datetime', 'label' => 'Dernière modification'],
                    'created_by_user_id' => ['type' => 'integer', 'label' => 'Utilisateur créateur'],
                ],
                'filters' => [
                    'query' => ['type' => 'string', 'label' => 'Recherche texte (nom, société, e-mail, téléphone, ville)'],
                    'created_after' => ['type' => 'date', 'label' => 'Créé à partir du (AAAA-MM-JJ)'],
                    'created_before' => ['type' => 'date', 'label' => "Créé jusqu'au (AAAA-MM-JJ)"],
                    'updated_after' => ['type' => 'date', 'label' => 'Modifié à partir du'],
                    'updated_before' => ['type' => 'date', 'label' => "Modifié jusqu'au"],
                    'processed' => ['type' => 'boolean', 'label' => 'true : fiches traitées, false : non traitées'],
                    'assigned_user_id' => ['type' => 'integer', 'label' => 'Assigné à cet utilisateur'],
                    'group_id' => ['type' => 'integer', 'label' => 'Dans ce groupe'],
                    'label_id' => ['type' => 'integer', 'label' => 'Avec cette étiquette'],
                    'without_label_id' => ['type' => 'integer', 'label' => "Sans cette étiquette"],
                    'not_contacted_since_days' => ['type' => 'integer', 'label' => "Aucun contact sortant (appel, SMS ou e-mail envoyé par un utilisateur) depuis ce nombre de jours, ou jamais contacté"],
                    'is_duplicate' => ['type' => 'boolean', 'label' => "true : fiches marquées comme doublon d'une autre (après duplicates_detect)"],
                    'never_contacted' => ['type' => 'boolean', 'label' => 'true : jamais contacté (aucun appel, SMS ou e-mail sortant)'],
                    'created_by_user_id' => ['type' => 'integer', 'label' => 'Créé par cet utilisateur'],
                    'city' => ['type' => 'string', 'label' => 'Ville exacte'],
                    'country' => ['type' => 'string', 'label' => 'Pays exact'],
                ],
                'sorts' => ['id', 'created_at', 'updated_at', 'last_name', 'processed_at'],
                'default_sort' => ['created_at', 'desc'],
                'relations' => [
                    'appels (entité call)', 'SMS (sms)', 'rendez-vous (appointment)', 'commandes (order)',
                ],
                'rules' => [
                    'Une fiche traitée ne peut plus être modifiée.',
                    "Les champs personnalisés du projet (colonne meta) ne sont pas encore exposés.",
                ],
            ],

            'user' => [
                'label' => 'Utilisateur',
                'description' => "Membre du projet (téléconseiller, commercial, administrateur...). Utile pour savoir qui est dans l'équipe, avant d'analyser l'activité.",
                'scope' => 'via la liaison utilisateur-projet',
                'ability' => null,
                'visibility' => "Un administrateur voit toute l'équipe ; les autres voient seulement eux-mêmes et les utilisateurs qu'ils ont le droit d'assigner.",
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'Identifiant', 'default' => true],
                    'name' => ['type' => 'string', 'label' => 'Prénom', 'default' => true],
                    'last_name' => ['type' => 'string', 'label' => 'Nom', 'default' => true],
                    'roles' => ['type' => 'string', 'label' => 'Rôles dans ce projet', 'default' => true],
                    'last_activity' => ['type' => 'datetime', 'label' => 'Dernière activité', 'default' => true],
                    'email' => ['type' => 'string', 'label' => 'E-mail (donnée personnelle)'],
                ],
                'filters' => [
                    'query' => ['type' => 'string', 'label' => 'Recherche texte (nom, e-mail)'],
                ],
                'sorts' => ['id', 'name', 'last_activity'],
                'default_sort' => ['name', 'asc'],
                'relations' => [],
                'rules' => [],
            ],

            'call' => [
                'label' => 'Appel',
                'description' => "Appel téléphonique lié à un prospect (entrant ou sortant, enregistré par l'opérateur de téléphonie).",
                'scope' => 'via le prospect',
                'ability' => 'prospectInteractionView',
                'visibility' => "Seuls les appels des prospects que l'utilisateur peut voir. Les numéros de téléphone ne sont pas exposés.",
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'Identifiant', 'default' => true],
                    'prospect_id' => ['type' => 'integer', 'label' => 'Prospect', 'default' => true],
                    'user_id' => ['type' => 'integer', 'label' => 'Utilisateur qui a appelé ou reçu', 'default' => true],
                    'direction' => ['type' => 'string', 'label' => "outgoing (sortant) ou incoming (entrant)", 'default' => true],
                    'status' => ['type' => 'string', 'label' => 'Statut (answered, completed, ended = abouti)', 'default' => true],
                    'source' => ['type' => 'string', 'label' => "Opérateur (cloudtalk, kavkom...)", 'default' => true],
                    'started_at' => ['type' => 'datetime', 'label' => 'Début', 'default' => true],
                    'duration_seconds' => ['type' => 'integer', 'label' => 'Durée en secondes', 'default' => true],
                ],
                'filters' => [
                    'prospect_id' => ['type' => 'integer', 'label' => 'Appels de ce prospect'],
                    'user_id' => ['type' => 'integer', 'label' => 'Appels de cet utilisateur'],
                    'direction' => ['type' => 'string', 'label' => 'outgoing ou incoming', 'enum' => ['outgoing', 'incoming']],
                    'status' => ['type' => 'string', 'label' => 'Statut exact'],
                    'source' => ['type' => 'string', 'label' => 'Opérateur exact'],
                    'started_after' => ['type' => 'date', 'label' => 'À partir du'],
                    'started_before' => ['type' => 'date', 'label' => "Jusqu'au"],
                ],
                'sorts' => ['id', 'started_at'],
                'default_sort' => ['started_at', 'desc'],
                'relations' => [],
                'rules' => [],
            ],

            'sms' => [
                'label' => 'SMS',
                'description' => "SMS envoyé à un prospect ou reçu de lui.",
                'scope' => 'via le prospect',
                'ability' => 'prospectSmsView',
                'visibility' => "Seuls les SMS des prospects que l'utilisateur peut voir. Le texte est tronqué à 300 caractères.",
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'Identifiant', 'default' => true],
                    'prospect_id' => ['type' => 'integer', 'label' => 'Prospect', 'default' => true],
                    'user_id' => ['type' => 'integer', 'label' => 'Utilisateur qui a envoyé', 'default' => true],
                    'direction' => ['type' => 'string', 'label' => 'sent (envoyé) ou received (reçu)', 'default' => true],
                    'source' => ['type' => 'string', 'label' => 'Passerelle SMS', 'default' => true],
                    'sent_at' => ['type' => 'datetime', 'label' => "Date d'envoi", 'default' => true],
                    'message' => ['type' => 'string', 'label' => 'Texte (donnée personnelle, tronqué)'],
                ],
                'filters' => [
                    'prospect_id' => ['type' => 'integer', 'label' => 'SMS de ce prospect'],
                    'user_id' => ['type' => 'integer', 'label' => 'SMS de cet utilisateur'],
                    'direction' => ['type' => 'string', 'label' => 'sent ou received', 'enum' => ['sent', 'received']],
                    'source' => ['type' => 'string', 'label' => 'Passerelle exacte'],
                    'sent_after' => ['type' => 'date', 'label' => 'À partir du'],
                    'sent_before' => ['type' => 'date', 'label' => "Jusqu'au"],
                ],
                'sorts' => ['id', 'sent_at'],
                'default_sort' => ['sent_at', 'desc'],
                'relations' => [],
                'rules' => [],
            ],

            'appointment' => [
                'label' => 'Rendez-vous',
                'description' => "Rendez-vous ou tâche de l'agenda, rattaché à un calendrier du projet et éventuellement à un prospect.",
                'scope' => 'via le calendrier',
                'ability' => 'prospectEventView',
                'visibility' => "Seuls les calendriers que l'utilisateur peut voir, et les rendez-vous de prospects qu'il peut voir.",
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'Identifiant', 'default' => true],
                    'name' => ['type' => 'string', 'label' => 'Titre', 'default' => true],
                    'calendar' => ['type' => 'string', 'label' => 'Calendrier', 'default' => true],
                    'prospect_id' => ['type' => 'integer', 'label' => 'Prospect (vide : tâche)', 'default' => true],
                    'user_id' => ['type' => 'integer', 'label' => 'Utilisateur concerné', 'default' => true],
                    'started_at' => ['type' => 'datetime', 'label' => 'Début', 'default' => true],
                    'ended_at' => ['type' => 'datetime', 'label' => 'Fin', 'default' => true],
                    'done' => ['type' => 'boolean', 'label' => 'Réalisé', 'default' => true],
                    'confirmed' => ['type' => 'boolean', 'label' => 'Confirmé'],
                    'location' => ['type' => 'string', 'label' => 'Lieu'],
                ],
                'filters' => [
                    'calendar_id' => ['type' => 'integer', 'label' => 'Dans ce calendrier'],
                    'prospect_id' => ['type' => 'integer', 'label' => 'Rendez-vous de ce prospect'],
                    'user_id' => ['type' => 'integer', 'label' => 'Concernant cet utilisateur'],
                    'done' => ['type' => 'boolean', 'label' => 'Réalisés (true) ou à faire (false)'],
                    'confirmed' => ['type' => 'boolean', 'label' => 'Confirmés ou non'],
                    'starts_after' => ['type' => 'date', 'label' => 'À partir du'],
                    'starts_before' => ['type' => 'date', 'label' => "Jusqu'au"],
                ],
                'sorts' => ['id', 'started_at'],
                'default_sort' => ['started_at', 'desc'],
                'relations' => [],
                'rules' => [],
            ],

            'order' => [
                'label' => 'Commande / devis',
                'description' => "Commande ou devis d'un prospect, avec son statut.",
                'scope' => 'via le prospect',
                'ability' => 'prospectOrderView',
                'visibility' => "Seulement les commandes des prospects que l'utilisateur peut voir.",
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'Identifiant', 'default' => true],
                    'number' => ['type' => 'integer', 'label' => 'Numéro', 'default' => true],
                    'name' => ['type' => 'string', 'label' => 'Intitulé', 'default' => true],
                    'prospect_id' => ['type' => 'integer', 'label' => 'Prospect', 'default' => true],
                    'status' => ['type' => 'string', 'label' => 'Statut', 'default' => true],
                    'user_id' => ['type' => 'integer', 'label' => 'Utilisateur qui l\'a créée', 'default' => true],
                    'created_at' => ['type' => 'datetime', 'label' => 'Date de création', 'default' => true],
                ],
                'filters' => [
                    'prospect_id' => ['type' => 'integer', 'label' => 'Commandes de ce prospect'],
                    'user_id' => ['type' => 'integer', 'label' => 'Créées par cet utilisateur'],
                    'status' => ['type' => 'string', 'label' => 'Statut exact (nom)'],
                    'created_after' => ['type' => 'date', 'label' => 'À partir du'],
                    'created_before' => ['type' => 'date', 'label' => "Jusqu'au"],
                ],
                'sorts' => ['id', 'created_at'],
                'default_sort' => ['created_at', 'desc'],
                'relations' => [],
                'rules' => [],
            ],

            'email' => [
                'label' => 'E-mail',
                'description' => "E-mail échangé avec un prospect, rangé dans un fil de discussion du projet (envoyé par l'équipe ou reçu).",
                'scope' => 'via le prospect et le fil de discussion (le fil appartient au projet)',
                'ability' => 'projectThreadView',
                'visibility' => "Seulement les e-mails des prospects que l'utilisateur peut voir, dans les fils auxquels il a accès (un fil privé reste privé).",
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'Identifiant', 'default' => true],
                    'prospect_id' => ['type' => 'integer', 'label' => 'Prospect', 'default' => true],
                    'thread' => ['type' => 'string', 'label' => 'Fil de discussion', 'default' => true],
                    'direction' => ['type' => 'string', 'label' => 'sent (envoyé) ou received (reçu)', 'default' => true],
                    'sent' => ['type' => 'boolean', 'label' => 'Bien parti', 'default' => true],
                    'preview' => ['type' => 'string', 'label' => 'Début du texte (300 caractères)', 'default' => true],
                    'created_at' => ['type' => 'datetime', 'label' => 'Date', 'default' => true],
                    'user_id' => ['type' => 'integer', 'label' => "Utilisateur qui l'a écrit"],
                ],
                'filters' => [
                    'prospect_id' => ['type' => 'integer', 'label' => 'E-mails de ce prospect'],
                    'thread_id' => ['type' => 'integer', 'label' => 'Dans ce fil (project_config > threads)'],
                    'direction' => ['type' => 'string', 'label' => 'sent ou received', 'enum' => ['sent', 'received']],
                    'user_id' => ['type' => 'integer', 'label' => 'Écrits par cet utilisateur'],
                    'query' => ['type' => 'string', 'label' => 'Texte contenu dans le message'],
                    'created_after' => ['type' => 'date', 'label' => 'À partir du'],
                    'created_before' => ['type' => 'date', 'label' => "Jusqu'au"],
                ],
                'sorts' => ['id', 'created_at'],
                'default_sort' => ['created_at', 'desc'],
                'relations' => [],
                'rules' => [],
            ],

            'workflow' => [
                'label' => 'Workflow (automatisation)',
                'description' => "Automatisation du projet : un déclencheur (prospect créé, étiquette, rendez-vous...) puis des étapes (SMS, e-mail, appel, attentes, conditions). Son statut dit s'il tourne.",
                'scope' => 'direct (le workflow appartient à un projet)',
                'ability' => 'projectWorkflowView',
                'visibility' => 'Tous les workflows du projet (non supprimés).',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'Identifiant', 'default' => true],
                    'name' => ['type' => 'string', 'label' => 'Nom', 'default' => true],
                    'status' => ['type' => 'string', 'label' => 'draft (brouillon), active, paused, archived', 'default' => true],
                    'description' => ['type' => 'string', 'label' => 'Description', 'default' => true],
                    'created_at' => ['type' => 'datetime', 'label' => 'Création', 'default' => true],
                    'updated_at' => ['type' => 'datetime', 'label' => 'Dernière modification'],
                ],
                'filters' => [
                    'status' => ['type' => 'string', 'label' => 'Statut exact', 'enum' => ['draft', 'active', 'paused', 'archived']],
                    'query' => ['type' => 'string', 'label' => 'Recherche dans le nom'],
                ],
                'sorts' => ['id', 'name', 'created_at', 'updated_at'],
                'default_sort' => ['updated_at', 'desc'],
                'relations' => [],
                'rules' => ['Pour activer ou mettre en pause un workflow : outil workflow_set_status.'],
            ],

            'campaign' => [
                'label' => 'Campagne (ancien moteur)',
                'description' => "Campagne d'actions automatiques sur des prospects, selon des règles et une fréquence. Les nouveaux travaux passent par les workflows.",
                'scope' => 'direct (la campagne appartient à un projet)',
                'ability' => 'projectCampaignView',
                'visibility' => 'Toutes les campagnes du projet.',
                'fields' => [
                    'id' => ['type' => 'integer', 'label' => 'Identifiant', 'default' => true],
                    'name' => ['type' => 'string', 'label' => 'Nom', 'default' => true],
                    'active' => ['type' => 'boolean', 'label' => 'Active', 'default' => true],
                    'frequency' => ['type' => 'string', 'label' => 'Fréquence (once, hourly, daily, monthly...)', 'default' => true],
                    'for' => ['type' => 'string', 'label' => 'Objet visé (prospect, order, event)', 'default' => true],
                    'begin_at' => ['type' => 'datetime', 'label' => 'Début'],
                    'end_at' => ['type' => 'datetime', 'label' => 'Fin'],
                    'error' => ['type' => 'boolean', 'label' => 'En erreur'],
                ],
                'filters' => [
                    'active' => ['type' => 'boolean', 'label' => 'true : actives, false : inactives'],
                    'query' => ['type' => 'string', 'label' => 'Recherche dans le nom'],
                ],
                'sorts' => ['id', 'name'],
                'default_sort' => ['id', 'desc'],
                'relations' => [],
                'rules' => [],
            ],
        ];
    }

    public function has(string $entity): bool
    {
        return isset($this->all()[$entity]);
    }

    public function get(string $entity): ?array
    {
        return $this->all()[$entity] ?? null;
    }

    /**
     * @return string[]
     */
    public function keys(): array
    {
        return array_keys($this->all());
    }

    /**
     * Default fields of an entity
     *
     * @return string[]
     */
    public function defaultFields(string $entity): array
    {
        return array_keys(array_filter($this->all()[$entity]['fields'] ?? [], fn ($field) => !empty($field['default'])));
    }
}
