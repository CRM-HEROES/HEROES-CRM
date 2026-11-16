# Campagnes

## À quoi ça sert
Les campagnes historiques regroupent des règles, opérateurs et actions et peuvent être liées à des prospects et commandes. Le CRM comporte aussi un système de workflows distinct, avec brouillons versionnés et exécutions.

## Comment faire
Les routes projet permettent de consulter, créer, modifier et supprimer des campagnes, de vérifier leurs règles, et de consulter les prospects ou commandes correspondants.

## Règles
La vérification des règles renvoie une erreur si le traitement échoue. Les actions de campagne peuvent entraîner des communications ou d’autres effets; vérifie leur configuration avant toute activation.

## Limites
Les fréquences, dates de traitement et règles de reprise dépendent de la campagne et de son exécution. Leur comportement exact est À vérifier dans les contrôleurs et tâches concernés.

## Dans l'interface
La route `campaign` charge `components/campaign/Campaign.vue`. **L’entrée « Campagnes » dans la barre du haut est commentée et inactive** dans `Header.vue`; sa présence dans `fr.json` ne signifie donc pas qu’elle est affichée.

1. Ouvrez la page campagne par une route/profondeur de lien disponible dans le projet; le chemin de navigation actif vers cette route est À vérifier.
2. Dans la page, les commandes de création et les éléments de règles/actions sont présentés dans le panneau latéral. « Nouvelle campagne » est le libellé traduit du modèle de création; la manière de la publier ou de l’activer est À vérifier.
3. Les composants permettent de composer visuellement des règles, opérateurs et actions par glisser-déposer; sélectionnez un élément pour afficher les options associées.

Droits requis : l’ancien lien d’en-tête était conditionné par `all.project.campaign.view`, mais il est commenté. Les droits applicables à la route et à ses actions sont À vérifier. Après édition, les éléments de campagne apparaissent sur la zone de travail; l’exécution réelle dépend de la configuration.

Sources d’interface : `components/layouts/Header.vue` (entrée commentée), `components/campaign/Campaign.vue`, `components/campaign/Rules.vue`, `router/index.js`, `lang/fr.json` (`header.campaigns`, `campaign.*`).

## Questions fréquentes
**Pourquoi « Campagnes » n’est-elle pas dans la barre ?** Son entrée dans `Header.vue` est commentée et inactive.

**La route des campagnes existe-t-elle ?** Oui, `campaign` et `campaign.show` sont déclarées dans le routeur.

**Comment ouvrir la page sans l’entrée de barre ?** Le lien actif est À vérifier; n’inventez pas une action de navigation.
