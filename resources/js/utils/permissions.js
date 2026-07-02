export default {
    key: "all",
    name: "Toutes les permissions",
    sub: [
        {
            key: "project",
            name: "Projet",
            icon: "fa-file icon-blue",
            sub: [
                {
                    key: "calendar",
                    name: "Type de RDV",
                    icon: "fa-calendar icon-purple",
                    sub: [
                        {
                            key: "affected",
                            name: "Types de RDV affectés",
                            icon: "fa-check icon-green",
                            action: "user-manage-calendars",
                        },
                        {
                            key: "add",
                            name: "Ajouter un type de RDV",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un type de RDV",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un type de RDV",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "campaign",
                    name: "Campagne",
                    icon: "fa-bullhorn icon-brown",
                    sub: [
                        {
                            key: "add",
                            name: "Ajouter une campagne",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier une campagne",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer une campagne",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "category",
                    name: "Catégorie de filtre",
                    icon: "fa-tags icon-purple",
                    sub: [
                        {
                            key: "affected",
                            name: "Catégories de filtre affectées",
                            icon: "fa-check icon-green",
                            action: "user-manage-categories",
                        },
                        {
                            key: "add",
                            name: "Ajouter une catégorie de filtre",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier une catégorie de filtre",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer une catégorie de filtre",
                            icon: "fa-trash icon-red",
                        },
                        {
                            key: "label",
                            name: "Gérer les filtres",
                            icon: "fa-tag icon-garnet",
                            sub: [
                                {
                                    key: "add",
                                    name: "Ajouter un filtre",
                                    icon: "fa-plus icon-green",
                                },
                                {
                                    key: "update",
                                    name: "Modifier un filtre",
                                    icon: "fa-pen icon-cyan",
                                },
                                {
                                    key: "delete",
                                    name: "Supprimer un filtre",
                                    icon: "fa-trash icon-red",
                                },
                            ],
                        },
                    ],
                },
                {
                    key: "document",
                    name: "Document",
                    icon: "fa-file-pdf icon-garnet",
                    sub: [
                        {
                            key: "affected",
                            name: "Documents affectés",
                            icon: "fa-check icon-green",
                            action: "user-manage-documents",
                        },
                        {
                            key: "add",
                            name: "Ajouter un document",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un document",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un document",
                            icon: "fa-trash icon-red",
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
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un champ de prospect",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un champ de prospect",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "folder",
                    name: "Dossier",
                    icon: "fa-folder icon-blue",
                    sub: [
                        {
                            key: "affected",
                            name: "Dossiers affectés",
                            icon: "fa-check icon-green",
                            action: "user-manage-folders",
                        },
                        {
                            key: "add",
                            name: "Ajouter un dossier",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un dossier",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un dossier",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "group",
                    name: "Groupe",
                    icon: "fa-users icon-brown",
                    sub: [
                        {
                            key: "affected",
                            name: "Groupes affectés",
                            icon: "fa-check icon-green",
                            action: "user-manage-groups",
                        },
                        {
                            key: "add",
                            name: "Ajouter un groupe",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un groupe",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un groupe",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "menu",
                    name: "Menu",
                    icon: "fa-list icon-garnet",
                    sub: [
                        {
                            key: "add",
                            name: "Ajouter un menu",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un menu",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un menu",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "message-template",
                    name: "Modèle de message",
                    icon: "fa-envelope icon-green",
                    sub: [
                        {
                            key: "add",
                            name: "Ajouter un modèle de message",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un modèle de message",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un modèle de message",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "questionnaire",
                    name: "Questionnaire",
                    icon: "fa-clipboard icon-brown",
                    sub: [
                        {
                            key: "affected",
                            name: "Questionnaires affectés",
                            icon: "fa-check icon-green",
                            action: "user-manage-questionnaires",
                        },
                        {
                            key: "add",
                            name: "Ajouter un questionnaire",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un questionnaire",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un questionnaire",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "role",
                    name: "Rôle",
                    icon: "fa-user-tie icon-brown",
                    sub: [
                        {
                            key: "affected",
                            name: "Rôles affectés",
                            icon: "fa-check icon-green",
                            action: "user-manage-roles",
                        },
                        {
                            key: "add",
                            name: "Ajouter un rôle",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un rôle",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un rôle",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "sms-template",
                    name: "Modèle de SMS",
                    icon: "fa-comment icon-purple",
                    sub: [
                        {
                            key: "add",
                            name: "Ajouter un modèle de SMS",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un modèle de SMS",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un modèle de SMS",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "thread",
                    name: "Canal de discussion",
                    icon: "fa-envelope icon-green",
                    sub: [
                        {
                            key: "affected",
                            name: "Canaux de discussion affectés",
                            icon: "fa-check icon-green",
                            action: "user-manage-threads",
                        },
                        {
                            key: "add",
                            name: "Ajouter un canal de discussion",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier un canal de discussion",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer un canal de discussion",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "order",
                    name: "Commande",
                    icon: "fa-shopping-cart icon-cyan",
                    sub: [
                        {
                            key: "action",
                            name: "Action sur les commandes",
                            icon: "fa-person-digging",
                            sub: [
                                {
                                    key: "affected",
                                    name: "Actions sur les commandes affectées",
                                    icon: "fa-check icon-green",
                                    action: "user-manage-order-actions",
                                },
                                {
                                    key: "add",
                                    name: "Ajouter une action sur les commandes",
                                    icon: "fa-plus icon-green",
                                },
                                {
                                    key: "update",
                                    name: "Modifier une action sur les commandes",
                                    icon: "fa-pen icon-cyan",
                                },
                                {
                                    key: "delete",
                                    name: "Supprimer une action sur les commandes",
                                    icon: "fa-trash icon-red",
                                },
                            ],
                        },
                        {
                            key: "step",
                            name: "Etape de commande",
                            icon: "fa-step-forward icon-cyan",
                            sub: [
                                {
                                    key: "affected",
                                    name: "Etapes de commande affectées",
                                    icon: "fa-check icon-green",
                                    action: "user-manage-order-steps",
                                },
                                {
                                    key: "add",
                                    name: "Ajouter une étape de commande",
                                    icon: "fa-plus icon-green",
                                },
                                {
                                    key: "update",
                                    name: "Modifier une étape de commande",
                                    icon: "fa-pen icon-cyan",
                                },
                                {
                                    key: "delete",
                                    name: "Supprimer une étape de commande",
                                    icon: "fa-trash icon-red",
                                },
                            ],
                        },
                        {
                            key: "status",
                            name: "Etat de commande",
                            icon: "fa-tags icon-cyan",
                            sub: [
                                {
                                    key: "affected",
                                    name: "Etats de commande affectés",
                                    icon: "fa-check icon-green",
                                    action: "user-manage-order-statuses",
                                },
                                {
                                    key: "add",
                                    name: "Ajouter un état de commande",
                                    icon: "fa-plus icon-green",
                                },
                                {
                                    key: "update",
                                    name: "Modifier un état de commande",
                                    icon: "fa-pen icon-cyan",
                                },
                                {
                                    key: "delete",
                                    name: "Supprimer un état de commande",
                                    icon: "fa-trash icon-red",
                                },
                            ],
                        },
                        {
                            key: "product",
                            name: "Produit",
                            icon: "fa-gift icon-brown",
                            sub: [
                                {
                                    key: "add",
                                    name: "Ajouter un produit",
                                    icon: "fa-plus icon-green",
                                },
                                {
                                    key: "update",
                                    name: "Modifier les produits",
                                    icon: "fa-pen icon-cyan",
                                },
                                {
                                    key: "delete",
                                    name: "Supprimer des produits",
                                    icon: "fa-trash icon-red",
                                },
                            ],
                        },
                    ],
                },
                {
                    key: "user",
                    name: "Utilisateur",
                    icon: "fa-user icon-brown",
                    sub: [
                        {
                            key: "affected",
                            name: "Utilisateurs affectés",
                            icon: "fa-check icon-green",
                            action: "user-manage-users",
                        },
                        {
                            key: "add",
                            name: "Ajouter un utilisateur dans le projet",
                            icon: "fa-plus icon-green",
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
                                    icon: "fa-plus icon-green",
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
            icon: "fa-users icon-green",
            sub: [
                {
                    key: "view",
                    name: "Voir tous les prospects",
                    icon: "fa-eye icon-blue",
                },
                {
                    key: "add",
                    name: "Ajouter un prospect",
                    icon: "fa-plus icon-green",
                },
                {
                    key: "update",
                    name: "Modifier tous les prospects",
                    icon: "fa-pen icon-cyan",
                },
                {
                    key: "delete",
                    name: "Supprimer tous les prospects",
                    icon: "fa-trash icon-red",
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
                    icon: "fa-file-upload icon-green",
                },
                {
                    key: "export",
                    name: "Exporter des prospects",
                    icon: "fa-file-download icon-green",
                },
                {
                    key: "sms",
                    name: "SMS",
                    icon: "fa-comments icon-purple",
                    sub: [
                        {
                            key: "view",
                            name: "Voir les sms envoyés",
                            icon: "fa-eye icon-blue",
                        },
                        {
                            key: "add",
                            name: "Envoyer un sms",
                            icon: "fa-comment icon-purple",
                        },
                    ],
                },
                {
                    key: "interaction",
                    name: "Gestion des appels",
                    icon: "fa-phone icon-green",
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
                    icon: "fa-shopping-cart icon-cyan",
                    sub: [
                        {
                            key: "view",
                            name: "Voir les devis",
                            icon: "fa-eye icon-blue",
                        },
                        {
                            key: "add",
                            name: "Ajouter un devis",
                            icon: "fa-plus icon-green",
                        },
                        {
                            key: "update",
                            name: "Modifier des devis",
                            icon: "fa-pen icon-cyan",
                        },
                        {
                            key: "delete",
                            name: "Supprimer des devis",
                            icon: "fa-trash icon-red",
                        },
                    ],
                },
                {
                    key: "event",
                    name: "Gérer les RDVs",
                    icon: "fa-calendar icon-purple",
                    sub: [
                        {
                            key: "affected",
                            name: "Types de RDV affectés",
                            icon: "fa-calendar icon-purple",
                            action: "user-manage-calendars",
                        },
                        {
                            key: "view",
                            name: "Voir les RDVs",
                            icon: "fa-eye icon-blue",
                        },
                    ],
                },
            ],
        },
        {
            key: "user",
            name: "Utilisateur",
            icon: "fa-user icon-brown",
            sub: [
                {
                    key: "calendar",
                    name: "Gérer les types de RDV affectés",
                    icon: "fa-calendar icon-purple",
                },
                {
                    key: "category",
                    name: "Gérer les catégories de filtre affectés",
                    icon: "fa-tags icon-purple",
                },
                {
                    key: "document",
                    name: "Gérer les documents affectés",
                    icon: "fa-file-pdf icon-garnet",
                },
                {
                    key: "folder",
                    name: "Gérer les dossiers affectés",
                    icon: "fa-folder icon-green",
                },
                {
                    key: "group",
                    name: "Gérer les groupes affectés",
                    icon: "fa-users icon-brown",
                },
                {
                    key: "message-template",
                    name: "Gérer les modèles de message affectés",
                    icon: "fa-envelope icon-green",
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
                    icon: "fa-comment icon-purple",
                },
                {
                    key: "thread",
                    name: "Gérer les canaux de discussion affectés",
                    icon: "fa-envelope icon-green",
                },
                {
                    key: "order",
                    name: "Commande",
                    icon: "fa-shopping-cart icon-cyan",
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
            icon: "fa-chart-bar icon-orange",
        },
    ],
};
