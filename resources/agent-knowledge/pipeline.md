# Pipeline

## À quoi ça sert
La vue Pipeline organise les prospects en colonnes associées aux filtres/étiquettes de la pipeline choisie.

## Comment faire
Ouvrez « Pipeline » depuis Paramètres, choisissez une pipeline, puis consultez ses colonnes et prospects.

## Règles
Le composant charge au plus 500 prospects dans sa vue. La possibilité de déplacer des cartes dépend des règles de mise à jour des prospects et de la configuration des labels.

## Limites
Une pipeline est une suite ordonnée de labels : chaque colonne est un label et un prospect est dans la colonne dont il porte le label. Il n’existe pas de règle de transition : n’importe quelle carte peut être déplacée vers n’importe quelle colonne. La signification métier de chaque colonne vient du nom du label, défini dans la configuration du projet.

## Dans l'interface
Chemin : icône engrenage > « Paramètres » > « Pipeline » ouvre les routes `pipeline` / `pipeline.show` (`components/pipeline/list/Pipeline.vue`).

1. Sélectionnez une pipeline dans la liste « Choisir une pipeline ... ».
2. Pour en créer une, utilisez l’icône « + »; le formulaire a le titre « Nouveau pipeline ».
3. Pour modifier la pipeline active, utilisez l’icône crayon; le formulaire d’édition est ouvert par le composant.
4. Les colonnes affichent les prospects associés; leur réorganisation est prise en charge par glisser-déposer. Pour déplacer un prospect, glissez sa carte vers une autre colonne : le CRM retire ses autres labels de cette pipeline, ajoute celui de la colonne cible et déclenche les automatisations liées à l’ajout de ce label.

Droits requis : l’entrée « Pipeline » est affichée quand un projet est sélectionné; créer une pipeline demande `all.project.pipeline.add`, la modifier `all.project.pipeline.update` et la supprimer `all.project.pipeline.delete`. Déplacer une carte ne vérifie pas de droit particulier côté serveur : seule la cohérence entre le projet, la pipeline et le label est contrôlée. Après sélection, les colonnes et les nombres de prospects s’affichent.

Sources d’interface : `components/layouts/setting/Layout.vue`, `components/pipeline/list/Pipeline.vue`, `components/pipeline/list/Column.vue`, `router/index.js`, `lang/fr.json` (`pipeline.add.title`).

## Questions fréquentes
**Comment choisir une pipeline ?** Utilisez la liste « Choisir une pipeline ... ».

**Comment en créer une ?** Cliquez l’icône « + »; le formulaire s’intitule « Nouveau pipeline ».

**Pourquoi aucun prospect n’apparaît ?** La vue dépend de la pipeline sélectionnée et de ses données; causes possibles : aucun prospect visible pour l’utilisateur ne porte un label de cette pipeline, ou plus de 500 prospects correspondent (seuls 500 sont chargés).
