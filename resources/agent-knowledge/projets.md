# Projets

## À quoi ça sert
Un projet regroupe les prospects, utilisateurs, champs, filtres, calendriers, documents et autres réglages du CRM.

## Comment faire
L’accueil donne accès aux projets. La fiche du projet présente des blocs de configuration et des raccourcis pour créer certaines ressources.

## Règles
Les données sont rattachées au projet. Les actions de gestion dépendent des permissions de projet.

## Limites
Les blocs de la fiche sont des cartes que chaque utilisateur peut réorganiser (glisser-déposer par la poignée, sur ordinateur seulement) : « Utilisateurs », « Imports », « Agents IA », « Calendriers », « Documents », « Menus », « Lignes téléphoniques », etc. Aucun bloc n’est masqué par un droit dans l’interface; ce sont les actions et les données à l’intérieur qui sont refusées par le serveur sans le droit correspondant. Le bouton des paramètres d’appels n’apparaît qu’au créateur du projet, à un super-administrateur ou à un utilisateur ayant `all.project.role.update`.

## Dans l'interface
Chemin : « Accueil » > sélection du projet dans « Vos tableaux » ouvre la route `project.show`. La fiche est `components/project/profile/Profile.vue`.

1. Ouvrez un projet depuis la liste des projets.
2. Pour créer un élément, utilisez l’icône correspondante en haut de la fiche : « Canaux de discussion », « Dossiers », « Catégories de filtres », « Champs de prospects », « Groupes », « Rôles » ou « Produits ». Les formulaires s’ouvrent depuis ces icônes.
3. Parcourez les blocs de la fiche, notamment « Utilisateurs », « Imports », « Agents IA », « Calendriers », « Documents », « Menus » et « Lignes téléphoniques ».
4. Pour changer le logo, l’action est « Modifier » dans la zone du logo (`project.profile.logo.change`).

Droit requis : l’accès au projet; droits propres à chaque ressource pour créer ou modifier. Les icônes d’en-tête (« Canaux de discussion », « Dossiers », « Catégories de filtres », « Champs de prospects », « Groupes », « Rôles », « Produits ») sont toujours affichées : c’est le serveur qui refuse l’action sans le droit d’ajout correspondant du projet (par exemple `all.project.group.add`, `all.project.thread.add`, `all.project.folder.add`, `all.project.category.add`, `all.project.field.add`, `all.project.role.add`). La fiche affiche les données et blocs chargés; le résultat d’une modification dépend de la ressource.

Sources d’interface : `components/project/profile/Profile.vue`, `components/project/profile/ProfileBloc.vue`, `components/project/profile/blocs/`, `router/index.js`, `lang/fr.json` (`project.profile.*`).

## Questions fréquentes
**Où gérer les ressources d’un projet ?** Ouvrez sa fiche depuis « Accueil » et parcourez ses blocs.

**Comment créer un groupe ou un produit ?** Utilisez l’icône « Groupes » ou « Produits » de la fiche projet; créer un groupe demande `all.project.group.add`; créer un produit demande `all.project.order.product.add`.

**Pourquoi un bloc est-il absent ?** Aucun bloc n’est masqué par un droit dans l’interface : un bloc absent vient de la disposition de la fiche, ou de données que le serveur refuse de renvoyer sans le droit correspondant.

## Pour aller plus loin
Créer des champs, filtres, groupes, calendriers, dossiers, canaux, produits : sujet `configuration-projet`. Menus : sujet `menus-prospects`. Qui voit quoi : sujet `affectations-utilisateurs-roles`.
