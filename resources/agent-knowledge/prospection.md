# Prospection

## À quoi ça sert
Les fiches prospects centralisent les coordonnées, champs personnalisés, labels, groupes, assignations, échanges et documents. L’API projet expose les opérations sur les prospects; les outils d’agent consultent la même visibilité que le CRM.

## Comment faire
Consulte les champs et filtres disponibles, puis recherche les prospects dans le projet. Pour une modification, vérifie d’abord la cible et les identifiants de labels, groupes ou champs dans la configuration du projet.

## Règles
La visibilité des prospects dépend des droits de l’utilisateur et du projet. Les données personnelles, dont le téléphone et l’e-mail, ne sont renvoyées par la recherche d’agent que si ces champs sont demandés. Une fiche déjà traitée n’est pas modifiable par les outils d’agent prévus pour les fiches éditables.

## Limites
La liste complète des filtres dépend de l’entité et de la configuration du projet. Consulte le catalogue CRM avant de les utiliser. Les doublons ne sont repérés que sur l’e-mail, le mobile et le téléphone, et seulement si le champ est configuré comme unique dans le projet : deux prospects non supprimés du projet qui ont la même valeur non vide sont regroupés et surlignés dans le tableau. La détection par les outils d’agent est réservée aux administrateurs.

## Dans l'interface
Chemin : barre du haut > « Prospects » > route `/project/{project}/prospect` puis fiche `/project/{project}/prospect/{prospect}` (`prospect.table.Layout.vue`, `prospect.profile.Profile.vue`).

1. Pour créer une fiche, choisissez « Nouveau prospect » dans le menu ou la barre du tableau; le contrôle est affiché avec `all.prospect.add`. Pour importer, « Importer des prospects » nécessite `all.prospect.import`.
2. Ouvrez une fiche prospect. Les icônes de la fiche ont ces libellés exacts : « SMS », « Appel », « Message », « Fichier », « Document », « Devis », « RDV », « Filtres », « Questionnaire », « Historique de modification », « Historique de consultations » et « MAP ».
3. « SMS » ouvre le panneau SMS si `all.prospect.sms.view` est accordé; l’envoi demande `all.prospect.sms.add` et un canal/numéro disponible. « Appel » ouvre le panneau d’interactions avec `all.prospect.interaction.view`; une ligne compatible est nécessaire pour passer l’appel. L’enregistrement d’un historique vérifie `all.prospect.interaction.add`.
4. « Message » ouvre les échanges si le droit de consultation correspondant est satisfait; ajouter un canal de discussion vérifie `all.project.thread.add`. « Fichier » ouvre les dossiers et fichiers si le droit de consultation correspondant est satisfait; « Ajouter » pour un dossier requiert `all.project.folder.add`. L’ajout de fichier utilise aussi « Ajouter » dans le dossier. « Document » ouvre les documents associés; « Ajouter » y est affiché avec `all.project.document.add` et « Nouveau devis » avec `all.prospect.order.add`.
5. « Devis » ouvre les commandes de la fiche; « Nouveau devis » n’apparaît qu’avec `all.prospect.order.add`. « RDV » ouvre les événements associés; la disponibilité du panneau suit `all.prospect.event.view`.
6. « Questionnaire » ouvre les questionnaires du prospect. « Historique de modification » et « Historique de consultations » affichent les entrées correspondantes. « MAP » est affiché si la fiche a latitude et longitude.
7. **Importer des prospects.** Dans la barre du tableau, « Importer des prospects » (droit `all.prospect.import`) ouvre un formulaire : « Depuis » (« Fichier local » ou « Google Sheets »), « Fichier » (ou « Lien public du classeur » pour Google Sheets), « Nom », « Séparateur de champs » (« Virgule », « Point virgule », « (Tabulation) » ou « (Espace) ») et « Délimiteur de texte ». Les fichiers lus sont les CSV, les classeurs ODS et les classeurs Excel XLSX (un classeur Google Sheets est téléchargé en XLSX avec toutes ses feuilles); il n'y a donc pas besoin de convertir un fichier Excel en CSV. L'import s'ouvre ensuite sur trois onglets : « Mapping » (relier chaque colonne à un champ du prospect, à un filtre, à un rendez-vous, un devis, un SMS, un appel, un lien externe ou des utilisateurs affectés; « + Champ » et « + Filtre » créent ce qui manque, et « Feuilles à importer » choisit les feuilles d'un classeur), « Relations » (utilisateurs et groupes affectés) et « Importer » (« Valider l'import » lance l'import, « Re importer » le rejoue). Un import Google Sheets peut garder une « Synchronisation instantanée (nouveaux leads) ». On retrouve les imports du projet dans le bloc « Imports » de la fiche du projet, avec les suppressions possibles : l'import seul, ses prospects, ou les deux.
8. **Exporter les prospects.** Le bouton « Exporter les prospects » du pied du tableau (droit `all.prospect.export`) exporte les prospects filtrés avec les colonnes choisies, dans l'un de trois modes : téléchargement direct d'un fichier XLSX (quand des filtres sont appliqués), envoi par e-mail (l'export est préparé en arrière-plan puis reçu par e-mail), ou création d'un Google Sheet (il faut avoir lié un compte Google Drive au projet dans « Paramètres »). L'envoi par e-mail ou Google Sheet peut être planifié à une date future.
9. « Paramètre des blocs de la fiche prospect » (icône engrenage) permet de réorganiser les blocs et colonnes; il requiert `all.prospect.setting-prospect-profile`.

Après une création ou modification validée, les données sont visibles dans le panneau ou la fiche correspondante. Les actions non citées ci-dessus n’ont pas de droit propre documenté ici : en cas de refus, l’utilisateur n’a pas le droit correspondant dans le projet.

Sources d’interface : `components/layouts/Header.vue`, `components/prospect/table/menu/Menus.vue`, `components/prospect/table/CardMenus.vue`, `components/prospect/profile/Profile.vue`, `components/prospect/profile/slides/setting/Slide.vue`, panneaux `components/prospect/{sms,interaction,message,file,document,order,event,questionnaire}/`, `router/index.js`, `lang/fr.json` (`header.*`, `prospect.profile.*`, `prospect.table.*`).

## Questions fréquentes
**Comment ajouter un prospect ?** Dans « Prospects », choisissez « Nouveau prospect »; `all.prospect.add` est requis.

**Pourquoi « MAP » n’apparaît-elle pas sur la fiche ?** L’icône exige latitude et longitude.

**Qui peut réorganiser les blocs ?** Il faut `all.prospect.setting-prospect-profile`.

## Pour aller plus loin
Fiche d’un prospect, ses blocs et historiques : sujet `fiche-prospect`. Colonnes et filtres du tableau : sujet `tableau-prospects`. Actions sur plusieurs prospects : sujet `actions-en-masse`. Doublons et corbeille : sujet `corbeille-doublons`.
