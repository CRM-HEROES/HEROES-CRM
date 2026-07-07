<?php

return [

    'prospect-profile' => [
        [
            [
                "name" => "Profil",
                "type" => "field",
                "items" => [
                    "last_name",
                    "first_name",
                    "company_name",
                    "title"
                ]
            ],
            [
                "name" => "Contact",
                "type" => "field",
                "items" => [
                    "street",
                    "website_url",
                    "email",
                    "mobile_phone_number",
                    "phone_number"
                ]
            ],
            [
                "name" => "Coordonnées",
                "type" => "field",
                "items" => [
                    "street_bis",
                    "state",
                    "county",
                    "city",
                    "postal_code",
                    "country",
                    "latitude",
                    "longitude"
                ]
            ],
            [
                "name" => "Liens externes",
                "type" => "link"
            ]
        ],
        [
            [
                "name" => "RDV",
                "type" => "event"
            ],
            [
                "name" => "Commandes",
                "type" => "order"
            ]
        ],
        [
            [
                "name" => "SMS",
                "type" => "sms"
            ],
            [
                "name" => "Appel",
                "type" => "interaction"
            ],
            [
                "name" => "Document",
                "type" => "document"
            ],
        ],
        [
            [
                "name" => "Groupes affectés",
                "type" => "group"
            ],
            [
                "name" => "Utilisateurs affectés",
                "type" => "user"
            ],
        ]
    ],

    'prospects-table' => [
        ['key' => "last_name"],
        ['key' => "first_name"],
        ['key' => "email"],
        ['key' => "street"],
        ['key' => "street_bis"],
        ['key' => "postal_code"],
        ['key' => "city"],
        ['key' => "country"],
    ],

    'users-table' => [
        ['key' => "name"],
        ['key' => "last_name"],
        ['key' => "email"],
        ['key' => "street"],
        ['key' => "street_bis"],
        ['key' => "postal_code"],
        ['key' => "city"],
        ['key' => "country"],
        ['key' => "calendars"],
        ['key' => "categories"],
        ['key' => "documents"],
        ['key' => "folders"],
        ['key' => "groups"],
        ['key' => "menus"],
        ['key' => "order_actions"],
        ['key' => "order_steps"],
        ['key' => "roles"],
        ['key' => "threads"],
        ['key' => "users"],
        ['key' => "projects"],
    ],

    'global-users-table' => [
        ['key' => "name"],
        ['key' => "last_name"],
        ['key' => "email"],
        ['key' => "street"],
        ['key' => "street_bis"],
        ['key' => "postal_code"],
        ['key' => "city"],
        ['key' => "country"],
        ['key' => "projects"],
        ['key' => "ip_address"],
        ['key' => "last_activity"],
        ['key' => "last-project"],
    ],

    'projects-table' => [
        ['key' => "name"],
        ['key' => "email"],
        ['key' => "street"],
        ['key' => "street_bis"],
        ['key' => "postal_code"],
        ['key' => "city"],
        ['key' => "country"],
        ['key' => "active-users"],
    ],

    'stat-bloc-value' => [
        ['key' => "prospects", 'name' => "Prospects", 'color' => "#FFFFFF", 'bgcolor' => "#83c7d4"],
        ['key' => "interactions", 'name' => "Appels", 'color' => "#FFFFFF", 'bgcolor' => "#8979ff"],
        ['key' => "interactions-duration", 'name' => "Durée des appels", 'color' => "#FFFFFF", 'bgcolor' => "#8979ff"],
        ['key' => "orders", 'name' => "Devis", 'color' => "#FFFFFF", 'bgcolor' => "#ff8979"],
        ['key' => "orders-total", 'name' => "Total des devis", 'color' => "#FFFFFF", 'bgcolor' => "#ff8979"],
        ['key' => "sms", 'name' => "SMS", 'color' => "#FFFFFF", 'bgcolor' => "#83d496"],
        ['key' => "events", 'name' => "RDVs", 'color' => "#FFFFFF", 'bgcolor' => "#ffae4c"],
        ['key' => "files", 'name' => "Fichiers", 'color' => "#FFFFFF", 'bgcolor' => "#ffae4c"],
        ['key' => "messages", 'name' => "Messages", 'color' => "#FFFFFF", 'bgcolor' => "#ffae4c"],
    ],

    'stat-bloc-chart' => [
        ['key' => "orders", 'name' => "Devis", 'bgcolor' => "#83c7d4", 'type' => "vbar"],
        ['key' => "orders-total", 'name' => "Total des devis", 'bgcolor' => "#83c7d4", 'type' => "vbar"],
        ['key' => "prospects", 'name' => "Prospects", 'bgcolor' => "#83c7d4", 'type' => "vbar"],
        ['key' => "interactions", 'name' => "Appels", 'bgcolor' => "#8979ff", 'type' => "vbar"],
        ['key' => "interactions-duration", 'name' => "Durée des appels", 'bgcolor' => "#8979ff", 'type' => "vbar"],
        ['key' => "sms", 'name' => "SMS", 'bgcolor' => "#83d496", 'type' => "vbar"],
        ['key' => "events", 'name' => "RDVs", 'bgcolor' => "#ffae4c", 'type' => "vbar"],
        ['key' => "files", 'name' => "Fichiers", 'bgcolor' => "#ffae4c", 'type' => "vbar"],
        ['key' => "messages", 'name' => "Messages", 'bgcolor' => "#ffae4c", 'type' => "vbar"],
    ],

];
