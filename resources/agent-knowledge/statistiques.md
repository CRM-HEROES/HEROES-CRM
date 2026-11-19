# Statistiques

## À quoi ça sert
Les tableaux de bord et statistiques présentent des valeurs et graphiques portant notamment sur les prospects, SMS, appels, rendez-vous, commandes, fichiers et messages.

## Comment faire
L’API de statistiques du projet fournit des valeurs et des graphiques sur une période `from`–`to`, ainsi que des séries journalières. Les outils d’agent proposent un catalogue de mesures, agrégations et comparaisons de périodes.

## Règles
Précise toujours la période et le périmètre des résultats. Les outils d’agent appliquent la visibilité de l’utilisateur dans le projet.

## Limites
Les mesures et dimensions disponibles dépendent du catalogue; consulte-le plutôt que de déduire une métrique. Le constructeur interroge six sources : Appels, SMS, Commandes, Prospects, Rendez-vous et Utilisateurs. Chaque mesure compte des enregistrements (« Nombre d’appels », « Nombre de SMS », etc.) ou agrège avec somme, moyenne, minimum ou maximum quand c’est pertinent, par exemple la durée des appels en minutes. Les regroupements courants sont Jour, Semaine, Mois, Jour de la semaine et Heure. Dans le tableau des utilisateurs, « Performance » est le taux d’appels aboutis et « Suivi » le nombre d’ajouts de suivi effectués pendant la période. Le sens « Sortant » ou « Entrant » d’un appel et « Envoyé » ou « Reçu » d’un SMS vient du champ expéditeur : sortant ou envoyé quand l’utilisateur en est l’auteur.

## Dans l'interface
Chemin : barre du haut du projet > « Statistiques » ouvre la route `stat` et le constructeur `stat-builder/StatBuilder.vue`.

1. Ouvrez « Statistiques ».
2. Le tableau de bord est personnel à chaque utilisateur et s’enregistre tout seul. Utilisez « Ajouter un widget » : un assistant en plusieurs étapes demande comment afficher les données, puis « Configurer les données », puis « Prêt à ajouter au dashboard ». « Personnaliser » permet de déplacer et de redimensionner les widgets (puis « Terminer »), « Masqués » ré-affiche ceux qui sont cachés, et le menu d’options propose « Tout actualiser », « Réinitialiser le dashboard » et « Ancienne version ».
3. Précisez la période et le périmètre avant d’interpréter un résultat. Les filtres globaux sont Période, Utilisateur, Équipe, Rôle et Statut (bouton « Filtre » pour les activer, « Réinitialiser » pour les effacer). Les périodes proposées sont : Aujourd'hui, Hier, 7 derniers jours, 30 derniers jours, 90 derniers jours, Cette semaine, Semaine dernière, Ce mois, Mois dernier, Cette année et Personnalisée (dates de début et de fin).

Droit requis : l’entrée de barre est conditionnée par `all.stat`. Après chargement, l’écran affiche les mesures ou graphiques accessibles; le tableau de bord enregistré est celui de l’utilisateur connecté dans ce projet. Côté agent, la période personnalisée est limitée à 366 jours.

Sources d’interface : `components/layouts/Header.vue`, `components/stat-builder/StatBuilder.vue`, `components/stat2/Stat.vue`, `components/stat/Stat.vue`, `router/index.js`, `lang/fr.json` (`header.stat`, `stat.*`).

## Questions fréquentes
**Comment ouvrir les statistiques ?** Cliquez « Statistiques » dans l’en-tête du projet.

**Pourquoi cette entrée est-elle absente ?** Elle nécessite `all.stat`.

**Comment interpréter une mesure ?** Vérifiez sa source, son regroupement et sa période : une même valeur change selon la période et les filtres globaux actifs.

## Pour aller plus loin
Les droits des statistiques : sujet `droits-permissions`.
