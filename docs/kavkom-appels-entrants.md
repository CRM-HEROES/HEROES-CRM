# Appels entrants Kavkom

Le CRM reçoit désormais les appels qui arrivent sur l'extension Kavkom de l'agent : sonnerie et popup d'appel entrant où qu'il soit dans le CRM, identification du prospect par son numéro, et écriture de l'appel dans l'historique de la fiche (statut `ringing` → `answered` → `hangup`, ou `missed`).

## Côté Kavkom

L'extension SIP de l'agent est enregistrée par le navigateur (softphone WebRTC). Pour qu'un appel entrant sonne dans le CRM, le DID/numéro entrant doit être routé par Kavkom vers cette extension (`extension` configurée sur la ligne Kavkom de l'agent), comme pour un poste physique.

Champs utilisés de la ligne Kavkom (Paramètres du projet → Lignes) :

| Champ | Usage pour l'entrant |
| --- | --- |
| `extension` | poste qui sonne dans le CRM |
| `domain_uuid`, `api_token` | identité SIP et résolution de l'extension |
| `phone_number` | DID appelé, enregistré comme `from_number` de l'interaction |

## Mise en service

1. `php artisan migrate` (ajoute `kavkom_calls.direction`).
2. Laisser un onglet du CRM ouvert : l'enregistrement SIP vit dans l'onglet (comme tout softphone WebRTC), et donc la sonnerie aussi.
3. Router côté Kavkom le numéro entrant vers l'extension de l'agent.

## Fonctionnement

- `resources/js/utils/kavkom-phone.js` : **une seule** connexion SIP pour tout le CRM (état partagé + événements sur l'EventBus). Une extension ne doit jamais être enregistrée deux fois depuis le même navigateur, sinon le PBX forke l'appel entrant vers deux dialogues.
- `resources/js/components/utils/KavkomIncomingCall.vue` (monté dans le layout principal) : démarre l'enregistrement, affiche la popup d'appel entrant (numéro, prospect reconnu, lien vers la fiche, Accepter/Refuser), la sonnerie, la barre d'appel en cours et la journalisation.
- `resources/js/components/utils/Kavkom.vue` : panneau du softphone dans l'onglet Kavkom de la fiche prospect. Il ne se connecte plus, il affiche l'état partagé.
- Distinction sortant/entrant : un clic-à-appeler (`KavkomController::call`) appelle `expectAgentLeg()` avant sa requête REST. Le prochain INVITE est alors le *leg agent* du clic-à-appeler et est auto-répondu (`autoAnswer`) ; tout autre INVITE est un vrai appel entrant, laissé à l'agent. La fenêtre d'attente est de 60 s.
- Historique : `POST /api/settings/kavkom/incoming` (`KavkomController::incoming`) cherche le prospect par numéro (formes locale `0688…` et internationale `+33688…` équivalentes), puis ouvre ou poursuit une interaction `source = kavkom`, `from_user = false`, `data.direction = inbound`. Le navigateur renvoie l'`interaction_id` reçu pour que les événements suivants mettent à jour la même interaction.
- Un appelant inconnu peut être décroché mais n'est pas historisé (aucune fiche prospect à alimenter).
- CDR entrant : `KavkomWebhookController::cdr` renseigne `direction` et place **l'autre partie** dans `destination` (l'appelant pour un entrant, le numéro composé pour un sortant). Le prospect est cherché sur ce numéro, et l'interaction ouverte par le softphone est rattachée au CDR afin que `ProcessKavkomCall` la complète (transcription, analyse, qualification) au lieu d'en créer une seconde.

## Limites connues

- Si l'agent ferme tous ses onglets du CRM, les appels entrants ne sonnent plus (comportement normal d'un softphone navigateur).
- La sonnerie dépend de l'autorisation audio du navigateur pour le domaine du CRM.
- Chaque onglet du CRM enregistre la même extension : avec deux onglets ouverts, l'appel sonne dans les deux et peut être historisé deux fois. Un seul onglet par agent est recommandé.
- La détection sortant/entrant repose sur `expectAgentLeg()` (fenêtre de 60 s couvrant la requête REST). Si Kavkom refuse la demande d'appel, le lég est annulé (`forgetAgentLeg()`) pour qu'un appel entrant arrivé juste après ne soit pas auto-répondu.
