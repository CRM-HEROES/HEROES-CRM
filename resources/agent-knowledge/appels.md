# Appels

## À quoi ça sert
Le CRM peut lancer des appels sortants via une ligne CloudTalk ou Kavkom configurée. Les appels et leurs résultats peuvent apparaître dans l’historique des prospects.

## Comment faire
Pour CloudTalk, l’API projet reçoit un numéro et résout une ligne associée à l’utilisateur connecté; un identifiant de ligne peut être fourni. Les workflows proposent aussi une action d’appel quand une ligne compatible est configurée. Pour un appel d’agent, vérifie d’abord l’historique et la présence du téléphone.

## Règles
Les workflows `make_call` appellent d’abord l’agent de la ligne, puis le prospect. Les appels démarrés par l’outil d’agent `call_start` sont soumis à validation humaine, même en mode autonome. L’aperçu d’approbation masque le numéro et présente le briefing. L’action de workflow `agent_call` lance un appel par un agent IA : l’activation du workflow par un humain vaut autorisation.

Avant tout appel d’agent, le CRM vérifie : coupe-circuit global, appels activés sur le projet, numéro valide, `do_not_call`, heures de travail du prospect (jours ouvrés et jours fériés exclus), maximum de 3 tentatives, un seul appel par jour et un délai de 48 heures entre deux appels, et une limite quotidienne par projet. Le consentement du prospect n’est pas contrôlé. Hors des heures de travail du prospect, un appel lancé par l’action de workflow `agent_call` est programmé à la prochaine ouverture (le résultat indique l’heure) : le script est préparé tout de suite, puis tous les contrôles sont refaits au moment de l’appel; cela demande une file d’attente réelle (`queue:work --queue=agents`), car avec la file « sync » l’appel est refusé. Le même appel n’est programmé qu’une fois. Un appel proposé dans le chat avec l’outil `call_start` n’est pas programmé : la tentative est marquée « skipped » avec la prochaine ouverture. Le résultat d’un appel CloudTalk arrive par webhook : s’il est signé (secret de webhook configuré), il est pris tel quel; sinon le CRM relit l’appel dans l’API CloudTalk avant de le clôturer, et n’utilise que cette réponse. Un appel resté « en cours » sans résultat est clôturé en échec après 60 minutes.

## Limites
`call_start` ne prend en charge que CloudTalk. Kavkom est disponible dans les API et workflows existants, mais pas par cet outil. Pour qu’un agent appelle par CloudTalk, le propriétaire de l’agent doit avoir une ligne CloudTalk dans le projet et un agent CloudTalk choisi sur cette ligne; sans l’un des deux, l’appel échoue avec un message explicite. Le numéro du prospect est converti au format international (un numéro qui commence par 0 sans pays reconnu est refusé). Dans la fiche prospect, les moyens d’appel proposés sont « Appeler via Téléphone », « Appeler via Aircall », « Appeler via Ringover » et « Appeler via CloudTalk », selon les lignes configurées; CloudTalk propose aussi le choix « Appels successifs » ou « Parallel Dialer » pour plusieurs prospects.

## Dans l'interface
Chemin : « Prospects » > fiche du prospect > icône « Appel » (`prospect.profile.Profile.vue`) ouvre le panneau d’interactions.

1. Vérifiez le numéro de téléphone du prospect et ouvrez « Appel ».
2. Choisissez un moyen d’appel proposé dans le panneau; les options visibles dépendent des lignes configurées (CloudTalk, Kavkom ou intégrations compatibles).
3. Après l’appel, utilisez l’action d’enregistrement de l’interaction si elle est disponible. Le bouton s’intitule « Ajouter un historique d’appel » (droit `all.prospect.interaction.add`).

Droits requis : l’affichage du panneau d’interactions est soumis à `all.prospect.interaction.view`; l’ajout d’un historique requiert `all.prospect.interaction.add`. Une ligne/configuration d’appel compatible doit être disponible. Après l’action, les interactions et résultats consignés apparaissent dans le panneau d’historique.

Sources d’interface : `components/prospect/profile/Profile.vue`, `components/prospect/interaction/Slide.vue`, `components/prospect/interaction/`, `components/layouts/Main.vue`, `lang/fr.json` (`prospect.profile.menus.call`, `prospect.interaction.*`).

## Questions fréquentes
**Où appeler un prospect ?** Dans sa fiche, cliquez l’icône « Appel ».

**Pourquoi aucun moyen d’appel n’est proposé ?** Une ligne compatible et les droits d’interaction sont nécessaires.

**L’appel d’un agent est-il lancé sans confirmation ?** Non, `call_start` attend une validation humaine.

## Pour aller plus loin
Configurer une ligne CloudTalk, Kavkom, Ringover ou Twilio : sujet `lignes-telephoniques`. Webhook CloudTalk : sujet `services-sms-email-telephonie`.
