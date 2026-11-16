# Carte

## À quoi ça sert
Le CRM contient une interface de carte et des champs de coordonnées géographiques (latitude et longitude) sur les prospects. Le projet comprend aussi des composants et services liés à Google Maps.

## Comment faire
Utilise la carte dans l’interface du CRM pour visualiser les prospects géolocalisés. Les outils de recherche peuvent restituer les champs autorisés lorsqu’ils sont demandés.

## Règles
Les données visibles restent soumises aux droits du projet et à la visibilité des prospects.

## Limites
Aucune route API dédiée nommée « map » n’a été identifiée dans `routes/api.php`. La façon dont l’interface sélectionne les prospects, géocode les adresses et filtre les marqueurs est À vérifier.

## Dans l'interface
Chemin : barre du haut du projet > « MAP » ouvre la route `map`. Depuis le tableau, « MAP » est également proposé avec `all.map`.

1. Sur la carte, utilisez le champ de localisation puis le rayon « R km » pour actualiser les prospects autour d’un lieu.
2. Les icônes de filtre, calendrier, palette et export ouvrent respectivement les filtres, le filtre d’événements, le choix des couleurs et l’export. Ces icônes n’ont pas de libellé visible défini dans le composant : leurs intitulés sont À vérifier.
3. L’icône engrenage recherche les prospects sans latitude/longitude; les icônes étoile, bulle et enveloppe ouvrent des actions de menus, SMS ou message. Leurs conditions et permissions précises sont À vérifier.

Droits requis : la carte depuis la barre est présente dans le code de l’en-tête; l’accès depuis le tableau est conditionné par `all.map`. Les actions sur les prospects restent soumises aux droits correspondants. Après recherche, les marqueurs et fiches des prospects géolocalisés sont présentés; les détails des résultats sans coordonnées sont dans un panneau distinct.

Sources d’interface : `components/layouts/Header.vue`, `components/map/Map.vue`, `components/map/filters/`, `components/prospect/table/CardMenus.vue`, `router/index.js`, `lang/fr.json` (`header.map`, `map.*`).

## Questions fréquentes
**Comment centrer les prospects autour d’un lieu ?** Saisissez un lieu dans le champ de carte et ajustez « R km ».

**Pourquoi un prospect n’a-t-il pas de marqueur ?** La carte s’appuie sur les coordonnées; les fiches sans latitude/longitude sont consultables via l’icône engrenage.

**Comment filtrer par événement ?** Utilisez l’icône calendrier; les critères proposés sont À vérifier.
