<?php

namespace App\Utils\Stat\Builder;

/**
 * Registre déclaratif des sources de données du Dashboard Builder.
 *
 * Pour ajouter une nouvelle source : ajouter une entrée dans `all()`.
 * - kind "events"  : table de faits agrégeable (dimensions + mesures SQL)
 * - kind "users"   : une ligne par utilisateur du projet (champs calculés)
 *
 * Aucun code front n'est à modifier : le catalogue est exposé tel quel au wizard.
 */
class Sources
{
    /**
     * Dimensions temporelles communes à toutes les sources "events".
     * `{d}` est remplacé par l'expression SQL de la date de la source.
     */
    protected static function timeDimensions(): array
    {
        return [
            'day' => ['label' => "Jour", 'type' => 'date', 'expr' => "DATE({d})"],
            'week' => ['label' => "Semaine", 'type' => 'date', 'expr' => "DATE_FORMAT({d}, '%x-S%v')"],
            'month' => ['label' => "Mois", 'type' => 'date', 'expr' => "DATE_FORMAT({d}, '%Y-%m')"],
            'weekday' => ['label' => "Jour de la semaine", 'type' => 'string', 'expr' => "WEEKDAY({d})", 'labels' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']],
            'hour' => ['label' => "Heure", 'type' => 'string', 'expr' => "HOUR({d})", 'suffix' => 'h'],
        ];
    }

    protected static function userDimension(string $col): array
    {
        return [
            'label' => "Utilisateur",
            'type' => 'string',
            'expr' => $col,
            'lookup' => 'users',
        ];
    }

    public static function all(): array
    {
        $time = static::timeDimensions();

        return [
            'interactions' => [
                'kind' => 'events',
                'label' => "Appels",
                'icon' => 'fa-phone',
                'table' => 'interactions',
                'alias' => 't',
                'join' => "JOIN prospects p ON p.id = t.prospect_id",
                'project_col' => 'p.project_id',
                'date' => "COALESCE(t.started_at, t.created_at)",
                'user_col' => 't.creator_id',
                'dimensions' => $time + [
                    'user' => static::userDimension('t.creator_id'),
                    'source' => ['label' => "Source", 'type' => 'string', 'expr' => 't.source'],
                    'status' => ['label' => "Statut", 'type' => 'string', 'expr' => 't.status'],
                    'direction' => ['label' => "Direction", 'type' => 'string', 'expr' => "IF(t.from_user = 1, 'Sortant', 'Entrant')"],
                ],
                'measures' => [
                    'records' => ['label' => "Nombre d'appels", 'aggs' => ['count'], 'default' => 'count'],
                    'duration' => ['label' => "Durée (min)", 'aggs' => ['sum', 'avg', 'min', 'max'], 'default' => 'sum', 'expr' => "(TIMESTAMPDIFF(SECOND, t.started_at, t.ended_at) / 60)", 'format' => 'minutes'],
                ],
            ],
            'sms' => [
                'kind' => 'events',
                'label' => "SMS",
                'icon' => 'fa-comment',
                'table' => 'sms',
                'alias' => 't',
                'join' => "JOIN prospects p ON p.id = t.prospect_id",
                'project_col' => 'p.project_id',
                'date' => "COALESCE(t.sent_at, t.created_at)",
                'user_col' => 't.creator_id',
                'dimensions' => $time + [
                    'user' => static::userDimension('t.creator_id'),
                    'source' => ['label' => "Source", 'type' => 'string', 'expr' => 't.source'],
                    'direction' => ['label' => "Direction", 'type' => 'string', 'expr' => "IF(t.from_user = 1, 'Envoyé', 'Reçu')"],
                ],
                'measures' => [
                    'records' => ['label' => "Nombre de SMS", 'aggs' => ['count'], 'default' => 'count'],
                ],
            ],
            'orders' => [
                'kind' => 'events',
                'label' => "Commandes",
                'icon' => 'fa-shopping-bag',
                'table' => 'orders',
                'alias' => 't',
                'join' => "JOIN prospects p ON p.id = t.prospect_id",
                'where' => "t.deleted_at IS NULL",
                'project_col' => 'p.project_id',
                'date' => "t.created_at",
                'user_col' => 't.creator_id',
                'dimensions' => $time + [
                    'user' => static::userDimension('t.creator_id'),
                    'status' => ['label' => "Statut", 'type' => 'string', 'expr' => "COALESCE((SELECT s.name FROM order_status s WHERE s.id = t.status_id), 'Sans statut')"],
                ],
                'measures' => [
                    'records' => ['label' => "Nombre de commandes", 'aggs' => ['count'], 'default' => 'count'],
                ],
            ],
            'prospects' => [
                'kind' => 'events',
                'label' => "Prospects",
                'icon' => 'fa-users',
                'table' => 'prospects',
                'alias' => 't',
                'join' => "",
                'project_col' => 't.project_id',
                'date' => "t.created_at",
                'user_col' => 't.creator_id',
                'dimensions' => $time + [
                    'user' => static::userDimension('t.creator_id'),
                    'country' => ['label' => "Pays", 'type' => 'string', 'expr' => "COALESCE(NULLIF(t.country, ''), 'Inconnu')"],
                ],
                'measures' => [
                    'records' => ['label' => "Nombre de prospects", 'aggs' => ['count'], 'default' => 'count'],
                ],
            ],
            'events' => [
                'kind' => 'events',
                'label' => "Rendez-vous",
                'icon' => 'fa-calendar',
                'table' => 'events',
                'alias' => 't',
                'join' => "JOIN calendars c ON c.id = t.calendar_id",
                'where' => "t.deleted_at IS NULL",
                'project_col' => 'c.project_id',
                'date' => "COALESCE(t.started_at, t.created_at)",
                'user_col' => 't.user_id',
                'dimensions' => $time + [
                    'user' => static::userDimension('t.user_id'),
                    'calendar' => ['label' => "Calendrier", 'type' => 'string', 'expr' => "c.name"],
                    'done' => ['label' => "Réalisé", 'type' => 'string', 'expr' => "IF(t.done = 1, 'Réalisé', 'À faire')"],
                ],
                'measures' => [
                    'records' => ['label' => "Nombre de rendez-vous", 'aggs' => ['count'], 'default' => 'count'],
                ],
            ],
            'users' => [
                'kind' => 'users',
                'label' => "Utilisateurs",
                'icon' => 'fa-user',
                'fields' => [
                    'name' => ['label' => "Utilisateur", 'type' => 'string', 'sortable' => true],
                    'email' => ['label' => "Email", 'type' => 'string', 'sortable' => true],
                    'role' => ['label' => "Rôle", 'type' => 'string', 'sortable' => true],
                    'last_login' => ['label' => "Dernière connexion", 'type' => 'datetime', 'sortable' => true],
                    'connection_status' => ['label' => "Statut de connexion", 'type' => 'status', 'sortable' => true],
                    'calls_count' => ['label' => "Appels", 'type' => 'number', 'sortable' => true],
                    'call_duration' => ['label' => "Durée", 'type' => 'number', 'format' => 'minutes', 'sortable' => true, 'description' => "Durée totale des appels (minutes)"],
                    'sms_count' => ['label' => "SMS", 'type' => 'number', 'sortable' => true],
                    'orders_count' => ['label' => "Commandes", 'type' => 'number', 'sortable' => true],
                    'prospects_count' => ['label' => "Prospects", 'type' => 'number', 'sortable' => true],
                    'followup' => ['label' => "Suivi", 'type' => 'number', 'sortable' => true, 'description' => "Nombre d'ajouts du suivi effectués par l'utilisateur pendant la période"],
                    'recording' => ['label' => "Écouter", 'type' => 'audio_action', 'sortable' => false, 'description' => "Dernier enregistrement d'appel"],
                    'performance' => ['label' => "Performance", 'type' => 'percentage', 'sortable' => true, 'description' => "Taux d'appels aboutis"],
                ],
                'default_columns' => ['name', 'role', 'connection_status', 'call_duration', 'performance'],
                // Dimensions/mesures pour les graphiques agrégés sur les utilisateurs
                'dimensions' => [
                    'name' => ['label' => "Utilisateur"],
                    'role' => ['label' => "Rôle"],
                    'connection_status' => ['label' => "Statut de connexion"],
                ],
                'measures' => [
                    'records' => ['label' => "Nombre d'utilisateurs", 'aggs' => ['count'], 'default' => 'count'],
                    'calls_count' => ['label' => "Appels", 'aggs' => ['sum', 'avg', 'min', 'max'], 'default' => 'sum'],
                    'call_duration' => ['label' => "Durée (min)", 'aggs' => ['sum', 'avg', 'min', 'max'], 'default' => 'sum', 'format' => 'minutes'],
                    'sms_count' => ['label' => "SMS", 'aggs' => ['sum', 'avg', 'min', 'max'], 'default' => 'sum'],
                    'orders_count' => ['label' => "Commandes", 'aggs' => ['sum', 'avg', 'min', 'max'], 'default' => 'sum'],
                    'performance' => ['label' => "Performance (%)", 'aggs' => ['avg', 'min', 'max'], 'default' => 'avg', 'format' => 'percentage'],
                ],
            ],
        ];
    }

    /**
     * Version publique (sans SQL) pour le wizard.
     */
    public static function publicCatalog(): array
    {
        $out = [];

        foreach (static::all() as $key => $s) {
            $item = [
                'key' => $key,
                'kind' => $s['kind'],
                'label' => $s['label'],
                'icon' => $s['icon'],
                'dimensions' => [],
                'measures' => [],
                'fields' => [],
            ];

            foreach ($s['dimensions'] as $dk => $d) {
                $item['dimensions'][] = [
                    'key' => $dk,
                    'label' => $d['label'],
                    'type' => $d['type'] ?? 'string',
                ];
            }

            foreach ($s['measures'] as $mk => $m) {
                $item['measures'][] = [
                    'key' => $mk,
                    'label' => $m['label'],
                    'aggs' => $m['aggs'],
                    'default' => $m['default'],
                    'format' => $m['format'] ?? null,
                ];
            }

            if ($s['kind'] === 'users') {
                foreach ($s['fields'] as $fk => $f) {
                    $item['fields'][] = ['key' => $fk] + $f;
                }
                $item['default_columns'] = $s['default_columns'];
            } else {
                // Une source "events" n'expose en tableau que ses dimensions + mesures
                foreach ($s['dimensions'] as $dk => $d) {
                    $item['fields'][] = ['key' => $dk, 'label' => $d['label'], 'type' => $d['type'] ?? 'string', 'sortable' => true];
                }
                foreach ($s['measures'] as $mk => $m) {
                    $item['fields'][] = [
                        'key' => $mk,
                        'label' => $m['label'],
                        'type' => 'number',
                        'format' => $m['format'] ?? null,
                        'sortable' => true,
                        'measure' => true,
                    ];
                }
            }

            $out[] = $item;
        }

        return $out;
    }
}
