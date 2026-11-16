# Rendez-vous

## À quoi ça sert
Le CRM gère les rendez-vous et tâches sous forme d’événements, associés éventuellement à un prospect et à un calendrier.

## Comment faire
Consulte les calendriers et les utilisateurs visibles avant de créer ou modifier un événement. L’API projet expose la liste, la création, la consultation, la mise à jour et la suppression des événements.

## Règles
Les rendez-vous et tâches sont soumis aux droits du projet et aux règles de visibilité des calendriers et des prospects. Les outils d’agent attendent des dates au format `AAAA-MM-JJ HH:MM`.

## Limites
Les champs requis et les règles de disponibilité dépendent des validations de l’API et du calendrier choisi; les conflits d’agenda ne sont pas documentés ici et sont À vérifier.

## Dans l'interface
Chemin : barre du haut > « RDV » ouvre la route `event` et le composant `event/Agenda.vue`. La fiche prospect propose aussi l’icône « RDV » qui ouvre ses événements.

1. Dans l’agenda, consultez la vue en cours (jour, semaine, mois ou planning) et les calendriers/utilisateurs visibles.
2. Pour créer ou modifier un événement, utilisez le contrôle proposé dans l’agenda ou la fiche. Le formulaire comprend « Calendrier », « Utilisateur » et « Prospect »; le libellé exact de création n’est pas constant dans les composants consultés : À vérifier.
3. Le panneau d’événement offre « Modifier » lorsque l’événement peut être modifié. La suppression passe par une confirmation « Voulez-vous vraiment effectuer cette suppression ? ».

Droits requis : visibilité des calendriers et prospects; la liste des utilisateurs d’agenda est filtrée par permission de calendrier. Les droits exacts de création et de suppression dépendent du calendrier : À vérifier. Après validation, l’événement est affiché dans l’agenda et/ou la fiche du prospect.

Sources d’interface : `components/layouts/Header.vue`, `components/event/Agenda.vue`, `components/event/agenda/`, `components/event/Slide.vue`, `components/prospect/profile/Profile.vue`, `router/index.js`, `lang/fr.json` (`header.appointments`, `event.*`).

## Questions fréquentes
**Où ouvrir l’agenda ?** Cliquez « RDV » dans la barre du haut.

**Comment rattacher un rendez-vous à un prospect ?** Dans le formulaire, utilisez « Prospect ».

**Pourquoi un calendrier ou utilisateur manque-t-il ?** La visibilité dépend des droits et des affectations; le détail est À vérifier.
