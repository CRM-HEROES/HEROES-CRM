# Actions en masse sur les prospects

## À quoi ça sert
Dans le tableau des prospects, on peut sélectionner plusieurs prospects et leur appliquer la même action en une fois : affecter, classer, écrire, appeler, archiver, supprimer, copier vers un autre projet, créer un rendez-vous.

## Comment faire
Dans « Prospects », cochez des prospects (ou utilisez « Sélectionner tous les prospects filtrés » pour prendre tous ceux du filtre en cours), puis choisissez l’action dans le pied du tableau. « Déselectionner les prospects » vide la sélection.

## Règles
- Une action en masse porte sur les prospects sélectionnés du projet; les prospects déjà traités ne reçoivent pas la plupart des actions (« Archiver » en a fait des prospects traités) : « Re traiter » les remet en circulation.
- Les envois en masse (« SMS » et « Message ») ne partent qu’après confirmation et ne concernent que les prospects que l’utilisateur peut écrire (sans le droit `prospectView`, seulement ceux qui lui sont affectés).
- Malgré son nom, le bouton « Supprimer définitivement » du pied du tableau met les prospects dans la corbeille : ils peuvent être restaurés. L’effacement réel se fait ensuite depuis la Corbeille (« Paramètres » > « Corbeille », bouton « Supprimer »).

## Limites
Le serveur ne contrôle pas lui-même chaque droit des actions en masse : c’est surtout l’interface qui cache les actions non autorisées. Pour un agent IA, tout envoi en masse et toute suppression passent par une validation humaine. L’action « Rechercher et remplacer » est prévue dans le code mais désactivée dans l’interface.

## Dans l'interface
Chemin : « Prospects » > pied du tableau (`components/prospect/table/CardMenus.vue`).

1. « Assigner ou rétirer des utilisateurs » : affecte ou retire des utilisateurs aux prospects sélectionnés.
2. « Assigner ou rétirer des groupes » et « Assigner ou rétirer des filtres » : classent les prospects.
3. « Copier les prospects vers un autre projet » : crée une copie dans un autre projet.
4. « SMS » et « Message » : envoient le même texte à tous (voir la fiche SMS et e-mails). « SMS » demande `all.prospect.sms`.
5. « Update » (ouvre « Modifier un champ ») : donne la même valeur d’un champ à tous les prospects sélectionnés. Le champ doit exister dans le projet.
6. « Archiver » (droit `all.prospect.archive`) marque les prospects comme traités; « Re traiter » les remet à traiter.
7. « Supprimer définitivement » (droit `all.prospect.delete`) demande une confirmation, puis envoie les prospects à la corbeille.
8. « Appeler sur Aircall », « Appeler sur Ringover » et « Appeler via CloudTalk » (droits `all.prospect.interaction.view` et `.add`) lancent des appels successifs; CloudTalk propose aussi « Parallel Dialer » (une campagne CloudTalk, limitée à un certain nombre de prospects).
9. « Nouveau RDV » crée un rendez-vous pour tous les prospects sélectionnés.
10. « Pipeline » ouvre la page des pipelines (voir la fiche Pipeline); « Exporter les prospects » (`all.prospect.export`) et « MAP » (`all.map`) travaillent sur la sélection (voir les fiches Prospection et Carte).
11. « Scanner une carte de visite » (droit `all.prospect.ocr`) ouvre une fenêtre avec « Importer » (« Choisir des fichiers ») ou « Scanner » (caméra) : les cartes sont lues ensuite et les prospects correspondants sont ajoutés au projet (« Vos fichiers importés seront prochainement scannés »).

Droits requis : seules ces actions sont conditionnées dans l’interface : « SMS » (`all.prospect.sms`), « Supprimer définitivement » (`all.prospect.delete`), « Archiver » et « Re traiter » (`all.prospect.archive`), « Appeler via CloudTalk » (`all.prospect.interaction.view` et `.add`), « Scanner une carte de visite » (`all.prospect.ocr`), « Exporter les prospects » (`all.prospect.export`) et « MAP » (`all.map`). Les autres actions (affecter des utilisateurs, des groupes ou des filtres, copier vers un autre projet, « Message », « Update », « Appeler sur Aircall », « Appeler sur Ringover ») n’ont pas de droit dédié dans l’interface : elles s’affichent dès que les prospects sélectionnés ne sont pas archivés. Après une action, le tableau se met à jour.

Sources d’interface : `components/prospect/table/CardMenus.vue`, `components/prospect/bulk/`, `components/prospect/table/menu/Menus.vue`, `lang/fr.json` (`prospect.table.footer.*`).

## Questions fréquentes
**Comment affecter 200 prospects à un commercial ?** Filtrez le tableau, « Sélectionner tous les prospects filtrés », puis « Assigner ou rétirer des utilisateurs ».

**Comment changer une valeur pour plusieurs prospects ?** Sélectionnez-les et utilisez « Update ».

**Comment supprimer plusieurs prospects ?** Sélectionnez-les puis « Supprimer définitivement » (droit `all.prospect.delete`) : ils vont dans la corbeille et se restaurent; sans le droit, le bouton n’apparaît pas.

**Pourquoi certaines actions disparaissent-elles ?** Au moins un prospect sélectionné est déjà traité (archivé) : « Re traiter » le remet en circulation.
