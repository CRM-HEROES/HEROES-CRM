# Services de SMS, d'e-mail et de téléphonie

## À quoi ça sert
Pour envoyer un SMS ou un e-mail, ou pour passer un appel, le projet doit être relié à un service externe. Cette fiche dit où le configurer et quelles informations préparer.

## Comment faire
Fiche du projet (« Accueil » > le projet) > bloc « API » : les entrées « Mail », « SMS via Brevo », « SMS via SMSBOX », « UltraMsg », « CloudTalk » et « SMS via MTarget » ouvrent chacune la fenêtre de réglages du service. On peut aussi les ouvrir depuis la recherche de la barre du haut en tapant « Paramètre API SMSBOX », « Paramètre envoi email », « Paramètre API Ultramsg » ou « Paramètre API MTarget ». Les lignes téléphoniques se configurent à part (voir la fiche Lignes téléphoniques).

## Règles
Une fois un service configuré, il devient un canal choisissable : dans le panneau « SMS » d’un prospect (les boutons « SMS via » suivis du nom du service), dans les envois en masse et dans les workflows. Un service non configuré est proposé avec un bouton de configuration. Les identifiants saisis ne sont jamais réaffichés ni renvoyés au navigateur.

## Limites
Les réglages se font par projet. Seuls les administrateurs du projet (`all`) modifient les réglages d’envoi; pour la téléphonie, voir les droits de la fiche Lignes téléphoniques. Les informations à fournir viennent de votre fournisseur : le CRM ne les crée pas.

## Dans l'interface
Chemin : « Accueil » > le projet > bloc « API » (`components/project/profile/blocs/api/Bloc.vue`, `components/settings/`).

1. **Mail** (fenêtre « Email via Brevo ») : « Clé API Brevo », « Nom expéditeur », « Email expéditeur ». La partie « Emails entrants (IMAP) » reçoit les réponses : choisissez un « Fournisseur » dans la liste ou « Personnalisé (serveur manuel) », puis « Serveur IMAP », « Port », « Chiffrement » (SSL, TLS, STARTTLS ou Aucun), « Identifiant (adresse email) », « Mot de passe » et, si besoin, « Dossier (optionnel) ». « Tester la connexion » vérifie les réglages et la synchronisation relève les e-mails reçus.
2. **SMS via Brevo** (fenêtre « Brevo ») : « Clé API » et « Expéditeur (Sender) ».
3. **SMS via SMSBOX** (fenêtre « SMSBOX ») : « API URI », « HRL URI » et « Clé API ».
4. **UltraMsg** (fenêtre « Ultramsg ») : « API URI », « Instance » et « Token ».
5. **SMS via MTarget** (fenêtre « MTarget ») : « API URI », « Username » et « Password ».
6. **Ringover** (fenêtre « Ringover ») : « Token ». Elle s’ouvre depuis le panneau d’appel d’un prospect.
7. **CloudTalk** (fenêtre « CloudTalk ») : trois onglets, « Configuration », « Exportation appel CloudTalk » et « Exportation SMS ». L’onglet « Configuration » donne l’adresse (webhook) à copier pour synchroniser les SMS reçus et envoyés, les appels, les messages vocaux et les enregistrements. Dans CloudTalk : « Paramètres → Webhooks », ajoutez ou modifiez le webhook, collez l’adresse (publique, en HTTPS), cochez les événements message.received, message.sent, call.ended et call.recording_ready, enregistrez, puis envoyez un SMS depuis CloudTalk vers le numéro d’un prospect pour vérifier qu’il apparaît dans le CRM.
8. Après l’enregistrement (bouton « Modifier »), le canal apparaît dans le panneau « SMS » du prospect.

Droits requis : l’administration du projet (`all`) pour ces réglages. Après la configuration, les envois par ce canal sont possibles.

Sources d’interface : `components/settings/`, `components/project/profile/blocs/api/Bloc.vue`, `components/line/`, `lang/fr.json` (`global_search.actions.api.*`).

## Questions fréquentes
**Comment configurer l’envoi d’e-mails ?** Bloc « API » du projet > « Mail » : clé Brevo, nom et e-mail de l’expéditeur.

**Comment recevoir les réponses par e-mail dans le CRM ?** Dans « Mail », la partie « Emails entrants (IMAP) » : serveur, port, identifiant et mot de passe, puis « Tester la connexion ».

**Comment brancher CloudTalk pour recevoir les SMS ?** Fenêtre « CloudTalk » > « Configuration » : copiez l’adresse du webhook et ajoutez-la dans CloudTalk avec les quatre événements indiqués.

**Pourquoi un canal SMS n’apparaît-il pas ?** Il n’est pas configuré dans le bloc « API » du projet.
