# Recherche

## À quoi ça sert
La recherche globale de l’en-tête propose des résultats de recherche accessibles depuis les vues CRM.

## Comment faire
Cliquez dans le champ « Rechercher ... », saisissez un terme et choisissez un résultat proposé.

## Règles
Les résultats sont limités aux types et données que le code de recherche rend disponibles ainsi qu’aux droits de l’utilisateur.

## Limites
La recherche démarre à partir de 2 caractères, avec un délai de 300 ms après la dernière frappe. Dans un projet, elle propose les prospects (par prénom, nom et par les champs configurés comme recherchables dans le projet) ainsi que les éléments du projet : rôles, groupes, filtres, catégories, champs, dossiers, documents, calendriers, menus, produits, questionnaires, canaux de discussion, imports et raccourcis de réglages (par exemple SMSBOX, MTarget, Pipedrive, e-mail, UltraMsg). Hors projet (sur l’accueil), elle propose les utilisateurs et les projets. Pour les éléments du projet, la correspondance porte sur le nom et ne tient pas compte des majuscules. Les flèches du clavier ↓ et ↑ parcourent les résultats; la touche Entrée ouvre le résultat surligné.

## Dans l'interface
Chemin : champ de recherche dans l’en-tête commun (`components/layouts/Search.vue`), avec le placeholder `Rechercher ...` formé depuis la clé fr.json `search`.

1. Cliquez le champ puis saisissez le nom ou le terme recherché.
2. Si une liste de résultats apparaît, choisissez une ligne pour ouvrir l’élément correspondant.
3. Utilisez les flèches et la validation clavier uniquement si elles sont proposées par l’autocomplétion; ↓ et ↑ déplacent la sélection (en bas de liste, ↓ revient au champ), Entrée ouvre le résultat sélectionné; vider le champ réinitialise la liste des prospects du tableau.

Droits requis : être connecté; les résultats visibles respectent les accès du compte. Aucun bouton de création ni filtre dédié n’est confirmé dans ce composant. Après sélection, la page de l’élément s’ouvre.

Sources d’interface : `components/layouts/Header.vue`, `components/layouts/Search.vue`, `components/layouts/search/`, `router/index.js`, `lang/fr.json`.

## Questions fréquentes
**Où se trouve la recherche ?** Dans le champ « Rechercher ... » de l’en-tête.

**Comment ouvrir un résultat ?** Sélectionnez-le dans la liste affichée sous le champ.

**Quels champs sont recherchés ?** Le prénom, le nom et les champs de prospect marqués recherchables dans la configuration du projet (le téléphone ou l’e-mail n’en font partie que si cette option est activée).
