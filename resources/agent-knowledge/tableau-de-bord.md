# Tableau de bord

## À quoi ça sert
Le tableau de bord global présente les projets accessibles et un accès pour créer un projet. Le routeur distingue cette page d’accueil de la fiche du projet.

## Comment faire
Ouvrez « Accueil » dans la barre supérieure pour revenir au tableau de bord. Depuis « Vos tableaux », choisissez un projet ou « Nouveau projet ».

## Règles
La connexion est requise. Les projets affichés dépendent des accès du compte. La création peut être soumise aux permissions et au parcours d’inscription.

## Limites
Les cartes, métriques et critères de leur affichage sont À vérifier selon le projet et les droits.

## Dans l'interface
Chemin : barre du haut > icône maison, libellé « Accueil » (`Header.vue`, route `dashboard`) > « Vos tableaux » (`Dashboard.vue`, `fr.json` `dashboard.your_projects`).

1. Pour ouvrir un projet, sélectionnez-le dans « Vos tableaux »; le composant de destination est la fiche ou le projet correspondant. Le libellé des lignes est le nom du projet.
2. Pour démarrer la création, sélectionnez « Nouveau projet ». Les champs et étapes suivants sont À vérifier dans le parcours affiché.
3. Pour revenir au projet actuellement ouvert, sélectionnez « Accueil » puis son projet, si celui-ci apparaît dans la liste.

Droit requis : être connecté et avoir accès au projet; la condition exacte d’apparition de « Nouveau projet » est À vérifier. Après sélection, le tableau du projet s’ouvre; ses métriques exactes sont À vérifier.

Sources d’interface : `components/layouts/Header.vue`, `components/dashboard/Dashboard.vue`, `router/index.js`, `lang/fr.json`.

## Questions fréquentes
**Comment revenir à l’accueil ?** Sélectionnez « Accueil » dans la barre du haut.

**Comment ouvrir un projet ?** Choisissez-le dans « Vos tableaux ».

**Je ne vois pas un projet, que faire ?** Son accès dépend du compte; la cause précise est À vérifier.
