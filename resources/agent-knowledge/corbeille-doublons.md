# Corbeille et doublons

## À quoi ça sert
La corbeille garde les éléments supprimés pour les restaurer. La recherche de doublons repère les prospects en double et propose de les nettoyer.

## Comment faire
Corbeille : icône engrenage > « Paramètres » > « Corbeille ». Doublons : dans le tableau des prospects, « Rechercher des duplications ».

## Règles
La corbeille liste, par type, les éléments supprimés du projet : prospects, catégories et filtres, canaux de discussion et messages, dossiers et fichiers, produits, et d’autres éléments. Les doublons se calculent sur les champs choisis pour la recherche; dans le tableau, un doublon n’est signalé que sur l’e-mail, le mobile et le téléphone configurés comme uniques dans le projet.

## Limites
La recherche de doublons recalcule tout le projet; côté agent IA, elle est réservée aux administrateurs. Les trois actions de nettoyage sont irréversibles pour les prospects supprimés définitivement : vérifiez toujours la liste avant de confirmer. L’action « Ecraser les données modifiées des anciens prospects par celles des nouveaux » est proposée dans l’interface, mais son effet n’a pas été confirmé dans le code consulté : ne la conseille pas sans le dire.

## Dans l'interface
Chemin corbeille : icône engrenage > « Paramètres » > « Corbeille » (`components/trash/`). Chemin doublons : tableau « Prospects » > menu du pied du tableau > « Rechercher des duplications » (`components/prospect/table/menu/Menus.vue`).

1. Corbeille : choisissez le type d’élément, puis utilisez « Restaurer » pour le remettre en place ou « Supprimer » pour l’effacer définitivement (une confirmation « Voulez-vous vraiment effectuer cette suppression ? » est demandée).
2. Doublons : la fenêtre « Recherche de doublons » demande « Chercher par rapport aux champs ... » : cochez les champs à comparer (« Tous » les sélectionne). Le réglage d’un champ comme unique demande `all.project.field.update`.
3. « Choisir une action à faire » propose : « Supprimer les anciens prospects dupliqués », « Supprimer les duplications de prospects dans l'import » ou « Ecraser les données modifiées des anciens prospects par celles des nouveaux ». Confirmez ensuite l’action.

Droits requis : « Rechercher des duplications » n’est affiché qu’avec `all.prospect.import`; la restauration et la suppression définitive suivent les droits sur les éléments concernés. Après une action, les listes se mettent à jour.

Sources d’interface : `components/trash/`, `components/duplicate/`, `components/prospect/table/menu/Menus.vue`, `components/import/process/Duplicate.vue`, `lang/fr.json` (`duplicate.*`, `restore`, `delete`).

## Questions fréquentes
**Comment récupérer un prospect supprimé ?** « Paramètres » > « Corbeille » > type « Prospects » > « Restaurer ».

**Comment trouver les doublons ?** « Rechercher des duplications » dans le tableau des prospects, en choisissant les champs à comparer.

**Les doublons sont-ils supprimés automatiquement ?** Non : il faut choisir une action et la confirmer.
