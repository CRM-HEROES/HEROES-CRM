# Tableau des prospects : colonnes et filtres

## À quoi ça sert
Le tableau « Prospects » est l’écran de travail principal : il liste les prospects du projet avec les colonnes choisies et permet de les filtrer, de les trier et d’agir dessus.

## Comment faire
Ouvrez « Prospects » dans la barre du haut. Choisissez les colonnes avec « Paramètre des colonnes », filtrez avec les filtres du tableau, puis cliquez un prospect pour ouvrir sa fiche ou sélectionnez-en plusieurs pour une action en masse.

## Règles
- Le tableau n’affiche que les prospects visibles pour l’utilisateur (voir la fiche Prospection).
- Les colonnes et leur ordre sont personnels : chacun compose son tableau. Choisir des colonnes demande le droit `all.prospect.setting-prospects-table`.
- Plusieurs filtres se combinent : un prospect doit correspondre à tous les filtres actifs. « Réinitialiser » les efface.
- « Nombre de prospects par page » et « Nombre de lignes max par prospects » règlent la densité du tableau.

## Limites
Un filtre ne montre que les valeurs qui existent dans le projet. Le tableau ne remplace pas la recherche globale (voir la fiche Recherche) : celle-ci cherche aussi les autres éléments du projet.

## Dans l'interface
Chemin : « Prospects » (`components/prospect/table/`).

1. **Colonnes :** « Paramètre des colonnes » ouvre « Gérer les colonnes de {project} » avec les onglets « Champs », « Filtres », « Discussions », « Questionnaires » et « Autres ». « Autres » propose : « Historique », « RDV », « Appels », « SMS », « Utilisateurs affectés », « Groupes affectés », « Devis », « Import », « Créé par », « Groupes » et « Utilisateurs ». Pour les RDV, on peut afficher le titre, la description, l’adresse, l’adresse de destination, les heures et dates de début et de fin, le type (calendrier), l’utilisateur, le véhicule, le prospect et le devis du rendez-vous. « Utiliser les colonnes de l'import ... » reprend les colonnes d’un import.
2. **Filtres par ce qu’on sait du prospect :** utilisateurs affectés, groupes affectés, imports, créateurs, ajouté depuis Pipedrive, champ, filtre (label), fichier (« Avec fichiers »), message (« Avec messages ») et appel (« Avec appels »). Chacun propose aussi l’inverse, par exemple « Non affectés à un utilisateur », « Non affectés à un groupe » ou « Non importés ».
3. **Filtres par SMS :** « Avec SMS », « Avec erreur », par source et par période, ou « Sans erreur ».
4. **Filtres par devis :** « Avec devis », avec produits, actions, étapes, états ou documents.
5. **Filtres par RDV :** « Avec RDV » ou « Sans RDV », « Dernier RDV », calendriers, « Affectés à », « Créés par », période (« Aujourd'hui », « Hier », « Demain », « Semaine en cours », « Après le », « Avant le », « A venir », « Passé ») et statut (« RDV confirmés », « RDV non confirmés », « RDV faits », « RDV non faits »).
6. **Dans une cellule :** on peut « Ajouter un RDV » ou « Ecrire un message ... » directement depuis la ligne d’un prospect, et « Voir la discussion ... ». La cellule d’historique charge les dernières actions (« Réessayer » en cas d’erreur).
7. **Menus :** « Afficher/cacher les menus » et « Afficher/cacher les menus par prospect » montrent ou masquent les icônes d’action.
8. **Sélection :** cochez des prospects, ou « Sélectionner tous les prospects filtrés »; puis utilisez le pied du tableau (voir la fiche Actions en masse).

Droits requis : `all.prospect.setting-prospects-table` pour les colonnes, `all.prospect.add` pour « Nouveau prospect », `all.prospect.import` pour l’import, `all.prospect.export` pour l’export. Après un réglage de colonnes ou de filtres, le tableau se recharge.

Sources d’interface : `components/prospect/table/`, `components/prospect/filters/`, `lang/fr.json` (`prospect.table.*`).

## Questions fréquentes
**Comment ajouter une colonne ?** « Paramètre des colonnes », onglet « Champs », « Filtres », « Discussions », « Questionnaires » ou « Autres » (droit `all.prospect.setting-prospects-table`).

**Comment voir les prospects sans rendez-vous ?** Filtre par RDV > « Sans RDV ».

**Comment voir les prospects non affectés ?** Filtre par utilisateurs affectés > « Non affectés à un utilisateur ».

**Comment voir les SMS en erreur ?** Filtre par SMS > « Avec erreur ».

**Comment tout effacer ?** « Réinitialiser ».
