# Utilisateurs et rôles

## À quoi ça sert
Les utilisateurs d’un projet et leurs rôles contrôlent l’accès aux ressources, calendriers, documents, groupes et autres fonctions attribuées.

## Comment faire
La liste des utilisateurs d’un projet permet de consulter des profils et d’ajuster des affectations si les droits le permettent. La fiche du projet donne aussi accès aux blocs « Utilisateurs » et « Rôles ».

## Règles
Les permissions de l’utilisateur connecté limitent les opérations disponibles. Le bouton d’ajout de la liste de projet est affiché avec `can('all')`.

## Limites
Les rôles appartiennent à un projet : un utilisateur a des permissions différentes dans chaque projet où il est membre. Les permissions sont hiérarchiques : `all` couvre toutes les permissions du projet, `all.prospect` couvre celles des prospects, et ainsi de suite. Deux statuts particuliers existent en dehors des rôles : le statut de super-administrateur, qui voit et gère tous les projets, et le statut d’administrateur de projet. Les droits d’un agent IA ne dépassent jamais ceux de son propriétaire.

## Dans l'interface
Chemin : icône engrenage > « Paramètres » > « Liste des utilisateurs » (visible dans le projet lorsque `can('all.project')`) ouvre la route `user`. Autre chemin : « Accueil » > projet > blocs « Utilisateurs » et « Rôles ».

1. Ouvrez la liste des utilisateurs du projet.
2. Sélectionnez un utilisateur pour afficher son profil; les affectations sont présentées dans les blocs correspondants.
3. Dans la liste, « Ajouter » apparaît quand `can('all')`; pour modifier, sélectionnez une ligne puis « Modifier » si l’action est disponible avec ce droit.
4. Consultez le bloc « Rôles » de la fiche projet pour gérer les rôles du projet; l’icône « + » du bloc ajoute un rôle; cliquer sur une ligne de rôle l’ouvre pour le modifier. Côté serveur, ajouter, modifier et supprimer un rôle demandent `all.project.role.add`, `all.project.role.update` et `all.project.role.delete`.

Droits requis : `all.project` pour voir « Liste des utilisateurs » dans Paramètres; `all` pour les actions d’ajout et d’édition relevées dans `user/list/Layout.vue`. Côté serveur : lister les utilisateurs du projet demande `all`; en ajouter ou en retirer est réservé aux super-administrateurs et aux détenteurs de `all`; modifier un utilisateur demande `all.project.user.update`. Pour un utilisateur sélectionné, le profil détaille ses affectations (calendriers, catégories, documents, dossiers, groupes, menus, actions et étapes de commande, questionnaires) et ses permissions. Après modification, la liste et le profil sont mis à jour.

Sources d’interface : `components/layouts/setting/Layout.vue`, `components/user/list/Layout.vue`, `components/project/profile/Profile.vue`, `components/project/profile/blocs/role/Bloc.vue`, `router/index.js`, `lang/fr.json`.

## Questions fréquentes
**Comment ouvrir les utilisateurs du projet ?** Dans « Paramètres », choisissez « Liste des utilisateurs ».

**Qui peut ajouter ou modifier un utilisateur ?** Dans l’interface, le bouton « Ajouter » demande `all`; côté serveur, l’ajout est permis aux super-administrateurs et aux détenteurs de `all`, et la modification demande `all.project.user.update`.

**Où se trouvent les rôles ?** Dans le bloc « Rôles » de la fiche projet.

## Pour aller plus loin
Créer, bloquer, débloquer un utilisateur, se connecter à sa place : sujet `gestion-utilisateurs`. Ce que chacun voit : sujet `affectations-utilisateurs-roles`. Liste de tous les droits : sujet `droits-permissions`.
