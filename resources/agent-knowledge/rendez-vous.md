# Rendez-vous

## À quoi ça sert
Le CRM gère les rendez-vous et tâches sous forme d’événements, associés éventuellement à un prospect et à un calendrier.

## Comment faire
Consulte les calendriers et les utilisateurs visibles avant de créer ou modifier un événement. L’API projet expose la liste, la création, la consultation, la mise à jour et la suppression des événements.

## Règles
Les rendez-vous et tâches sont soumis aux droits du projet et aux règles de visibilité des calendriers et des prospects. Les outils d’agent attendent des dates au format `AAAA-MM-JJ HH:MM`.

## Limites
Pour créer un événement, le calendrier et l’utilisateur doivent appartenir au projet; le prospect, le devis (qui doit appartenir à ce prospect) et le véhicule sont optionnels mais doivent aussi appartenir au projet. Le CRM ne détecte pas les conflits d’agenda : deux rendez-vous peuvent se chevaucher pour le même utilisateur. Les champs d’un événement sont : titre, description, date et heure de début (« De... ») et de fin (« A... »), lieu, adresse de destination, « RDV confirmé », « RDV réalisé », filtres, et l’option « Envoyer une invitation au prospect ».

## Dans l'interface
Chemin : barre du haut > « RDV » ouvre la route `event` et le composant `event/Agenda.vue`. La fiche prospect propose aussi l’icône « RDV » qui ouvre ses événements.

1. Dans l’agenda, consultez la vue en cours (jour, semaine, mois ou planning) et les calendriers/utilisateurs visibles.
2. Pour créer ou modifier un événement, utilisez le contrôle proposé dans l’agenda ou la fiche. Le formulaire comprend « Calendrier », « Utilisateur » et « Prospect »; le libellé de création est « Nouveau RDV » (« Voir et modifier le RDV » pour un événement existant, « Déplacer le RDV » pour le changer de créneau). Le formulaire propose aussi « Véhicule », « Devis » et « Templates de description ».
3. Le panneau d’événement offre « Modifier » lorsque l’événement peut être modifié. La suppression passe par une confirmation « Voulez-vous vraiment effectuer cette suppression ? ».

Droits requis : visibilité des calendriers et prospects; la liste des utilisateurs d’agenda est filtrée par permission de calendrier. Côté serveur, créer un événement ne demande pas de droit propre, seulement des éléments appartenant au projet. Voir, modifier ou supprimer un événement est permis à l’administrateur du projet, à l’utilisateur concerné, au créateur, à un utilisateur invité, à un utilisateur affecté au prospect de l’événement, ou à un utilisateur autorisé sur l’agenda de la personne concernée (`projectUserAgenda`). Après validation, l’événement est affiché dans l’agenda et/ou la fiche du prospect.

Sources d’interface : `components/layouts/Header.vue`, `components/event/Agenda.vue`, `components/event/agenda/`, `components/event/slides/`, `components/prospect/profile/Profile.vue`, `router/index.js`, `lang/fr.json` (`header.appointments`, `event.*`).

## Questions fréquentes
**Où ouvrir l’agenda ?** Cliquez « RDV » dans la barre du haut.

**Comment rattacher un rendez-vous à un prospect ?** Dans le formulaire, utilisez « Prospect ».

**Pourquoi un calendrier ou utilisateur manque-t-il ?** La visibilité dépend des droits et des affectations; un utilisateur ne voit que les agendas des personnes dont il a le droit de voir l’agenda, et seuls les utilisateurs du projet peuvent recevoir un événement.

## Pour aller plus loin
Itinéraires de la journée, rappels de rendez-vous et agendas des autres : sujet `rendez-vous-itineraires-rappels`.
