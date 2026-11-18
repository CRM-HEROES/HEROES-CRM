# Commandes

## À quoi ça sert
Les commandes sont rattachées à des prospects et peuvent inclure des statuts, étapes, produits et commissions.

## Comment faire
Consulte les statuts disponibles dans la configuration du projet. Les commandes sont accessibles depuis les routes projet et depuis la fiche prospect; les outils d’agent peuvent les rechercher et, selon les permissions accordées, en créer, modifier ou supprimer.

## Règles
Une commande doit être rattachée à un prospect visible du projet. Les statuts sont propres au projet; utilise leurs identifiants issus de `project_config`.

## Limites
Chaque ligne de produit d’une commande a un prix unitaire, une taxe, une quantité et une indication taxe incluse ou non : le prix hors taxe vaut le prix divisé par (1 + taxe) si le prix saisi est taxe incluse, le total d’une ligne est le prix unitaire multiplié par la quantité. La commission d’un utilisateur est définie par produit et par action, avec une valeur et un type : pour une commission fixe, elle vaut quantité × valeur; sinon, la valeur est appliquée au prix du produit (avec ou sans taxe selon l’indicateur de la commission) et multipliée par la quantité. Les commissions se règlent avec le droit `all.project.user.commission.add`. Le calcul exact est écrit dans `Prospect/Order/CommissionController` (formule en commentaire); en cas de doute sur un montant, ne le recalcule pas : lis-le dans la commande.

## Dans l'interface
Chemin : barre du haut > « Devis » ouvre le tableau de devis (`order/Table/Layout.vue`). Une fiche prospect propose aussi « Devis ».

1. Dans le tableau, choisissez « Nouveau devis » pour ouvrir la création si `all.order.add` est accordé.
2. Sélectionnez les colonnes via « Paramètre des colonnes ». Les statuts, étapes, produits et actions visibles dépendent des paramètres du projet.
3. Depuis la fiche prospect, choisissez « Devis », puis « Nouveau devis » si `all.prospect.order.add` est accordé.
4. Pour supprimer des lignes sélectionnées dans le tableau, l’action « Supprimer définitivement » est affichée avec `all.order.delete`; une confirmation est demandée.

Droits requis : `all.order.add` pour créer dans le tableau, `all.order.delete` pour la suppression groupée, `all.prospect.order.add` pour créer depuis la fiche. Après validation, le devis apparaît dans la liste et dans les données du prospect associé.

Sources d’interface : `components/layouts/Header.vue`, `components/order/Table/Layout.vue`, `components/order/Table/CardMenus.vue`, `components/prospect/profile/Profile.vue`, `components/prospect/order/Slide.vue`, `router/index.js`, `lang/fr.json` (`header.order`, `order.table.*`, `prospect.order.*`).

## Questions fréquentes
**Comment créer un devis depuis un prospect ?** Ouvrez « Devis » sur la fiche puis « Nouveau devis »; `all.prospect.order.add` est requis.

**Comment modifier les colonnes ?** Cliquez « Paramètre des colonnes » dans le tableau.

**Pourquoi la suppression n’apparaît-elle pas ?** Le tableau exige `all.order.delete` pour « Supprimer définitivement ».

## Pour aller plus loin
Produits, états, étapes, actions, commissions et tableau des devis : sujet `devis-produits-commissions`.
