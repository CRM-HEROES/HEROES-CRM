export default {
    key: "all",
    name: "Toutes les permissions",
    sub: [
        {
            key: "project",
            name: "Projet",
            icon: "fa-file",
            sub: [
                {
                    key: "calendar",
                    name: "Type de RDV",
                    icon: "fa-calendar",
                    sub: [
                        {
                            key: "affected",
                            name: "Types de RDV affectés",
                            icon: "fa-check",
                            action: "role-manage-calendars",
                        },
                        {
                            key: "add",
                            name: "Ajouter un type de RDV",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier un type de RDV",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un type de RDV",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "campaign",
                    name: "Campagne",
                    icon: "fa-bullhorn",
                    sub: [
                        {
                            key: "add",
                            name: "Ajouter une campagne",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier une campagne",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer une campagne",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "category",
                    name: "Catégorie de filtre",
                    icon: "fa-tags",
                    sub: [
                        {
                            key: "affected",
                            name: "Catégories de filtre affectées",
                            icon: "fa-check",
                            action: "role-manage-categories",
                        },
                        {
                            key: "add",
                            name: "Ajouter une catégorie de filtre",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier une catégorie de filtre",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer une catégorie de filtre",
                            icon: "fa-trash",
                        },
                        {
                            key: "label",
                            name: "Gérer les filtres",
                            icon: "fa-tag",
                            sub: [
                                {
                                    key: "add",
                                    name: "Ajouter un filtre",
                                    icon: "fa-plus",
                                },
                                {
                                    key: "update",
                                    name: "Modifier un filtre",
                                    icon: "fa-pen",
                                },
                                {
                                    key: "delete",
                                    name: "Supprimer un filtre",
                                    icon: "fa-trash",
                                },
                            ],
                        },
                    ],
                },
                {
                    key: "document",
                    name: "Document",
                    icon: "fa-file-pdf",
                    sub: [
                        {
                            key: "affected",
                            name: "Documents affectés",
                            icon: "fa-check",
                            action: "role-manage-documents",
                        },
                        {
                            key: "add",
                            name: "Ajouter un document",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier un document",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un document",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "field",
                    name: "Champ de prospect",
                    icon: "fa-columns",
                    sub: [
                        {
                            key: "add",
                            name: "Ajouter un champ de prospect",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier un champ de prospect",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un champ de prospect",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "folder",
                    name: "Dossier",
                    icon: "fa-folder",
                    sub: [
                        {
                            key: "affected",
                            name: "Dossiers affectés",
                            icon: "fa-check",
                            action: "role-manage-folders",
                        },
                        {
                            key: "add",
                            name: "Ajouter un dossier",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier un dossier",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un dossier",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "group",
                    name: "Groupe",
                    icon: "fa-users",
                    sub: [
                        {
                            key: "affected",
                            name: "Groupes affectés",
                            icon: "fa-check",
                            action: "role-manage-groups",
                        },
                        {
                            key: "add",
                            name: "Ajouter un groupe",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier un groupe",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un groupe",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "menu",
                    name: "Menu",
                    icon: "fa-list",
                    sub: [
                        {
                            key: "add",
                            name: "Ajouter un menu",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier un menu",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un menu",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "message-template",
                    name: "Modèle de message",
                    icon: "fa-envelope",
                    sub: [
                        {
                            key: "add",
                            name: "Ajouter un modèle de message",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier un modèle de message",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un modèle de message",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "questionnaire",
                    name: "Questionnaire",
                    icon: "fa-clipboard",
                    sub: [
                        {
                            key: "affected",
                            name: "Questionnaires affectés",
                            icon: "fa-check",
                            action: "role-manage-questionnaires",
                        },
                        {
                            key: "add",
                            name: "Ajouter un questionnaire",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier un questionnaire",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un questionnaire",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "role",
                    name: "Rôle",
                    icon: "fa-user-tie",
                    sub: [
                        {
                            key: "affected",
                            name: "Rôles affectés",
                            icon: "fa-check",
                            action: "role-manage-roles",
                        },
                        {
                            key: "add",
                            name: "Ajouter un rôle",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier un rôle",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un rôle",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "sms-template",
                    name: "Modèle de SMS",
                    icon: "fa-comment",
                    sub: [
                        {
                            key: "add",
                            name: "Ajouter un modèle de SMS",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier un modèle de SMS",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un modèle de SMS",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "thread",
                    name: "Canal de discussion",
                    icon: "fa-envelope",
                    sub: [
                        {
                            key: "affected",
                            name: "Canaux de discussion affectés",
                            icon: "fa-check",
                            action: "role-manage-threads",
                        },
                        {
                            key: "add",
                            name: "Ajouter un canal de discussion",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier un canal de discussion",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un canal de discussion",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "order",
                    name: "Commande",
                    icon: "fa-shopping-cart",
                    sub: [
                        {
                            key: "action",
                            name: "Action sur les commandes",
                            icon: "fa-person-digging",
                            sub: [
                                {
                                    key: "affected",
                                    name: "Actions sur les commandes affectées",
                                    icon: "fa-check",
                                    action: "role-manage-order-actions",
                                },
                                {
                                    key: "add",
                                    name: "Ajouter une action sur les commandes",
                                    icon: "fa-plus",
                                },
                                {
                                    key: "update",
                                    name: "Modifier une action sur les commandes",
                                    icon: "fa-pen",
                                },
                                {
                                    key: "delete",
                                    name: "Supprimer une action sur les commandes",
                                    icon: "fa-trash",
                                },
                            ],
                        },
                        {
                            key: "step",
                            name: "Etape de commande",
                            icon: "fa-step-forward",
                            sub: [
                                {
                                    key: "affected",
                                    name: "Etapes de commande affectées",
                                    icon: "fa-check",
                                    action: "role-manage-order-steps",
                                },
                                {
                                    key: "add",
                                    name: "Ajouter une étape de commande",
                                    icon: "fa-plus",
                                },
                                {
                                    key: "update",
                                    name: "Modifier une étape de commande",
                                    icon: "fa-pen",
                                },
                                {
                                    key: "delete",
                                    name: "Supprimer une étape de commande",
                                    icon: "fa-trash",
                                },
                            ],
                        },
                        {
                            key: "status",
                            name: "Etat de commande",
                            icon: "fa-tags",
                            sub: [
                                {
                                    key: "affected",
                                    name: "Etats de commande affectés",
                                    icon: "fa-check",
                                    action: "role-manage-order-statuses",
                                },
                                {
                                    key: "add",
                                    name: "Ajouter un état de commande",
                                    icon: "fa-plus",
                                },
                                {
                                    key: "update",
                                    name: "Modifier un état de commande",
                                    icon: "fa-pen",
                                },
                                {
                                    key: "delete",
                                    name: "Supprimer un état de commande",
                                    icon: "fa-trash",
                                },
                            ],
                        },
                        {
                            key: "product",
                            name: "Produit",
                            icon: "fa-gift",
                            sub: [
                                {
                                    key: "add",
                                    name: "Ajouter un produit",
                                    icon: "fa-plus",
                                },
                                {
                                    key: "update",
                                    name: "Modifier les produits",
                                    icon: "fa-pen",
                                },
                                {
                                    key: "delete",
                                    name: "Supprimer des produits",
                                    icon: "fa-trash",
                                },
                            ],
                        },
                    ],
                },
                {
                    key: "user",
                    name: "Utilisateur",
                    icon: "fa-user",
                    sub: [
                        {
                            key: "add",
                            name: "Ajouter un utilisateur dans le projet",
                            icon: "fa-plus",
                        },
                        {
                            key: "delete",
                            name: "Enlever un utilisateur du projet",
                            icon: "fa-times",
                        },
                        {
                            key: "commission",
                            name: "Gérer les commission d'un utilisateur",
                            icon: "fa-money",
                            sub: [
                                {
                                    key: "add",
                                    name: "Ajouter/modifier une commission",
                                    icon: "fa-plus",
                                },
                                {
                                    key: "delete",
                                    name: "Supprimer la commission",
                                    icon: "fa-times",
                                },
                            ],
                        },
                    ],
                },
                {
                    key: "vehicle",
                    name: "Véhicule",
                    icon: "fa-car icon-brown",
                    sub: [
                        {
                            key: "add",
                            name: "Ajouter un véhicule",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un véhicule",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un véhicule",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
            ],
        },
        {
            key: "prospect",
            name: "Prospect",
            icon: "fa-users",
            sub: [
                {
                    key: "view",
                    name: "Voir tous les prospects",
                    icon: "fa-eye",
                },
                {
                    key: "add",
                    name: "Ajouter un prospect",
                    icon: "fa-plus",
                },
                {
                    key: "update",
                    name: "Modifier tous les prospects",
                    icon: "fa-pen",
                },
                {
                    key: "delete",
                    name: "Supprimer tous les prospects",
                    icon: "fa-trash",
                },
                {
                    key: "setting-prospects-table",
                    name: "Gérer les colonnes du tableau prospects",
                    icon: "fa-columns icon-grey",
                },
                {
                    key: "setting-prospect-profile",
                    name: "Gérer les blocs et champs dans la fiche prospect",
                    icon: "fa-columns icon-grey",
                },
                {
                    key: "import",
                    name: "Importer des prospects",
                    icon: "fa-file-upload",
                },
                {
                    key: "export",
                    name: "Exporter des prospects",
                    icon: "fa-file-download",
                },
                {
                    key: "sms",
                    name: "SMS",
                    icon: "fa-comments",
                    sub: [
                        {
                            key: "view",
                            name: "Voir les sms envoyés",
                            icon: "fa-eye",
                        },
                        {
                            key: "add",
                            name: "Envoyer un sms",
                            icon: "fa-comment",
                        },
                    ],
                },
                {
                    key: "interaction",
                    name: "Gestion des appels",
                    icon: "fa-phone",
                    sub: [
                        {
                            key: "view",
                            name: "Voir les historiques",
                            icon: "fa-clock",
                        },
                        {
                            key: "add",
                            name: "Appeler un prospect",
                            icon: "fa-phone",
                        },
                        {
                            key: "audio",
                            name: "Ecouter un appel enregistré",
                            icon: "fa-headphones",
                        },
                    ],
                },
                {
                    key: "order",
                    name: "Gérer les devis",
                    icon: "fa-shopping-cart",
                    sub: [
                        {
                            key: "view",
                            name: "Voir les devis",
                            icon: "fa-eye",
                        },
                        {
                            key: "add",
                            name: "Ajouter un devis",
                            icon: "fa-plus",
                        },
                        {
                            key: "update",
                            name: "Modifier des devis",
                            icon: "fa-pen",
                        },
                        {
                            key: "delete",
                            name: "Supprimer des devis",
                            icon: "fa-trash",
                        },
                    ],
                },
                {
                    key: "event",
                    name: "Gérer les RDVs",
                    icon: "fa-calendar",
                    sub: [
                        {
                            key: "affected",
                            name: "Types de RDV affectés",
                            icon: "fa-calendar",
                            action: "role-manage-calendars",
                        },
                        {
                            key: "view",
                            name: "Voir les RDVs",
                            icon: "fa-eye",
                        },
                    ],
                },
            ],
        },
        {
            key: "user",
            name: "Utilisateur",
            icon: "fa-user",
            sub: [
                {
                    key: "calendar",
                    name: "Gérer les types de RDV affectés",
                    icon: "fa-calendar",
                },
                {
                    key: "category",
                    name: "Gérer les catégories de filtre affectés",
                    icon: "fa-tags",
                },
                {
                    key: "document",
                    name: "Gérer les documents affectés",
                    icon: "fa-file-pdf",
                },
                {
                    key: "folder",
                    name: "Gérer les dossiers affectés",
                    icon: "fa-folder",
                },
                {
                    key: "group",
                    name: "Gérer les groupes affectés",
                    icon: "fa-users",
                },
                {
                    key: "message-template",
                    name: "Gérer les modèles de message affectés",
                    icon: "fa-envelope",
                },
                {
                    key: "questionnaire",
                    name: "Gérer les questionnaires affectés",
                    icon: "fa-clipboard",
                },
                {
                    key: "role",
                    name: "Gérer les rôles affectés",
                    icon: "fa-user-tie",
                },
                {
                    key: "sms-template",
                    name: "Gérer les modèles de SMS affectés",
                    icon: "fa-comment",
                },
                {
                    key: "thread",
                    name: "Gérer les canaux de discussion affectés",
                    icon: "fa-envelope",
                },
                {
                    key: "order",
                    name: "Commande",
                    icon: "fa-shopping-cart",
                    sub: [
                        {
                            key: "action",
                            name: "Gérer les actions sur les commandes affectées",
                            icon: "fa-person-digging",
                        },
                        {
                            key: "step",
                            name: "Gérer les étapes de commande affectées",
                            icon: "fa-step-forward",
                        },
                        {
                            key: "status",
                            name: "Gérer les états de commande affectés",
                            icon: "fa-tags",
                        },
                    ],
                },
                {
                    key: "user",
                    name: "Gérer les utilisateurs affectés",
                    icon: "fa-user",
                },
            ],
        },
        {
            key: "map",
            name: "Utiliser la MAP",
            icon: "fa-map-marker",
        },
        {
            key: "stat",
            name: "Voir les statistiques",
            icon: "fa-chart-bar",
        },
    ],
};
