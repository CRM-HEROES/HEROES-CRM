# Pipeline

## À quoi ça sert
La vue Pipeline organise les prospects en colonnes associées aux filtres/étiquettes de la pipeline choisie.

## Comment faire
Ouvrez « Pipeline » depuis Paramètres, choisissez une pipeline, puis consultez ses colonnes et prospects.

## Règles
Le composant charge au plus 500 prospects dans sa vue. La possibilité de déplacer des cartes dépend des règles de mise à jour des prospects et de la configuration des labels.

## Limites
La signification métier des colonnes et les règles de transition ne sont pas déduites par la vue; À vérifier auprès de la configuration du projet.

## Dans l'interface
Chemin : icône engrenage > « Paramètres » > « Pipeline » ouvre les routes `pipeline` / `pipeline.show` (`components/pipeline/list/Pipeline.vue`).

1. Sélectionnez une pipeline dans la liste « Choisir une pipeline ... ».
2. Pour en créer une, utilisez l’icône « + »; le formulaire a le titre « Nouveau pipeline ».
3. Pour modifier la pipeline active, utilisez l’icône crayon; le formulaire d’édition est ouvert par le composant.
4. Les colonnes affichent les prospects associés; leur réorganisation est prise en charge par glisser-déposer. Les actions de déplacement d’un prospect entre colonnes sont À vérifier dans les contrôles de la carte.

Droits requis : l’entrée « Pipeline » est affichée quand un projet est sélectionné; les droits précis de création, modification et déplacement ne sont pas testés ici : À vérifier. Après sélection, les colonnes et les nombres de prospects s’affichent.

Sources d’interface : `components/layouts/setting/Layout.vue`, `components/pipeline/list/Pipeline.vue`, `components/pipeline/list/Column.vue`, `router/index.js`, `lang/fr.json` (`pipeline.add.title`).

## Questions fréquentes
**Comment choisir une pipeline ?** Utilisez la liste « Choisir une pipeline ... ».

**Comment en créer une ?** Cliquez l’icône « + »; le formulaire s’intitule « Nouveau pipeline ».

**Pourquoi aucun prospect n’apparaît ?** La vue dépend de la pipeline sélectionnée et de ses données; la cause précise est À vérifier.
