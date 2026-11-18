# Carte

## À quoi ça sert
Le CRM contient une interface de carte et des champs de coordonnées géographiques (latitude et longitude) sur les prospects. Le projet comprend aussi des composants et services liés à Google Maps.

## Comment faire
Utilise la carte dans l’interface du CRM pour visualiser les prospects géolocalisés. Les outils de recherche peuvent restituer les champs autorisés lorsqu’ils sont demandés.

## Règles
Les données visibles restent soumises aux droits du projet et à la visibilité des prospects.

## Limites
La carte n’a pas de route API propre : elle charge les prospects du tableau (identifiant, latitude, longitude) avec les mêmes filtres et droits que le tableau, 1000 marqueurs au plus par chargement. Un prospect sans latitude ni longitude n’a pas de marqueur. Un service Google (`Services/Google/Map.php`) sert aux recherches de lieux et d’itinéraires.

## Dans l'interface
Chemin : barre du haut du projet > « MAP » ouvre la route `map`. Depuis le tableau, « MAP » est également proposé avec `all.map`.

1. Sur la carte, utilisez le champ de localisation puis le rayon « R km » pour actualiser les prospects autour d’un lieu.
2. Les icônes de filtre, calendrier, palette et export ouvrent respectivement les filtres, le filtre d’événements, le choix des couleurs et l’export. Leurs aides intégrées s’intitulent « MAP - Filtre » (affiner la recherche), « MAP - Colorer les marqueurs » (colorer par catégorie de filtre), « MAP - Localisation des utilisateurs » (position des véhicules des utilisateurs), « MAP - Export » (exporter en XLSX les prospects sélectionnés dans le tableau) et « MAP - Prospects sans lat/lng ». L’icône calendrier ouvre le filtre des événements. Pour tracer une zone de recherche, maintenez CTRL et glissez pour un cercle, ou maintenez SHIFT et cliquez plusieurs points pour un polygone.
3. L’icône engrenage recherche les prospects sans latitude/longitude; les icônes étoile, bulle et enveloppe ouvrent des actions de menus, SMS ou message. Elles agissent sur les prospects de la carte : l’étoile ouvre les actions de menus, la bulle envoie un SMS en masse et l’enveloppe un message en masse; ces envois suivent les droits d’envoi de SMS et de message du projet. L’outil « MAP - Prospects sans lat/lng » relance la recherche de l’adresse des prospects absents de la carte.

Droits requis : l’entrée « MAP » de la barre du haut et celle du tableau sont toutes deux conditionnées par `all.map`. Les actions sur les prospects restent soumises aux droits correspondants. Après recherche, les marqueurs et fiches des prospects géolocalisés sont présentés; les détails des résultats sans coordonnées sont dans un panneau distinct.

Sources d’interface : `components/layouts/Header.vue`, `components/map/Map.vue`, `components/map/filters/`, `components/prospect/table/CardMenus.vue`, `router/index.js`, `lang/fr.json` (`header.map`, `map.*`).

## Questions fréquentes
**Comment centrer les prospects autour d’un lieu ?** Saisissez un lieu dans le champ de carte et ajustez « R km ».

**Pourquoi un prospect n’a-t-il pas de marqueur ?** La carte s’appuie sur les coordonnées; les fiches sans latitude/longitude sont consultables via l’icône engrenage.

**Comment filtrer par événement ?** Utilisez l’icône calendrier, qui ouvre le filtre des événements : il liste les calendriers et les utilisateurs pour choisir les événements à afficher.
