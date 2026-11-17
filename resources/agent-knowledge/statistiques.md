# Statistiques

## À quoi ça sert
Les tableaux de bord et statistiques présentent des valeurs et graphiques portant notamment sur les prospects, SMS, appels, rendez-vous, commandes, fichiers et messages.

## Comment faire
L’API de statistiques du projet fournit des valeurs et des graphiques sur une période `from`–`to`, ainsi que des séries journalières. Les outils d’agent proposent un catalogue de mesures, agrégations et comparaisons de périodes.

## Règles
Précise toujours la période et le périmètre des résultats. Les outils d’agent appliquent la visibilité de l’utilisateur dans le projet.

## Limites
Les mesures et dimensions disponibles dépendent du catalogue; consulte-le plutôt que de déduire une métrique. Les règles de calcul de chaque graphique sont À vérifier dans sa définition.

## Dans l'interface
Chemin : barre du haut du projet > « Statistiques » ouvre la route `stat` et le constructeur `stat-builder/StatBuilder.vue`.

1. Ouvrez « Statistiques ».
2. Consultez le tableau de bord et les blocs proposés. La sélection des métriques et la création/configuration d’un graphique dépendent des contrôles visibles dans le constructeur; les libellés précis sont À vérifier.
3. Précisez la période et le périmètre avant d’interpréter un résultat. Les interfaces de période ne sont pas entièrement confirmées ici : À vérifier.

Droit requis : l’entrée de barre est conditionnée par `all.stat`. Après chargement, l’écran affiche les mesures ou graphiques accessibles; les règles de calcul de chaque mesure restent À vérifier.

Sources d’interface : `components/layouts/Header.vue`, `components/stat-builder/StatBuilder.vue`, `components/stat2/Stat.vue`, `components/stat/Stat.vue`, `router/index.js`, `lang/fr.json` (`header.stat`, `stat.*`).

## Questions fréquentes
**Comment ouvrir les statistiques ?** Cliquez « Statistiques » dans l’en-tête du projet.

**Pourquoi cette entrée est-elle absente ?** Elle nécessite `all.stat`.

**Comment interpréter une mesure ?** Vérifiez sa définition et sa période; les calculs détaillés sont À vérifier.
