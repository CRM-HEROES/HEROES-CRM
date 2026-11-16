# Prospection

## À quoi ça sert
Les fiches prospects centralisent les coordonnées, champs personnalisés, labels, groupes, assignations, échanges et documents. L’API projet expose les opérations sur les prospects; les outils d’agent consultent la même visibilité que le CRM.

## Comment faire
Consulte les champs et filtres disponibles, puis recherche les prospects dans le projet. Pour une modification, vérifie d’abord la cible et les identifiants de labels, groupes ou champs dans la configuration du projet.

## Règles
La visibilité des prospects dépend des droits de l’utilisateur et du projet. Les données personnelles, dont le téléphone et l’e-mail, ne sont renvoyées par la recherche d’agent que si ces champs sont demandés. Une fiche déjà traitée n’est pas modifiable par les outils d’agent prévus pour les fiches éditables.

## Limites
La liste complète des filtres dépend de l’entité et de la configuration du projet. Consulte le catalogue CRM avant de les utiliser. Les règles détaillées de dédoublonnage sont À vérifier.

## Dans l'interface
Chemin : barre du haut > « Prospects » > route `/project/{project}/prospect` puis fiche `/project/{project}/prospect/{prospect}` (`prospect.table.Layout.vue`, `prospect.profile.Profile.vue`).

1. Pour créer une fiche, choisissez « Nouveau prospect » dans le menu ou la barre du tableau; le contrôle est affiché avec `all.prospect.add`. Pour importer, « Importer des prospects » nécessite `all.prospect.import`.
2. Ouvrez une fiche prospect. Les icônes de la fiche ont ces libellés exacts : « SMS », « Appel », « Message », « Fichier », « Document », « Devis », « RDV », « Filtres », « Questionnaire », « Historique de modification », « Historique de consultations » et « MAP ».
3. « SMS » ouvre le panneau SMS si `all.prospect.sms.view` est accordé; l’envoi demande `all.prospect.sms.add` et un canal/numéro disponible. « Appel » ouvre le panneau d’interactions avec `all.prospect.interaction.view`; une ligne compatible est nécessaire pour passer l’appel. L’enregistrement d’un historique vérifie `all.prospect.interaction.add`.
4. « Message » ouvre les échanges si le droit de consultation correspondant est satisfait; ajouter un canal de discussion vérifie `all.project.thread.add`. « Fichier » ouvre les dossiers et fichiers si le droit de consultation correspondant est satisfait; « Ajouter » pour un dossier requiert `all.project.folder.add`. L’ajout de fichier utilise aussi « Ajouter » dans le dossier. « Document » ouvre les documents associés; les droits et opérations disponibles dans ce panneau sont À vérifier.
5. « Devis » ouvre les commandes de la fiche; « Nouveau devis » n’apparaît qu’avec `all.prospect.order.add`. « RDV » ouvre les événements associés; la disponibilité du panneau suit `all.prospect.event.view`.
6. « Questionnaire » ouvre les questionnaires du prospect. « Historique de modification » et « Historique de consultations » affichent les entrées correspondantes. « MAP » est affiché si la fiche a latitude et longitude.
7. « Paramètre des blocs de la fiche prospect » (icône engrenage) permet de réorganiser les blocs et colonnes; il requiert `all.prospect.setting-prospect-profile`.

Après une création ou modification validée, les données sont visibles dans le panneau ou la fiche correspondante. Le formulaire de création/édition de certains blocs et les droits des actions non cités ci-dessus sont À vérifier.

Sources d’interface : `components/layouts/Header.vue`, `components/prospect/table/menu/Menus.vue`, `components/prospect/table/CardMenus.vue`, `components/prospect/profile/Profile.vue`, `components/prospect/profile/slides/setting/Slide.vue`, panneaux `components/prospect/{sms,interaction,message,file,document,order,event,questionnaire}/`, `router/index.js`, `lang/fr.json` (`header.*`, `prospect.profile.*`, `prospect.table.*`).

## Questions fréquentes
**Comment ajouter un prospect ?** Dans « Prospects », choisissez « Nouveau prospect »; `all.prospect.add` est requis.

**Pourquoi « MAP » n’apparaît-elle pas sur la fiche ?** L’icône exige latitude et longitude.

**Qui peut réorganiser les blocs ?** Il faut `all.prospect.setting-prospect-profile`.
