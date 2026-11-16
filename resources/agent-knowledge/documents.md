# Documents

## À quoi ça sert
Le CRM gère des modèles et fichiers de documents au niveau du projet et des prospects. Un document de prospect peut être rendu en PDF à partir d’un modèle.

## Comment faire
Utilise les routes de documents du projet pour gérer modèles, fichiers, pages et champs. La route de document d’un prospect rend le modèle associé pour ce prospect; selon le modèle, le PDF peut aussi être enregistré dans un dossier et attaché à sa fiche.

## Règles
Un modèle rendu pour un prospect doit appartenir au projet et être destiné aux prospects. Les fichiers sont servis par les routes dédiées du CRM.

## Limites
Les formats autorisés et les quotas varient selon l’opération; ils sont À vérifier dans les validations des contrôleurs concernés.

## Dans l'interface
Chemin : barre du haut > « Documents » ouvre `document/Document.vue`; la route peut afficher un document particulier via `document.show`. La fiche prospect propose également « Document ».

1. Ouvrez « Documents » puis sélectionnez un modèle/document dans la liste.
2. Dans l’éditeur, utilisez « Page » pour ajouter une page, choisissez un champ, puis renseignez les sections visibles telles que « Texte / Format » et « Contenu ».
3. Pour ajouter un fichier à un dossier du prospect, ouvrez la fiche > « Fichier », sélectionnez un dossier puis « Ajouter ». L’ajout de dossier requiert `all.project.folder.add`.
4. Le bouton d’édition du tableau de commande est « Modifier » lorsqu’un champ de type tableau de commandes est sélectionné. Les commandes de génération/export de PDF sont À vérifier dans le contexte du modèle.

Droits requis : l’entrée « Documents » de la barre nécessite `all.project.document.view`. Les droits de modification d’un modèle et de ses fichiers dépendent de la ressource et sont À vérifier. Après ajout ou édition, les pages et champs sont visibles dans l’éditeur; le PDF rendu est associé au prospect selon la configuration du modèle.

Sources d’interface : `components/layouts/Header.vue`, `components/document/Document.vue`, `components/prospect/profile/Profile.vue`, `components/prospect/file/Slide.vue`, `components/prospect/document/Slide.vue`, `router/index.js`, `lang/fr.json` (`header.documents`, `document.*`, `prospect.*`).

## Questions fréquentes
**Comment ouvrir l’éditeur ?** Cliquez « Documents » puis sélectionnez un document.

**Comment ajouter une page ?** Dans l’éditeur, utilisez « Page ».

**Pourquoi « Documents » n’apparaît pas ?** La barre vérifie `all.project.document.view`.
