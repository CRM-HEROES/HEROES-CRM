# Projets

## À quoi ça sert
Un projet regroupe les prospects, utilisateurs, champs, filtres, calendriers, documents et autres réglages du CRM.

## Comment faire
L’accueil donne accès aux projets. La fiche du projet présente des blocs de configuration et des raccourcis pour créer certaines ressources.

## Règles
Les données sont rattachées au projet. Les actions de gestion dépendent des permissions de projet.

## Limites
Les options disponibles varient selon le projet et le compte; les conditions de chaque bloc sont À vérifier.

## Dans l'interface
Chemin : « Accueil » > sélection du projet dans « Vos tableaux » ouvre la route `project.show`. La fiche est `components/project/profile/Profile.vue`.

1. Ouvrez un projet depuis la liste des projets.
2. Pour créer un élément, utilisez l’icône correspondante en haut de la fiche : « Canaux de discussion », « Dossiers », « Catégories de filtres », « Champs de prospects », « Groupes », « Rôles » ou « Produits ». Les formulaires s’ouvrent depuis ces icônes.
3. Parcourez les blocs de la fiche, notamment « Utilisateurs », « Imports », « Agents IA », « Calendriers », « Documents », « Menus » et « Lignes téléphoniques ».
4. Pour changer le logo, l’action est « Modifier » dans la zone du logo (`project.profile.logo.change`).

Droit requis : l’accès au projet; droits propres à chaque ressource pour créer ou modifier. Les vérifications exactes par icône sont À vérifier. La fiche affiche les données et blocs chargés; le résultat d’une modification dépend de la ressource.

Sources d’interface : `components/project/profile/Profile.vue`, `components/project/profile/ProfileBloc.vue`, `components/project/profile/blocs/`, `router/index.js`, `lang/fr.json` (`project.profile.*`).

## Questions fréquentes
**Où gérer les ressources d’un projet ?** Ouvrez sa fiche depuis « Accueil » et parcourez ses blocs.

**Comment créer un groupe ou un produit ?** Utilisez l’icône « Groupes » ou « Produits » de la fiche projet; le droit requis exact est À vérifier.

**Pourquoi un bloc est-il absent ?** Le chargement ou les permissions peuvent varier; la règle précise est À vérifier.
