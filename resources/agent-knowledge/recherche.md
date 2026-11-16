# Recherche

## À quoi ça sert
La recherche globale de l’en-tête propose des résultats de recherche accessibles depuis les vues CRM.

## Comment faire
Cliquez dans le champ « Rechercher ... », saisissez un terme et choisissez un résultat proposé.

## Règles
Les résultats sont limités aux types et données que le code de recherche rend disponibles ainsi qu’aux droits de l’utilisateur.

## Limites
Les règles précises de correspondance, les types recherchés dans chaque contexte et les raccourcis clavier sont À vérifier.

## Dans l'interface
Chemin : champ de recherche dans l’en-tête commun (`components/layouts/Search.vue`), avec le placeholder `Rechercher ...` formé depuis la clé fr.json `search`.

1. Cliquez le champ puis saisissez le nom ou le terme recherché.
2. Si une liste de résultats apparaît, choisissez une ligne pour ouvrir l’élément correspondant.
3. Utilisez les flèches et la validation clavier uniquement si elles sont proposées par l’autocomplétion; le comportement exact est À vérifier.

Droits requis : être connecté; les résultats visibles respectent les accès du compte. Aucun bouton de création ni filtre dédié n’est confirmé dans ce composant. Après sélection, la page de l’élément s’ouvre.

Sources d’interface : `components/layouts/Header.vue`, `components/layouts/Search.vue`, `components/layouts/search/`, `router/index.js`, `lang/fr.json`.

## Questions fréquentes
**Où se trouve la recherche ?** Dans le champ « Rechercher ... » de l’en-tête.

**Comment ouvrir un résultat ?** Sélectionnez-le dans la liste affichée sous le champ.

**Quels champs sont recherchés ?** La liste exacte dépend du code de recherche; À vérifier.
