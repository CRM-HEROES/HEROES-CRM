# Droits et permissions

## À quoi ça sert
Chaque action du CRM est protégée par un droit nommé, par exemple `all.prospect.sms.add` (envoyer un SMS). Cette fiche est la liste complète de ces droits avec leur libellé exact tel qu’il apparaît dans l’écran des permissions. Utilise-la pour dire à un utilisateur quel droit lui manque.

## Comment faire
Les droits s’accordent à un rôle ou directement à un utilisateur, dans un projet : « Accueil » > le projet > bloc « Rôles » (cliquer un rôle) ou « Paramètres » > « Liste des utilisateurs » > l’utilisateur > permissions. Il faut pour cela le droit `all.project.role.update` (rôles); pour les droits directs d’un utilisateur, il faut `all` (administrateur du projet).

## Règles
- Les droits sont hiérarchiques : accorder `all` donne tous les droits; accorder `all.prospect` donne tous les droits sur les prospects; accorder `all.prospect.sms` donne ceux des SMS, et ainsi de suite.
- Les droits sont propres à un projet : un utilisateur peut avoir des droits différents dans chaque projet.
- Le super-administrateur a tous les droits partout.
- Un agent IA n’a jamais plus de droits que son propriétaire.

## Limites
Dans l’écran des permissions, les lignes qui se terminent par « affectés » ou « affectées » (par exemple `all.project.calendar.affected`) ne sont pas des droits : elles n’ont pas de case et ouvrent le panneau où l’on choisit quels éléments (calendriers, filtres, documents…) sont affectés à l’utilisateur ou au rôle. Ce qu’un utilisateur voit ne dépend pas d’un droit mais de ces affectations (voir la fiche Affectations des utilisateurs et des rôles).

Cette liste vient de l’écran des permissions du CRM. L’interface cache une action quand le droit manque; côté serveur, certaines actions ne sont contrôlées qu’au niveau du projet ou du prospect visible (voir les fiches de chaque fonctionnalité). L’entrée « Claude » de la barre du haut demande `all.project.agent.view`. Voir ou modifier les permissions d’un rôle demande `all.project.role.update` et ne concerne que les rôles du projet ; on ne peut donner à un rôle qu’une permission que l’on possède soi-même (le créateur du projet et les super-administrateurs peuvent tout donner).

Droits contrôlés par le CRM mais sans case dédiée dans l’écran des permissions : `all.project.line.add`, `all.project.line.update`, `all.project.line.delete` (lignes téléphoniques), `all.project.pipeline.add`, `all.project.pipeline.update`, `all.project.pipeline.delete` (pipelines), `all.project.document.view`, `all.project.campaign.view`, `all.prospect.archive`, `all.prospect.ocr`, `all.order.add`, `all.order.delete`, `all.project.event-description-template.add`, `all.project.event-description-template.update`, `all.project.event-description-template.delete`, `all.project.action.add`, `all.project.action.update`, `all.project.status.add`, `all.project.status.update`, `all.project.step.add`, `all.project.step.update`, `all.project.product.add`, `all.project.product.update` (boutons des filtres de devis), `all.project.user.update` (modifier un utilisateur) et `all.user.commission.add` (bouton « Gérer les commissions »). On ne peut pas les accorder un par un : ils s’obtiennent avec le groupe parent (`all.project`, `all.prospect` ou `all`). En pratique, ces actions sont réservées aux administrateurs du projet.

Incohérence connue : l’écran des permissions propose `all.project.sms-template.add`, `.update` et `.delete` pour les modèles de SMS, mais le serveur contrôle `all.project.sms.template.add`, `.update` et `.delete`. Un utilisateur qui n’a que le premier verra les boutons et l’action sera refusée; un administrateur (`all.project`) n’est pas concerné.

Autre incohérence : le bouton « Gérer les commissions » demande `all.user.commission.add` alors que le serveur et l’écran des permissions utilisent `all.project.user.commission.add`. Un utilisateur qui n’a que ce dernier ne voit pas le bouton.

## Dans l'interface
Chemin : « Accueil » > le projet > bloc « Rôles », ou icône engrenage > « Paramètres » > « Liste des utilisateurs ». Le panneau des permissions affiche cette arborescence avec des cases à cocher; cocher un groupe coche tout ce qu’il contient.

Sources d’interface : `utils/permissions.js`, `components/role/`, `components/user/permission/`.

## Questions fréquentes
**Quel droit faut-il pour envoyer un SMS ?** `all.prospect.sms.add`.

**Quel droit faut-il pour voir la page de l’assistant Claude ?** `all.project.agent.view`.

**Comment donner tous les droits sur les prospects ?** Cochez `all.prospect` pour le rôle ou l’utilisateur.

# Liste des droits

Chaque groupe ci-dessous correspond à une partie du CRM; chaque ligne est `droit` : libellé.

## Projet

- **Type de RDV** (`all.project.calendar`) :
  - `all.project.calendar.affected` : Types de RDV affectés (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.calendar.add` : Ajouter un type de RDV
  - `all.project.calendar.update` : Modifier un type de RDV
  - `all.project.calendar.delete` : Supprimer un type de RDV
- **Campagne** (`all.project.campaign`) :
  - `all.project.campaign.add` : Ajouter une campagne
  - `all.project.campaign.update` : Modifier une campagne
  - `all.project.campaign.delete` : Supprimer une campagne
- **Automatisation** (`all.project.workflow`) :
  - `all.project.workflow.view` : Voir les automatisations
  - `all.project.workflow.add` : Ajouter une automatisation
  - `all.project.workflow.update` : Modifier et publier une automatisation
  - `all.project.workflow.delete` : Supprimer une automatisation
  - `all.project.workflow.execute` : Activer, mettre en pause, archiver une automatisation
- **Exécutions des automatisations** (`all.project.workflow.execution`) :
  - `all.project.workflow.execution.view` : Voir le suivi et l'historique des exécutions
- **Assistant Claude** (`all.project.agent`) :
  - `all.project.agent.view` : Voir l'assistant Claude
- **Catégorie de filtre** (`all.project.category`) :
  - `all.project.category.affected` : Catégories de filtre affectées (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.category.add` : Ajouter une catégorie de filtre
  - `all.project.category.update` : Modifier une catégorie de filtre
  - `all.project.category.delete` : Supprimer une catégorie de filtre
- **Gérer les filtres** (`all.project.category.label`) :
  - `all.project.category.label.add` : Ajouter un filtre
  - `all.project.category.label.update` : Modifier un filtre
  - `all.project.category.label.delete` : Supprimer un filtre
- **Document** (`all.project.document`) :
  - `all.project.document.affected` : Documents affectés (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.document.add` : Ajouter un document
  - `all.project.document.update` : Modifier un document
  - `all.project.document.delete` : Supprimer un document
- **Champ de prospect** (`all.project.field`) :
  - `all.project.field.add` : Ajouter un champ de prospect
  - `all.project.field.update` : Modifier un champ de prospect
  - `all.project.field.delete` : Supprimer un champ de prospect
- **Dossier** (`all.project.folder`) :
  - `all.project.folder.affected` : Dossiers affectés (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.folder.add` : Ajouter un dossier
  - `all.project.folder.update` : Modifier un dossier
  - `all.project.folder.delete` : Supprimer un dossier
- **Groupe** (`all.project.group`) :
  - `all.project.group.affected` : Groupes affectés (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.group.add` : Ajouter un groupe
  - `all.project.group.update` : Modifier un groupe
  - `all.project.group.delete` : Supprimer un groupe
- **Menu** (`all.project.menu`) :
  - `all.project.menu.add` : Ajouter un menu
  - `all.project.menu.update` : Modifier un menu
  - `all.project.menu.delete` : Supprimer un menu
- **Modèle de message** (`all.project.message-template`) :
  - `all.project.message-template.add` : Ajouter un modèle de message
  - `all.project.message-template.update` : Modifier un modèle de message
  - `all.project.message-template.delete` : Supprimer un modèle de message
- **Questionnaire** (`all.project.questionnaire`) :
  - `all.project.questionnaire.affected` : Questionnaires affectés (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.questionnaire.add` : Ajouter un questionnaire
  - `all.project.questionnaire.update` : Modifier un questionnaire
  - `all.project.questionnaire.delete` : Supprimer un questionnaire
- **Rôle** (`all.project.role`) :
  - `all.project.role.affected` : Rôles affectés (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.role.add` : Ajouter un rôle
  - `all.project.role.update` : Modifier un rôle
  - `all.project.role.delete` : Supprimer un rôle
- **Modèle de SMS** (`all.project.sms-template`) :
  - `all.project.sms-template.add` : Ajouter un modèle de SMS
  - `all.project.sms-template.update` : Modifier un modèle de SMS
  - `all.project.sms-template.delete` : Supprimer un modèle de SMS
- **Canal de discussion** (`all.project.thread`) :
  - `all.project.thread.affected` : Canaux de discussion affectés (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.thread.add` : Ajouter un canal de discussion
  - `all.project.thread.update` : Modifier un canal de discussion
  - `all.project.thread.delete` : Supprimer un canal de discussion
- **Commande** (`all.project.order`) :
- **Action sur les commandes** (`all.project.order.action`) :
  - `all.project.order.action.affected` : Actions sur les commandes affectées (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.order.action.add` : Ajouter une action sur les commandes
  - `all.project.order.action.update` : Modifier une action sur les commandes
  - `all.project.order.action.delete` : Supprimer une action sur les commandes
- **Etape de commande** (`all.project.order.step`) :
  - `all.project.order.step.affected` : Etapes de commande affectées (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.order.step.add` : Ajouter une étape de commande
  - `all.project.order.step.update` : Modifier une étape de commande
  - `all.project.order.step.delete` : Supprimer une étape de commande
- **Etat de commande** (`all.project.order.status`) :
  - `all.project.order.status.affected` : Etats de commande affectés (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.order.status.add` : Ajouter un état de commande
  - `all.project.order.status.update` : Modifier un état de commande
  - `all.project.order.status.delete` : Supprimer un état de commande
- **Produit** (`all.project.order.product`) :
  - `all.project.order.product.add` : Ajouter un produit
  - `all.project.order.product.update` : Modifier les produits
  - `all.project.order.product.delete` : Supprimer des produits
- **Utilisateur** (`all.project.user`) :
  - `all.project.user.affected` : Utilisateurs affectés (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.project.user.add` : Ajouter un utilisateur dans le projet
  - `all.project.user.delete` : Enlever un utilisateur du projet
  - `all.project.user.agenda` : Voir les agendas des autres utilisateurs
- **Gérer les commission d'un utilisateur** (`all.project.user.commission`) :
  - `all.project.user.commission.add` : Ajouter/modifier une commission
  - `all.project.user.commission.delete` : Supprimer la commission
- **Véhicule** (`all.project.vehicle`) :
  - `all.project.vehicle.add` : Ajouter un véhicule
  - `all.project.vehicle.update` : Modifier un véhicule
  - `all.project.vehicle.delete` : Supprimer un véhicule

## Prospect

  - `all.prospect.view` : Voir tous les prospects
  - `all.prospect.add` : Ajouter un prospect
  - `all.prospect.update` : Modifier tous les prospects
  - `all.prospect.delete` : Supprimer tous les prospects
  - `all.prospect.setting-prospects-table` : Gérer les colonnes du tableau prospects
  - `all.prospect.setting-prospect-profile` : Gérer les blocs et champs dans la fiche prospect
  - `all.prospect.import` : Importer des prospects
  - `all.prospect.export` : Exporter des prospects
- **SMS** (`all.prospect.sms`) :
  - `all.prospect.sms.view` : Voir les sms envoyés
  - `all.prospect.sms.add` : Envoyer un sms
- **Gestion des appels** (`all.prospect.interaction`) :
  - `all.prospect.interaction.view` : Voir les historiques
  - `all.prospect.interaction.add` : Appeler un prospect
  - `all.prospect.interaction.audio` : Ecouter un appel enregistré
- **Gérer les devis** (`all.prospect.order`) :
  - `all.prospect.order.view` : Voir les devis
  - `all.prospect.order.add` : Ajouter un devis
  - `all.prospect.order.update` : Modifier des devis
  - `all.prospect.order.delete` : Supprimer des devis
- **Gérer les RDVs** (`all.prospect.event`) :
  - `all.prospect.event.affected` : Types de RDV affectés (entrée qui ouvre le panneau d’affectation : ce n’est pas un droit à cocher)
  - `all.prospect.event.view` : Voir les RDVs

## Utilisateur

  - `all.user.calendar` : Gérer les types de RDV affectés
  - `all.user.category` : Gérer les catégories de filtre affectés
  - `all.user.document` : Gérer les documents affectés
  - `all.user.folder` : Gérer les dossiers affectés
  - `all.user.group` : Gérer les groupes affectés
  - `all.user.message-template` : Gérer les modèles de message affectés
  - `all.user.questionnaire` : Gérer les questionnaires affectés
  - `all.user.role` : Gérer les rôles affectés
  - `all.user.sms-template` : Gérer les modèles de SMS affectés
  - `all.user.thread` : Gérer les canaux de discussion affectés
- **Commande** (`all.user.order`) :
  - `all.user.order.action` : Gérer les actions sur les commandes affectées
  - `all.user.order.step` : Gérer les étapes de commande affectées
  - `all.user.order.status` : Gérer les états de commande affectés
  - `all.user.user` : Gérer les utilisateurs affectés
  - `all.map` : Utiliser la MAP
  - `all.stat` : Voir les statistiques
