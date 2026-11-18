# Affectations des utilisateurs et des rôles

## À quoi ça sert
Dans le CRM, un utilisateur ne voit et n’utilise que ce qui lui est « affecté ». C’est la règle qui explique pourquoi un calendrier, un filtre, un menu, un document ou un état de devis apparaît chez un collègue et pas chez un autre.

## Comment faire
Pour affecter un élément à un utilisateur : « Paramètres » > « Liste des utilisateurs » > l’utilisateur > bloc d’affectation (par exemple « Calendriers affectés »). Pour affecter à tout un rôle : fiche du projet > bloc « Rôles » > le rôle. Dans le panneau, cochez les éléments voulus (la case « Tous » les coche tous) ou utilisez la recherche, puis fermez.

## Règles
- **Éléments concernés :** calendriers (types de RDV), catégories de filtres et leurs filtres, documents (modèles), dossiers, groupes, menus, canaux de discussion, questionnaires, actions, étapes et états de devis, produits et pipelines. Les modèles de SMS, de message et de description de rendez-vous ne sont pas concernés : tous les utilisateurs du projet voient tous les modèles.
- **Direct ou par rôle :** un élément est utilisable si on l’a affecté à l’utilisateur ou à l’un de ses rôles. Affecter à un rôle équivaut à l’affecter à tous les utilisateurs qui ont ce rôle.
- **Exceptions :** un super-administrateur et un administrateur du projet voient tout, sans affectation.
- **Prospects :** sans le droit `all.prospect.view`, un utilisateur ne voit que les prospects qu’il a créés, ceux qui lui sont affectés et ceux de ses groupes. Pour les rendez-vous, il voit notamment ceux qu’il a créés ou auxquels il participe.
- **Droits pour affecter (rubrique) :** gérer les affectations d’un utilisateur demande le droit correspondant : `all.user.calendar`, `all.user.category`, `all.user.document`, `all.user.folder`, `all.user.group`, `all.user.questionnaire`, `all.user.role`, `all.user.thread`, `all.user.order.action`, `all.user.order.step`, `all.user.order.status` et `all.user.user`.
- **Droits d’usage :** pouvoir utiliser un élément affecté ne dit pas pouvoir en créer : créer un élément demande son droit d’ajout (par exemple `all.project.calendar.add`).

## Limites
Une affectation donne accès à l’élément, pas à ses droits de modification : ceux-ci viennent des permissions (voir la fiche Droits et permissions).

## Dans l'interface
Chemin : profil d’un utilisateur ou d’un rôle > blocs d’affectation (`components/user/`, `components/role/`).

1. Ouvrez le profil : l’utilisateur (« Liste des utilisateurs ») ou le rôle (bloc « Rôles »).
2. Choisissez le bloc : « Calendriers affectés », « Catégories de filtre affectées », « Documents affectés », « Dossiers affectés », « Groupes affectés », « Menus affectés », « Actions sur les devis affectées », « Etats de devis affectés », « Etapes de devis affectées », « Questionnaires affectées », « Canaux de discussion affectés » ou « Utilisateurs affectés ».
3. Cochez les éléments; « Tous » coche toute la liste; la recherche filtre. Le bouton « Ajouter » (droit d’ajout de l’élément) permet d’en créer un sans quitter le panneau.
4. Dans le tableau des utilisateurs, des filtres comme « Non affectés à un type de RDV » ou « Non affectés à un menu » repèrent ceux à qui il manque une affectation, et des actions groupées affectent à plusieurs utilisateurs en une fois.

Droits requis : voir la rubrique sur les droits pour affecter, plus haut. Après la modification, l’utilisateur voit l’élément à sa prochaine action (rechargez la page si besoin).

Sources d’interface : `components/user/calendar/`, `components/role/calendar/`, `app/Models/Scopes/` (règles de visibilité), `lang/fr.json` (`user.profile.blocs.*`, `user.table.*`).

## Questions fréquentes
**Pourquoi un collègue ne voit-il pas un calendrier que je vois ?** Il ne lui est pas affecté, ni à l’un de ses rôles.

**Comment donner les mêmes accès à dix nouveaux commerciaux ?** Créez un rôle, affectez-lui les éléments voulus, puis donnez ce rôle aux utilisateurs.

**Comment dupliquer les accès d’un utilisateur ?** « Dupliquer le profil pour d'autres utilisateurs » sur son profil.

**Pourquoi un nouveau menu n’apparaît-il pas chez moi ?** Il faut l’affecter (utilisateur ou rôle) via « Menus affectés ».
