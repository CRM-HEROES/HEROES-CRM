# Campagnes

## À quoi ça sert
Les campagnes historiques regroupent des règles, opérateurs et actions et peuvent être liées à des prospects et commandes. Le CRM comporte aussi un système de workflows distinct, avec brouillons versionnés et exécutions.

## Comment faire
Les routes projet permettent de consulter, créer, modifier et supprimer des campagnes, de vérifier leurs règles, et de consulter les prospects ou commandes correspondants.

## Règles
La vérification des règles renvoie une erreur si le traitement échoue. Les actions de campagne peuvent entraîner des communications ou d’autres effets; vérifie leur configuration avant toute activation.

## Limites
Une campagne est traitée seulement si elle est activée (« Activé »), ni en attente ni déjà en cours de traitement. Une campagne cible les prospects, les devis ou les RDVs (« Pour les prospects », « Pour les devis », « Pour les RDVs »). Les fréquences sont : « Une seule fois », « Toutes les 15mn », « Toutes les 30mn », « Toutes les heures », « Tous les jours » et « Tous les mois ». La date de la campagne est son début, jamais une heure exacte : « Une seule fois » s’exécute au premier passage du planificateur après cette date si elle n’a jamais été exécutée; « Tous les jours » et « Tous les mois » s’exécutent à l’heure de la date (et au même jour du mois, le 31 devenant le dernier jour des mois plus courts). Une fiche déjà traitée n’est retraitée que si « Retraiter les prospects déjà traités » est coché. Ces exécutions supposent que le planificateur Laravel tourne sur le serveur. Une campagne peut être transformée en workflow par la commande `workflows:convert-campaign`.

## Dans l'interface
La route `campaign` charge `components/campaign/Campaign.vue`. **L’entrée « Campagnes » dans la barre du haut est commentée et inactive** dans `Header.vue`; sa présence dans `fr.json` ne signifie donc pas qu’elle est affichée.

1. Aucune entrée de menu n’est active : ouvrez l’adresse `/project/{projet}/campaign` (ou `/project/{projet}/campaign/{id}` pour une campagne précise).
2. Dans la page, les commandes de création et les éléments de règles/actions sont présentés dans le panneau latéral. Le bouton « Nouvelle campagne » crée une campagne; activez-la avec « Activé », choisissez sa fréquence et sa date, puis utilisez « Vérifier les règles » (une erreur marque la campagne en erreur) et « Voir les prospects concernés » pour contrôler ce qu’elle cible avant de la laisser s’exécuter.
3. Les composants permettent de composer visuellement des règles, opérateurs et actions par glisser-déposer; sélectionnez un élément pour afficher les options associées.

Droits requis : l’ancien lien d’en-tête était conditionné par `all.project.campaign.view`, mais il est commenté. Côté serveur, créer une campagne demande `all.project.campaign.add`, la modifier `all.project.campaign.update` et la supprimer `all.project.campaign.delete`; consulter ne demande pas de droit particulier. Après édition, les éléments de campagne apparaissent sur la zone de travail; l’exécution réelle dépend de la configuration.

Sources d’interface : `components/layouts/Header.vue` (entrée commentée), `components/campaign/Campaign.vue`, `components/campaign/Rules.vue`, `router/index.js`, `lang/fr.json` (`header.campaigns`, `campaign.*`).

## Questions fréquentes
**Pourquoi « Campagnes » n’est-elle pas dans la barre ?** Son entrée dans `Header.vue` est commentée et inactive.

**La route des campagnes existe-t-elle ?** Oui, `campaign` et `campaign.show` sont déclarées dans le routeur.

**Comment ouvrir la page sans l’entrée de barre ?** Saisissez l’adresse `/project/{projet}/campaign`; n’inventez pas d’entrée de menu.
