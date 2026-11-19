# Fiche prospect

## À quoi ça sert
La fiche d’un prospect regroupe ses informations et tout son historique de relation : champs, filtres, appels, SMS, messages, fichiers, documents, devis, rendez-vous, utilisateurs et groupes affectés, position sur la carte.

## Comment faire
Dans « Prospects », cliquez sur un prospect pour ouvrir sa fiche. Les icônes de menu de la fiche ouvrent les panneaux (« SMS », « Appel », « Message », « Fichier », « Document », « Devis », « RDV », « Filtres », « Questionnaire »). Les blocs affichés sur la fiche sont des résumés de ces éléments.

## Règles
- Les blocs de la fiche sont : « Champs » (informations du prospect), « Messages », « Fichiers », « Filtres », « Devis », « Filtres actifs », « Liens externes », « RDVs », « Historiques », « Appels », « SMS », « Documents », « Utilisateurs affectés », « Groupes affectés » et « MAP ».
- Chaque utilisateur peut organiser les blocs de sa fiche, sur ordinateur : on déplace un bloc par glisser-déposer avec sa poignée, d’une place à une autre ou d’une colonne à l’autre, et la disposition s’enregistre toute seule (c’est un réglage personnel). L’icône « Paramètre des blocs de la fiche prospect » ouvre « Paramètre fiche de prospect » où l’on répartit les blocs en colonnes (Colonne 1, Colonne 2…), ajoute un bloc (« Nouveau bloc ») ou le modifie (« Modifier le bloc »); l’icône en forme de flèches y active ou désactive le déplacement. Il faut le droit `all.prospect.setting-prospect-profile`.
- Le bloc « Champs » a un bouton « Valider » qui enregistre les valeurs modifiées de la fiche.
- Le bloc « Filtres actifs » montre les filtres posés sur le prospect; les modifier demande `all.project.category.label.update`.
- Le bloc « Historiques » résume ce qui s’est passé sur la fiche : « Affecté à », « Affecté au groupe », « Devis », « Appelé sur », SMS avec sa source.
- Un appel passé par un agent IA s’affiche « Appelé par {agent} ».
- Un prospect « archivé » ou déjà traité est signalé comme traité : la plupart des actions en masse sont alors masquées pour lui (voir la fiche Actions en masse).

## Limites
Les droits d’ajout et de modification des prospects sont `all.prospect.add` et ceux du tableau (voir la fiche Actions en masse). Le serveur n’exige pas de droit particulier pour consulter l’historique d’un prospect visible. La fiche ne montre que les prospects que l’utilisateur a le droit de voir (voir la fiche Prospection).

## Dans l'interface
Chemin : « Prospects » > clic sur un prospect (`components/prospect/profile/Profile.vue`).

1. **Informations :** modifiez les champs dans le bloc « Champs », puis « Valider ».
2. **Communiquer :** « SMS », « Message » (e-mails et discussions) et « Appel » ouvrent leurs panneaux (voir les fiches SMS et e-mails et Appels).
3. **Fichiers et documents :** « Fichier » ouvre les dossiers du prospect (« Ajouter » un fichier dans un dossier) et « Document » génère les documents du prospect à partir des modèles (voir la fiche Documents).
4. **Commercial :** « Devis » liste et crée les devis (« Nouveau devis »); « RDV » liste et crée les rendez-vous.
5. **Classement :** « Filtres » pose ou retire des filtres; le bloc « Utilisateurs affectés » et le bloc « Groupes affectés » indiquent qui s’occupe du prospect.
6. **Traçabilité :** « Historique de modification » montre les changements faits sur la fiche (qui a modifié quoi), et « Historique de consultations » montre qui a ouvert la fiche.
7. **Géolocalisation :** « MAP » apparaît quand la fiche a une latitude et une longitude; il ouvre la carte sur ce prospect.
8. **Liens externes :** le bloc « Liens externes » liste les liens associés au prospect (par exemple une fiche d’un autre outil).

Droits requis : `all.prospect.sms.view` et `all.prospect.sms.add` pour les SMS, `all.prospect.interaction.view` et `.add` pour les appels, `all.prospect.order.add` pour « Nouveau devis », `all.prospect.event.view` pour les RDV, `all.project.folder.add` pour ajouter un dossier, `all.project.thread.add` pour un canal de discussion. Après chaque action, le bloc correspondant de la fiche se met à jour.

Sources d’interface : `components/prospect/profile/Profile.vue`, `components/prospect/profile/blocs/`, `components/prospect/profile/slides/setting/`, `lang/fr.json` (`prospect.profile.*`).

## Questions fréquentes
**Comment modifier un prospect ?** Ouvrez sa fiche, changez les valeurs dans le bloc « Champs », puis « Valider ».

**Comment voir qui a modifié une fiche ?** Icône « Historique de modification ».

**Comment voir qui a consulté une fiche ?** Icône « Historique de consultations ».

**Comment changer l’ordre des blocs de la fiche ?** « Paramètre des blocs de la fiche prospect » (droit `all.prospect.setting-prospect-profile`).

**Pourquoi un bloc est-il absent de ma fiche ?** Il a été retiré de votre disposition : réajoutez-le avec « Nouveau bloc ».
