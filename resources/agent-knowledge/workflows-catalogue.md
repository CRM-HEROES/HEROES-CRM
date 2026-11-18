# Automatisations : déclencheurs, conditions, actions et exécutions

## À quoi ça sert
Une automatisation fait des actions à votre place (SMS, e-mail, appel, affecter, classer…) quand un événement se produit dans le CRM. Cette fiche détaille tout ce qu’on peut y mettre et comment on la suit. La fiche Automatisation donne le parcours de base (créer, publier, activer).

## Comment faire
Barre du haut > « Automatisations ». « Nom de l'automatisation » puis « Créer » pour une automatisation vierge, ou « Modèles prêts à l'emploi » pour partir d’un modèle. Le schéma se construit en reliant des étapes : on clique ou on glisse une étape dans le schéma, puis on relie les étapes avec les ronds.

## Règles
- **Étapes :** « Quand » (le déclencheur, toujours la première étape), « Si » (une condition), « Faire » (une action), « Attendre », « Attendre un événement » et « Terminer ». Une étape ne peut pas être reliée à elle-même, aucune étape ne mène au déclencheur, une étape « Terminer » n’a pas de suite, et un lien ne peut pas créer de boucle.
- **Limites :** 100 étapes au plus, 5 niveaux de conditions imbriquées, attente de 365 jours au plus.
- **Déclencheurs :** « Un nouveau prospect est créé » (à la main, par import ou par synchronisation), « Un prospect est modifié » (informations, utilisateurs, filtres…), « Un prospect entre dans un filtre », « Un prospect sort d'un filtre » (le filtre se définit juste dessous; options pour arrêter si le prospect sort pendant l’attente et pour ne démarrer qu’une fois par prospect), « Un devis est créé », « Un devis est modifié », « Un RDV est créé », « Un RDV est modifié », « Un RDV approche » (avec « minutes avant ») et « Un RDV se termine ».
- **Conditions** (« Le prospect doit remplir : ») : on combine avec « Toutes ces conditions » ou « Au moins une de ces conditions », et on peut imbriquer des groupes (A et (B ou C)). Les critères sont : « Le prospect a écrit ou appelé », « Le prospect est archivé », « Créé depuis moins de (jours) », « Modifié depuis moins de (jours) », « A pour utilisateur », « N'a pas pour utilisateur », « Est dans le groupe », « N'est pas dans le groupe », « A le filtre » et « N'a pas le filtre ». Deux critères d’horaire existent : « Dans les heures de travail » (jours, de, à) et « Pendant une période » (dates). Les heures sont celles du fuseau horaire du projet.
- **Attendre :** « Pendant une durée » (minutes, heures, jours), « Jusqu'à une heure » (par exemple 8h le lendemain) ou « Jusqu'à une date »; l’option « Seulement ces jours » restreint aux jours choisis.
- **Attendre un événement :** l’automatisation attend que le prospect fasse quelque chose; si cela arrive, elle suit la sortie « Oui », sinon la sortie « Non » à la fin du délai (« Attendre au plus »). Événements : « Le prospect répond par SMS », « Le prospect répond par message ou email », « Le prospect appelle », « Un RDV est pris pour le prospect », « Un devis est créé pour le prospect ». Seul ce qui arrive après cette étape compte.
- **Actions :** *Communiquer* : « Envoyer un email », « Envoyer un SMS », « Effectuer un appel », « Appel par un agent IA », « SMS rédigé par un agent IA ». *Affecter* : « Affecter à un utilisateur », « Désaffecter un utilisateur », « Affecter à un groupe », « Retirer d'un groupe ». *Filtres* : « Ajouter un filtre », « Retirer un filtre ». *Statut du prospect* : « Archiver le prospect », « Désarchiver le prospect », « Supprimer le prospect » (définitif). Pour les devis et les rendez-vous, on retrouve aussi des actions reprises des campagnes (créer un RDV ou un devis, passer le devis à une étape, générer ou envoyer un document, géolocaliser…).
- **En cas d’échec d’une action :** choix entre « Arrêter l'automatisation » et « Continuer quand même » (par exemple si le prospect n’a pas d’e-mail ou de mobile).
- **Variables** dans les messages : `{{prospect.firstname}}`, `{{prospect.lastname}}`, `{{prospect.fullname}}`, `{{prospect.email}}`, `{{prospect.phone}}`, `{{prospect.mobile}}`, `{{prospect.company}}`, `{{prospect.city}}`, `{{prospect.postal_code}}`, `{{prospect.country}}`, `{{user.firstname}}`, `{{user.lastname}}`, `{{user.fullname}}`, `{{user.email}}` et `{{project.name}}`. Un clic sur une variable l’insère.
- **Modèles prêts à l'emploi :** « Email de bienvenue » (un e-mail à chaque nouveau prospect), « Relance SMS sans réponse » (SMS, attente de 2 jours, relance si pas de réponse), « Rappel de rendez-vous » (SMS la veille du rendez-vous) et « Contact en heures ouvrées » (SMS tout de suite en journée, ou le lendemain à 8h en dehors des horaires).

## Limites
Un SMS ou un appel d’agent IA dans une automatisation part réellement dès que l’automatisation est activée par un humain : vérifiez le texte, les conditions et les horaires avant d’activer. Les actions « Supprimer le prospect » et « Archiver le prospect » agissent sur les vrais prospects. Les actions reprises d’une campagne gardent leurs réglages d’origine (« Issue d'une campagne »).

## Dans l'interface
Chemin : « Automatisations » (`components/workflow/`).

1. **Construire :** « Quand » choisit le déclencheur (« Modifier le déclencheur » pour le changer); le panneau propose les sections « Déclencheur », « Actions » et « Règles ». « + » sous une étape en ajoute une suivante. Un nom court par étape (par exemple « SMS de relance ») s’affiche sur le schéma. « Réorganiser » range les étapes de haut en bas.
2. **Vérifier :** « Vérifier » liste « À corriger avant de publier » ou affiche « Aucun problème détecté. ». Le brouillon s’enregistre tout seul (« Brouillon enregistré »).
3. **Publier et lancer :** « Publier » enregistre une version sans démarrer; « Publier et activer » ou « Activer » démarre; « Publier la nouvelle version » remplace la version (les exécutions en cours continuent avec l’ancienne). « Mettre en pause » arrête les nouveaux démarrages (les exécutions en cours se terminent). « Archiver » ou « Supprimer » annulent les exécutions en cours.
4. **États d’une automatisation :** « Brouillon » (ne tourne pas encore), « Active » (démarre à chaque déclencheur), « En pause » et « Archivée » (ne tourne plus, plus modifiable).
5. **Suivi** (onglet « Suivi », droit `all.project.workflow.execution.view`) : « Vue d'ensemble » sur les derniers jours (en cours ou en attente, terminées, en échec, étapes exécutées), « Erreurs les plus fréquentes », « Opérateurs utilisés », et la liste des « Exécutions » filtrable par automatisation ou état. Un état d’exécution est « À démarrer », « En cours », « En attente », « Terminée », « En échec », « Annulée » ou « Ignorée ». « Annuler cette exécution » arrête une exécution (les étapes déjà faites ne sont pas défaites). Chaque exécution détaille ses étapes (« Réussie », « En échec », « Ignorée »), et pour un e-mail s’il est envoyé ou non.
6. **Sur la fiche d’un prospect :** l’icône « Automatisations » liste celles qui ont traité ce prospect (« Automatisations de {prospect} »).

Droits requis : `all.project.workflow.view` (voir), `.add` (créer), `.update` (modifier et publier), `.execute` (activer, mettre en pause, archiver), `.delete` (supprimer), `all.project.workflow.execution.view` (suivi). Après chaque action, le schéma et le suivi se mettent à jour.

Sources d’interface : `components/workflow/`, `lang/fr.json` (`workflow.*`), `app/Workflows/WorkflowCatalog.php`.

## Questions fréquentes
**Comment envoyer un SMS 2 jours après l’arrivée d’un prospect ?** Déclencheur « Un nouveau prospect est créé » > « Attendre » 2 jours > « Envoyer un SMS ». Le modèle « Relance SMS sans réponse » fait un cas proche.

**Comment n’envoyer qu’aux heures de travail ?** Ajoutez avant l’envoi une condition « Dans les heures de travail » (« Un horaire »).

**Comment faire une relance si le prospect ne répond pas ?** « Attendre un événement » « Le prospect répond par SMS » : la sortie « Non » mène à la relance.

**Pourquoi mon automatisation ne démarre-t-elle pas ?** Elle n’est pas « Active » (brouillon ou en pause), ou ses conditions ne sont pas remplies, ou aucun worker ne traite la file des automatisations sur le serveur.

**Comment voir pourquoi un envoi a échoué ?** Onglet « Suivi » > l’exécution > la liste de ses étapes et l’erreur.
