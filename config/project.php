<?php

return [
    // Default configuration for each type of created project
    'job_category' => [

        // Agence commerciale de prospection
        "commercial_prospecting_agency" => [
            "folders" => [
                "CIN",
                "Photos d'identité",
                "Contrat prospect",
                "Factures",
                "Liste de prospects",
            ],
            "threads" => [
                "Suivi prospect" => true,
                "Rendez-vous commerciaux" => true,
                "Feedback prospect" => true,
            ],
            "fields" => ["Montant du contrat"],
            "calendars" => ["Rendez-vous téléphonique" => "telephone", "Visite sur site" => "physical"],
            "categories" => [
                "Source de prospection" => []
            ],
            "roles" => ["Administrator" => ["all"], "Agent commercial" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ], "Superviseur" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ], "Assistant commercial" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ]],
            "messages_templates" => [
                "Rappel de rendez-vous" => "Bonjour {prospect.full_name},\nCeci est un rappel concernant votre rendez-vous.\nMerci de confirmer votre disponibilité ou de nous contacter si vous souhaitez modifier l’horaire.\nCordialement,\n{project.name}",
            ],
        ],

        // Installateur de panneaux photovoltaïques
        "solar_panel_installer" => [
            "folders" => [
                "Plan d'installation",
                "Photos du site",
                "Contrat de vente",
                "Certificats de conformité",
            ],
            "threads" => ["Suivi technique" => true, "Logistique" => true, "Support prospect" => true],
            "fields" => [
                "Puissance des panneaux",
                "Surface du toit",
            ],
            "calendars" => ["Inspection site" => "physical", "Installation" => "physical", "Maintenance" => "physical"],
            "categories" => [
                /*"Suivi" => [
                    "En attente d'installation" ,
                    "En cours d'installation" ,
                    "Maintenance requise" ,
                ],*/
                "Type d'onduleur" => [
                    "Onduleur centralisé" ,
                    "Onduleur string" ,
                    "Micro-onduleur" ,
                    "Onduleur hybride" ,
                    "Onduleur à batterie" ,
                    "Onduleur triphasé" ,
                ],
            ],
            "roles" => ["Administrator" => ["all"], "Technicien" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ], "Chargé d’affaires" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ], "Ingénieur support" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ]],
            "messages_templates" => [
                "Confirmation de l’installation" => "Bonjour {prospect.full_name},\nNous confirmons que l’installation de votre équipement est prévue pour le {date} à {time}.\nCordialement, \n{project.name}",
                "Suivi après installation" => "Bonjour {prospect.full_name},\nNous espérons que l’installation s’est bien déroulée. N’hésitez pas à nous contacter pour tout besoin d'assistance.\nCordialement, \n{project.name}",
            ],
        ],

        // Installateur de rénovation globale
        "global_renovation_installer" => [
            "folders" => [
                "Devis travaux",
                "Plan de rénovation",
                "Photos avant/après",
                "Certificats d'efficacité énergétique",
            ],
            "threads" => [
                "Suivi travaux" => true,
                "Coordination avec sous-traitants" => true,
                "Service après-vente" => true,
            ],
            "fields" => [
                "Budget prévisionnel",
                "Date de début et fin",
            ],
            "calendars" => ["Visite chantier" => "physical", "Réunion avec sous-traitants" => "physical"],
            "categories" => [
                /*"Suivi" => [
                    "En cours de travaux",
                    "Travaux terminés",
                    "En attente de devis",
                ],*/
                "Type de rénovation" => [
                    "Toiture",
                    "Fenêtres",
                ],
            ],
            "roles" => ["Administrator" => ["all"], "Conducteur de travaux" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ], "Architecte" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ], "Chef de chantier" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ]],
            "messages_templates" => [
                "Rappel de rendez-vous" => "Bonjour {prospect.full_name},\nCeci est un rappel concernant votre rendez-vous pour les travaux.\nCordialement, \n{project.name}",
            ],
        ],
        
        // Installateur de pompes à chaleur
        "heat_pump_installer" => [
            "folders" => [
                "Photos de site",
                "Contrat de vente",
                "Plan d’installation",
                "Certificats d'efficacité énergétique",
            ],
            "threads" => [
                "Suivi chantier" => true,
                "Assistance technique" => true,
                "Service après-vente" => true,
            ],
            "fields" => [
                "Puissance de la pompe",
                "Type de pompe",
                "Surface chauffée",
            ],
            "calendars" => ["Inspection initiale" => "physical", "Installation" => "physical", "Réparation" => "physical"],
            "categories" => [
                /*"Suivi" => ["En attente de pièces", "Installation prévue", "Besoin de maintenance"],*/
                "Type de pompe" => [
                    
                ]
            ],
            "roles" => ["Administrator" => ["all"], 
                "Technicien installateur" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
                "Chargé d’affaires" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
                "Chef de projet" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
            ],
            "messages_templates" => [
                "Confirmation de l’installation" => "Bonjour {prospect.full_name},\nVotre installation est confirmée pour le {date}.\nCordialement, \n{project.name}",
                "Suivi après installation" => "Bonjour {prospect.full_name},\nNous espérons que l’installation s’est bien déroulée. Contactez-nous si besoin.\nCordialement, \n{project.name}",
            ],
        ],
        
        // Transport de personnes
        "passenger_transport" => [
            "folders" => [
                "Permis de conduire",
                "Assurance",
                "Horaires de trajets",
                "Relevés de kilométrage",
            ],
            "threads" => [
                "Planification des trajets" => true,
                "Suivi véhicules" => true,
                "Relation prospect" => true,
            ],
            "fields" => ["Type de véhicule", "Capacité de transport", "Itinéraire"],
            "calendars" => [
                "Inspection véhicule" => "physical",
                "Entretien" => "physical",
                "Rendez-vous avec prospect" => "physical",
            ],
            "categories" => [
                /*"Suivi" => ["Véhicule en maintenance", "Trajet en cours", "Client VIP"],*/
                "Type de véhicule" => [
                ]
            ],
            "roles" => ["Administrator" => ["all"], "Chauffeur" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ], "Responsable logistique" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ], "Répartiteur" => [
                'all.prospect.add', 
                'all.prospect.update', 
                'all.prospect.delete', 
                'all.prospect.export', 
                'all.prospect.sms', 
                'all.prospect.interaction', 
                'all.prospect.order', 
                'all.prospect.event'
            ]],
            "messages_templates" => [
                "Confirmation de l’installation" => "Bonjour {prospect.full_name},\nVotre trajet est confirmé pour le {date}.\nCordialement, \n{project.name}",
                "Suivi après installation" => "Bonjour {prospect.full_name},\nNous espérons que votre trajet s'est bien passé. N'hésitez pas à nous contacter.\nCordialement, \n{project.name}",
            ],
        ],
        
        // Gestionnaire d'intervention terrain
        "field_intervention_manager" => [
            "folders" => [
                "Rapport d'intervention",
                "Fiche prospect",
                "Contrat de service",
            ],
            "threads" => [
                "Coordination des équipes" => true,
                "Assistance terrain" => true,
                "Suivi des incidents" => true,
            ],
            "fields" => [
                "Urgence",
                "Ressources nécessaires",
            ],
            "calendars" => [
                "Intervention programmée" => "physical",
                "Visite de site" => "physical",
                "Évaluation des besoins" => "physical",
            ],
            "categories" => [
                /*"Suivi" => ["Intervention urgente", "En attente de matériel", "Intervention terminée"],*/
                "Type d'intervention" => []
            ],
            "roles" => ["Administrator" => ["all"], 
                "Chef d’équipe terrain" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
                "Technicien" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
                "Coordinateur d’intervention" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
            ],
            "messages_templates" => [
                "Confirmation de l’installation" => "Bonjour {prospect.full_name},\nVotre intervention est confirmée pour le {date}.\nCordialement,\n{project.name}",
                "Suivi après installation" => "Bonjour {prospect.full_name},\nNous espérons que l'intervention s’est bien passée.\nCordialement,\n{project.name}",
            ],
        ],

        // Exposant salons et expositions
        "exhibition_manager" => [
            "folders" => [
                "Réservation de stand",
                "Contrats fournisseurs",
                "Matériel promotionnel",
            ],
            "threads" => [
                "Logistique événement" => true,
                "Relation fournisseurs" => true,
                "Suivi leads" => true,
            ],
            "fields" => [
                "Nombre de visiteurs attendus",
                "Surface stand",
            ],
            "calendars" => ["Réunion pré-salon" => "physical", "RDV avec prospects" => "physical"],
            "categories" => [
                /*"Suivi" => ["Lead à suivre", "Matériel prêt", "Stand en montage"],*/
                "Type de salon" => []
            ],
            "roles" => ["Administrator" => ["all"], 
                "Responsable événement" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
                "Commercial" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
                "Chef de projet logistique" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
            ],
            "messages_templates" => [
                "Confirmation de l’installation" => "Bonjour {prospect.full_name},\nVotre participation est confirmée pour le salon du {date}.\nCordialement,\n{project.name}",
                "Suivi après installation" => "Bonjour {prospect.full_name},\nMerci pour votre présence. Nous espérons que vous avez passé un bon moment au salon.\nCordialement,\n{project.name}",
            ],
        ],

        // Géolocalisation de véhicules
        "vehicle_geolocation" => [
            "folders" => [
                "Permis de conduire",
                "Historique des trajets",
                "Contrat de géolocalisation",
            ],
            "threads" => [
                "Suivi flotte" => true,
                "Maintenance véhicule" => true,
                "Relation prospect" => true,
            ],
            "fields" => [
                "Position actuelle",
                "Distance parcourue",
            ],
            "calendars" => ["Inspection véhicule" => "physical", "Maintenance" => "physical"],
            "categories" => [
                /*"Suivi" => ["Véhicule en maintenance", "En déplacement", "Hors zone"],*/
                "Type de véhicule" => []
            ],
            "roles" => ["Administrator" => ["all"], 
                "Gestionnaire flotte" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
                "Conducteur" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
                "Responsable maintenance" => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ],
            ],
            "messages_templates" => [
                "Confirmation de l’installation" => "Bonjour {prospect.full_name},\nVotre véhicule est suivi à partir du {date}.\nCordialement,\n{project.name}",
                "Suivi après installation" => "Bonjour {prospect.full_name},\nNous espérons que le suivi de votre flotte est conforme à vos attentes. Contactez-nous en cas de besoin.\nCordialement,\n{project.name}",
            ],
        ],
    ]
];