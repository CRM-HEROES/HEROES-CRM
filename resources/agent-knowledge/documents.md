# Documents

## À quoi ça sert
Le CRM gère des modèles et fichiers de documents au niveau du projet et des prospects. Un document de prospect peut être rendu en PDF à partir d’un modèle.

## Comment faire
Utilise les routes de documents du projet pour gérer modèles, fichiers, pages et champs. La route de document d’un prospect rend le modèle associé pour ce prospect; selon le modèle, le PDF peut aussi être enregistré dans un dossier et attaché à sa fiche.

## Règles
Un modèle rendu pour un prospect doit appartenir au projet et être destiné aux prospects. Les fichiers sont servis par les routes dédiées du CRM.

## Limites
Un modèle de document a un nom (obligatoire), une description, une destination (prospects, par exemple) et un dossier optionnel. Les fichiers de fond d’un modèle sont uniquement des PDF; les polices sont des fichiers .ttf (Normal ou Gras, style Normal ou Italic). Une page se crée blanche ou à partir d’un fichier, avec une largeur et une hauteur en millimètres. Les champs d’une page sont de type Texte, HTML ou QR Code. Le serveur ne fixe pas de taille maximale propre aux PDF de modèle; la limite est celle du serveur web.

## Dans l'interface
Chemin : barre du haut > « Documents » ouvre `document/Document.vue`; la route peut afficher un document particulier via `document.show`. La fiche prospect propose également « Document ».

1. Ouvrez « Documents » puis sélectionnez un modèle/document dans la liste.
2. Dans l’éditeur, utilisez « Page » pour ajouter une page, choisissez un champ, puis renseignez les sections visibles telles que « Texte / Format » et « Contenu ».
3. Pour ajouter un fichier à un dossier du prospect, ouvrez la fiche > « Fichier », sélectionnez un dossier puis « Ajouter ». L’ajout de dossier requiert `all.project.folder.add`.
4. Le bouton d’édition du tableau de commande est « Modifier » lorsqu’un champ de type tableau de commandes est sélectionné. Le PDF d’un prospect est produit à partir d’un modèle destiné aux prospects. Dans l’éditeur du modèle, le choix de dossier s’affiche sous la forme « Générer dans le dossier {folder} » : si un dossier est défini, le PDF généré y est enregistré et attaché à la fiche du prospect; sinon il est seulement rendu.

Droits requis : l’entrée « Documents » de la barre nécessite `all.project.document.view`. Côté serveur, créer, modifier et supprimer un modèle demandent `all.project.document.add`, `.update` et `.delete`; ajouter, modifier ou supprimer ses pages, champs et fichiers vérifie seulement que le modèle appartient au projet. Après ajout ou édition, les pages et champs sont visibles dans l’éditeur; le PDF rendu est associé au prospect selon la configuration du modèle.

Sources d’interface : `components/layouts/Header.vue`, `components/document/Document.vue`, `components/prospect/profile/Profile.vue`, `components/prospect/file/Slide.vue`, `components/prospect/document/Slide.vue`, `router/index.js`, `lang/fr.json` (`header.documents`, `document.*`, `prospect.*`).

## Questions fréquentes
**Comment ouvrir l’éditeur ?** Cliquez « Documents » puis sélectionnez un document.

**Comment ajouter une page ?** Dans l’éditeur, utilisez « Page ».

**Pourquoi « Documents » n’apparaît pas ?** La barre vérifie `all.project.document.view`.
