# Stack téléphonie isolée

Cette stack est indépendante de `docker-compose.yml` : elle ne modifie ni Nginx,
ni le CRM, ni le réseau `heroes-network`. FreeSWITCH et l'agent communiquent sur
le réseau Docker privé `heroes-telephony-network` aux noms DNS
`telephony-freeswitch` et `ai-phone-agent`.

Les seules publications hôte sont : SIP TCP/UDP `5060`, RTP UDP `26384-26483`
et l'API locale de l'agent `127.0.0.1:14000`. Elles sont configurables par les
variables `FREESWITCH_SIP_PORT`, `FREESWITCH_RTP_START_PORT`,
`FREESWITCH_RTP_END_PORT` et `AI_PHONE_AGENT_HTTP_PORT`. Les ports RTP doivent
être identiques à l'intérieur et à l'extérieur du conteneur.

Prérequis de production : renseigner dans le `.env` racine les variables Kavkom,
`FS_ESL_PASSWORD`, `FREESWITCH_EXTERNAL_IP` et
`SIGNALWIRE_PERSONAL_ACCESS_TOKEN`; renseigner les secrets et l'URL Laravel
dans `ai-phone-agent/.env`. Le PAT est généré dans **SignalWire Dashboard >
Personal Access Tokens** : il est distinct de `SIGNALWIRE_PROJECT_ID`,
`SIGNALWIRE_API_TOKEN` et `SIGNALWIRE_SPACE`, qui authentifient uniquement les
appels REST SignalWire.

Le compose lance aussi `orchestrator` (API locale `:14010`) et `dashboard`
(`:14100`). Le tableau de bord utilise le WebSocket local `:14002`; ne le
publiez pas sur Internet sans ajouter une authentification. Pour les essais,
activez `TEST_MODE=true` et renseignez `TEST_ALLOWED_NUMBERS` dans
`ai-phone-agent/.env` : tout numéro hors liste blanche sera refusé.

Après vérification que les ports sont disponibles, lancer uniquement cette stack :

```sh
docker compose -f docker-compose.telephony.yml up -d --build
docker compose -f docker-compose.telephony.yml ps
docker compose -f docker-compose.telephony.yml logs --tail=100 telephony-freeswitch ai-phone-agent
```

Ne pas démarrer le service `freeswitch` présent dans le compose CRM en même temps
que cette stack : il utilise le réseau hôte et vise les mêmes fonctions SIP/RTP.
