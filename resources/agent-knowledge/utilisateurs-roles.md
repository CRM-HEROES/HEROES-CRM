# Utilisateurs et rôles

## À quoi ça sert
Les utilisateurs d’un projet et leurs rôles contrôlent l’accès aux ressources, calendriers, documents, groupes et autres fonctions attribuées.

## Comment faire
La liste des utilisateurs d’un projet permet de consulter des profils et d’ajuster des affectations si les droits le permettent. La fiche du projet donne aussi accès aux blocs « Utilisateurs » et « Rôles ».

## Règles
Les permissions de l’utilisateur connecté limitent les opérations disponibles. Le bouton d’ajout de la liste de projet est affiché avec `can('all')`.

## Limites
La différence entre les rôles globaux et les rôles de projet, ainsi que l’effet détaillé de chaque permission, est À vérifier.

## Dans l'interface
Chemin : icône engrenage > « Paramètres » > « Liste des utilisateurs » (visible dans le projet lorsque `can('all.project')`) ouvre la route `user`. Autre chemin : « Accueil » > projet > blocs « Utilisateurs » et « Rôles ».

1. Ouvrez la liste des utilisateurs du projet.
2. Sélectionnez un utilisateur pour afficher son profil; les affectations sont présentées dans les blocs correspondants.
3. Dans la liste, « Ajouter » apparaît quand `can('all')`; pour modifier, sélectionnez une ligne puis « Modifier » si l’action est disponible avec ce droit.
4. Consultez le bloc « Rôles » de la fiche projet pour gérer les rôles du projet; les boutons précis de cette opération sont À vérifier.

Droits requis : `all.project` pour voir « Liste des utilisateurs » dans Paramètres; `all` pour les actions d’ajout et d’édition relevées dans `user/list/Layout.vue`. D’autres actions sont conditionnelles à leurs permissions. Après modification, la liste ou les affectations du profil reflètent le résultat; l’affichage exact est À vérifier.

Sources d’interface : `components/layouts/setting/Layout.vue`, `components/user/list/Layout.vue`, `components/project/profile/Profile.vue`, `components/project/profile/blocs/role/Bloc.vue`, `router/index.js`, `lang/fr.json`.

## Questions fréquentes
**Comment ouvrir les utilisateurs du projet ?** Dans « Paramètres », choisissez « Liste des utilisateurs ».

**Qui peut ajouter ou modifier un utilisateur ?** Le code vérifie `all`; les droits supplémentaires sont À vérifier.

**Où se trouvent les rôles ?** Dans le bloc « Rôles » de la fiche projet.
