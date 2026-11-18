# Rendez-vous : itinéraires, rappels et agendas des autres

## À quoi ça sert
Compléter la fiche Rendez-vous : voir l’itinéraire d’une journée de rendez-vous, être prévenu d’un rendez-vous qui approche, et consulter l’agenda d’un collègue.

## Comment faire
- **Itinéraire :** dans l’agenda (barre du haut > « RDV »), en vue jour ou mois, le panneau « Directions » affiche sur une carte le parcours des rendez-vous d’un utilisateur pour la journée choisie.
- **Rappels :** icône engrenage > « Paramètres » > cochez « Notifier les RDVs proches ».
- **Agenda d’un collègue :** par défaut on ne voit que ses propres rendez-vous; voir ceux des autres demande le droit `all.project.user.agenda`.

## Règles
- **Itinéraire :** les temps de trajet entre rendez-vous sont calculés avec Google Maps et mis en cache; l’agenda affiche les résumés déjà calculés sans relancer de calcul. Si le calcul est en cours, « Le calcul de l’itinéraire est toujours en cours. Réessayez dans quelques instants. » s’affiche.
- **Droit sur les agendas :** `all.project.user.agenda` donne accès aux agendas de tous les utilisateurs du projet; on peut aussi n’autoriser qu’un utilisateur précis avec `all.project.user.agenda.{identifiant de l'utilisateur}`. Seuls les utilisateurs du projet comptent.
- **Rappel :** quand la case est cochée, les rendez-vous de l’utilisateur déclenchent une alerte sonore et visuelle à l’approche de l’heure : elle commence environ une heure avant, se répète toutes les 5 minutes et clignote 20 secondes.
- **Rappel automatique au prospect :** une automatisation peut envoyer un SMS ou un e-mail au prospect avant son rendez-vous : déclencheur « Un RDV approche » (avec le nombre de minutes), ou le modèle « Rappel de rendez-vous » (SMS la veille). Voir la fiche Automatisations : déclencheurs, conditions, actions.

## Limites
Les alertes de rappel ne s’affichent que si le CRM est ouvert dans le navigateur. L’itinéraire dépend du service Google Maps.

## Dans l'interface
Chemin : « RDV » (`components/event/`).

1. **Voir l’itinéraire :** ouvrez l’agenda en vue jour ou mois, puis l’action « Directions » du menu de la vue. On peut « Chercher un prospect ... » ou « Chercher une adresse ... » pour ajouter un point, « Enlever l'itinéraire de la carte » ou « Supprimer le marqueur » pour nettoyer; un clic sur un marqueur propose « Envoyer un SMS », « Appeler », « Fichier », « Message » et « RDV » pour ce prospect.
2. **Activer les rappels :** « Paramètres » > « Notifier les RDVs proches » (case à cocher, à l’état « activé » ou « désactivé »).
3. **Afficher l’agenda d’un collègue :** dans l’agenda, la liste des utilisateurs ne propose que ceux dont vous avez le droit de voir l’agenda.

Droits requis : `all.project.user.agenda` (agenda des autres), `all.prospect.event.view` (voir les rendez-vous d’un prospect). Après l’activation du rappel, les alertes apparaissent à l’approche des rendez-vous.

Sources d’interface : `components/event/Reminder.vue`, `components/event/slides/daily-direction/`, `components/event/agenda/`, `components/layouts/setting/Layout.vue`, `lang/fr.json` (`event.user_daily_directions.*`, `setting.events_reminder.*`).

## Questions fréquentes
**Comment être prévenu d’un rendez-vous ?** « Paramètres » > « Notifier les RDVs proches ».

**Comment voir le trajet de ma journée ?** Agenda en vue jour > « Directions ».

**Comment voir l’agenda d’un collègue ?** Il faut le droit `all.project.user.agenda`, ou le droit sur cet utilisateur précis.

**Comment rappeler automatiquement un rendez-vous au client ?** Automatisation avec le déclencheur « Un RDV approche », ou le modèle « Rappel de rendez-vous ».
