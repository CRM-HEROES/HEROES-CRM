# Audit de performance — HEROES CRM (Laravel 10 / PHP-FPM / MySQL externe)

> **Phase 1 — lecture seule.** Aucun fichier de code n'a été modifié, aucune requête n'a été exécutée, `.env` n'a pas été lu.
> Seul ce document a été créé.

## 0. Légende et limites de l'audit

| Étiquette | Sens |
|---|---|
| **Constat** | lu directement dans le code (fichier:ligne cité) |
| **Mesuré (vous)** | chiffre que vous avez fourni dans la mission |
| **Estimation** | ordre de grandeur calculé à partir du code ; à valider par la commande indiquée |
| **À vérifier** | dépend de la prod (config, données, SQL réellement généré) ; la commande de vérification est donnée |

Les « gains attendus » sont des **estimations**, jamais des mesures. Chaque correctif a sa commande avant/après.

Numéros de ligne : état du dépôt au commit `a49cca9` (branche `Tonny`).

---

## 1. Synthèse — la chaîne causale la plus probable des 504

```
:00 (heure pile)                                     requêtes web (vision_laser)
 │                                                          │
 ├─ schedule:run (dans le conteneur PHP-FPM)                │
 │   app:user-tracker  ≈ 2-5 min, 1 cœur CPU à 100 %        │
 │   app:label-prospects-count  UPDATE 13 s (verrous)      │
 │   app:user-prospects-count   UPDATE user_project         │  ← verrous de lignes
 │   app:role-users-count                                   │     sur user_project
 │   app:campaign ×3 (15 min / 30 min / hourly)             │
 │                                                          ▼
 │                                          chaque requête : ~12 requêtes de tracking
 │                                          + UPDATE users + UPDATE user_project (parfois)
 │                                          → attend les verrous / la latence réseau MySQL
 │                                                          │
 │                      GET /event (12-30 s × 10 en parallèle) occupe 10 workers sur 20
 │                      GET /menu, /default-menu : N COUNT sur 1,1 M prospects, jamais en cache
 │                      jobs lancés « après réponse » : le worker FPM reste occupé (curl sans timeout)
 │                                                          ▼
 │                                     pool FPM (20) saturé → nginx attend fastcgi_read_timeout (60 s) → 504
```

Les causes se **cumulent** ; aucune ne suffit seule. Trois leviers à très faible effort débloquent l'essentiel (§ 4, étapes 1 à 3) :

1. **`CACHE_DRIVER=array` neutralise** le cache des permissions Spatie, tous les `cache()->remember`, le rate limiter et le mutex `withoutOverlapping()` du scheduler (F01). Redis est déjà déployé (`docker-compose.yml:31-40`) et déjà utilisé par `ImportProspects.php:273`.
2. **Aucun timeout nulle part** : ni curl (≈ 25 appels), ni `request_terminate_timeout` FPM, ni PDO (F03). Un appel Google/Pipedrive lent immobilise un worker indéfiniment.
3. **Les tâches lourdes tournent dans le conteneur FPM, à la même minute, sans verrou effectif** (F04).

> **Hypothèse à valider en priorité** (F04/F08) : les `UPDATE user_project` / `UPDATE labels` en `CASE WHEN` tiennent des verrous de lignes pendant 13 s ; chaque requête HTTP qui fait `UPDATE user_project SET last_activity` (`UserLastProjectCheck.php:31`) attend alors ce verrou. Commande pendant une fenêtre :00 :
> ```sql
> SELECT * FROM sys.innodb_lock_waits\G
> SHOW FULL PROCESSLIST;
> ```

---

## 2. Table des problèmes

| ID | Sévérité | Titre | Gain estimé |
|---|---|---|---|
| F01 | **Critique** | Cache `array` : Spatie, menus, throttle, mutex scheduler inopérants | très élevé |
| F02 | **Critique** | `dispatchAfterResponse` exécute des jobs *dans* le worker PHP-FPM | élevé |
| F03 | **Critique** | Aucun timeout (curl, FPM, nginx, PDO) | protège du pire cas |
| F04 | **Critique** | Scheduler : même conteneur, séquentiel, heure pile, mutex inopérant | élevé |
| F05 | **Critique** | `/menu` et `/default-menu` : COUNT sur 1,1 M prospects à chaque appel | élevé |
| F06 | **Critique** | `/event` : requête non bornée, EXISTS imbriqués, OR non indexable | très élevé |
| F07 | **Critique** | `app:user-tracker` : N+1, calcul PHP, pas d'index sur `created_at` | très élevé |
| F08 | **Élevé** | `*-prospects-count` / `role-users-count` : GROUP BY complet + UPDATE `CASE` O(N²) | élevé |
| F09 | **Élevé** | Tracker synchrone : ≈ 12 requêtes SQL par requête HTTP | élevé |
| F10 | **Élevé** | `last_activity` à chaque requête, géoloc HTTP synchrone, `ip_city` trop court | moyen/élevé |
| F11 | **Élevé** | Chaîne de middlewares et permissions : requêtes répétées, accessor non mémoïsé | moyen/élevé |
| F12 | **Élevé** | Workers `--once` : reboot du framework en boucle + polling de 9 files sur MySQL | moyen/élevé |
| F13 | **Élevé** | `retry_after` 90 s < `--timeout` 3600 s : exécutions en double | moyen |
| F14 | **Élevé** | OPcache / caches de bootstrap : absents du Dockerfile (à vérifier) | élevé |
| F15 | Moyen | Notifications : double saut queue→canal→dispatch, `notify()` inutile | moyen |
| F16 | Moyen | Index manquants / redondants (récapitulatif justifié) | élevé (cumulé) |
| F17 | Moyen | Tables à croissance illimitée (tracker_logs, route_paths, stats, revisions…) | moyen |
| F18 | Moyen | `app:campaign` : 6 invocations, constructeur lourd, pagination fragile | moyen |
| F19 | Moyen | `app:relevance` / `app:stat` : remises à zéro totales | faible/moyen |
| F20 | Moyen | `->get()` sans limite (pagination commentée) | moyen |
| F21 | Faible | Connexion DB non persistante, sessions fichier, log `single` | faible |

---

## 3. Détail des problèmes

---

### F01 — Cache `array` : plusieurs mécanismes sont silencieusement inopérants

**Sévérité : critique — Gain : très élevé (transversal) — Effort : faible**

**Constat.** `.env.example:19` indique `CACHE_DRIVER=array` et vous confirmez le même réglage en prod. Le driver `array` vit le temps d'*un* processus PHP : rien n'est partagé entre requêtes.

Conséquences relevées dans le code :

| Mécanisme | Où | Effet avec `array` |
|---|---|---|
| Cache des permissions Spatie 5.10.2 (`config/permission.php`, `'store' => 'default'`, 24 h) | chaque requête authentifiée | table `permissions` + `role_has_permissions` rechargées **à chaque requête** (avec `teams=true`, les permissions dynamiques `all.project.user.agenda.{id}` font gonfler la table) |
| `cache()->remember(..., 300)` des comptes de menu | `MenuController.php:122` | jamais servi depuis le cache |
| `cache()->remember` des stats (`app/Utils/Stat/Value/*`, `Chart/*`) | ~20 fichiers | idem |
| Rate limiter `api` 300/min | `RouteServiceProvider.php:34-36` | compteurs perdus à chaque requête → aucune limitation réelle |
| Mutex du scheduler (`->withoutOverlapping()`) | `Kernel.php:47` | **chaque `schedule:run` est un processus séparé** : le verrou n'est jamais vu par le suivant |

**Pourquoi c'est lent.** Le chargement des permissions Spatie est une requête avec jointure exécutée sur 100 % des requêtes API. Son coût dépend du nombre de lignes dans `permissions` (À vérifier : `SELECT COUNT(*) FROM permissions; SELECT COUNT(*) FROM role_has_permissions;`).

**Correctif proposé (sans l'appliquer).**

```dotenv
# .env de prod (valeurs, pas de secret ici)
CACHE_DRIVER=redis
REDIS_HOST=redis          # nom du service docker, PAS 127.0.0.1 (cf. .env.example:27)
REDIS_CLIENT=phpredis     # l'extension est installée dans le Dockerfile
```

À prévoir en même temps : `CACHE_PREFIX` explicite, `maxmemory` + `maxmemory-policy allkeys-lru` sur le service redis, et un `php artisan permission:cache-reset` après bascule.
Option de repli sans Redis : `CACHE_DRIVER=file` sur volume local (inférieur, mais déjà suffisant pour Spatie et le mutex).

**Risque de régression.** Faible. Les données obsolètes sont possibles si une permission est modifiée sans passer par l'API Spatie (SQL direct) : appeler `permission:cache-reset`. Le TTL de 300 s sur les menus rend les compteurs « en retard » de 5 min au maximum (déjà voulu par le code).

**Test avant/après.**
```bash
# nombre de requêtes de permissions par appel API (à lancer dans tinker avec un utilisateur de test)
DB::listen(fn($q) => str_contains($q->sql,'permissions') && logger($q->sql));
# avant : ≥ 2 requêtes 'permissions' / 'role_has_permissions' par requête ; après : 0 (cache chaud)
php artisan tinker --execute="echo config('cache.default');"
redis-cli -h redis ping
```

---

### F02 — `dispatchAfterResponse` exécute les jobs *dans* le worker PHP-FPM

**Sévérité : critique — Gain : élevé — Effort : faible à moyen**

**Constat.** `Dispatcher::dispatchAfterResponse()` (`vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php:263-268`) enregistre un callback `terminating` qui appelle **`dispatchSync`**. Le job n'est donc **pas** mis en file : il s'exécute dans le processus FPM après l'envoi de la réponse. Le `->onQueue('...')` chaîné est sans effet sur ce chemin.

Le client reçoit sa réponse, mais **le worker FPM reste occupé** jusqu'à la fin du job. Occurrences déclenchées depuis des requêtes web :

| Fichier:ligne | Job (appel externe) |
|---|---|
| `app/Observers/EventObserver.php:131` | `EventGetLatLng` (géocodage Google, curl sans timeout) |
| `EventObserver.php:219, 236, 258` | `UserGetEventsDailyDirection` ×1-2 (Google Directions) à chaque création/modif/suppression d'événement |
| `app/Observers/MessageObserver.php:77, 102, 128` | `ProspectThreadMessage`, `ProspectSendToThreadMessage`, `ProspectSendToUserThreadMessage` |
| `app/Observers/OcrObserver.php:32` | `ProspectOCR` (Mindee) |
| `app/Observers/ProjectObserver.php:120` | `ProjectGetLatLng` |
| `app/Http/Controllers/API/Project/Prospect/MessageUserController.php:43` | `UserProspectMessage` |
| `app/Utils/Tracker/DeviceRepository.php:42` | `App::terminating` → `UserDeviceCreated` (SQL + notification) |

Les routes `/event/{id}` (PUT/POST/DELETE) et `/event` de votre liste passent par `EventObserver`. Dans les workers de queue, les 7 canaux `app/Channels/*Channel.php` (lignes 24-26) font la même chose : le job s'exécute dans le callback `terminating`, donc **hors du `--timeout`** de `queue:work` (l'alarme `pcntl` n'est armée que pendant le traitement du job queue).

**Pourquoi c'est lent.** `max_execution_time = 60` (`Dockerfile`) ne compte que le temps CPU sous Linux : une attente sur curl ou MySQL ne le déclenche pas. Un appel Google bloqué → un worker FPM bloqué, indéfiniment (voir F03). Avec un pool de 20, quelques événements modifiés au même moment suffisent à retirer 10 à 20 % de la capacité.

**Correctif proposé.**
```php
// EventObserver.php:131 (idem pour les autres lignes listées)
-        EventGetLatLng::dispatchAfterResponse($event);
+        EventGetLatLng::dispatch($event)->onQueue('google_map');   // file déjà écoutée par les workers

// Channels/*.php : remplacer par un appel direct (on est déjà dans un worker)
-        PersonUpdate::dispatchAfterResponse($account, $prospect)->onQueue('imports');
+        PersonUpdate::dispatch($account, $prospect)->onQueue('imports');
```
Les files `google_map`, `imports`, `google_calendar`… figurent toutes dans la commande `queue:work` (`supervisor/worker.conf:7`).

**Risque de régression.** Moyen : le résultat (lat/lng, itinéraire du jour) devient disponible quelques secondes plus tard, au lieu d'immédiatement après la réponse. Vérifier que le front ne relit pas ces champs dans la même seconde. Les jobs doivent rester sérialisables (`SerializesModels` déjà présent).

**Test.** `php artisan queue:monitor google_map,imports` ; créer un événement avec `location` et observer `SELECT COUNT(*) FROM jobs WHERE queue='google_map'`. Durée des workers FPM : `pm.status_path` + `slowlog` (déjà à 10 s, `docker/zz-pool.conf:7`) doivent cesser de signaler des traces dans `curl_exec`.

---

### F03 — Aucun timeout : curl, FPM, nginx, PDO

**Sévérité : critique (pire cas illimité) — Gain : borne le blocage — Effort : faible**

**Constat.**

| Couche | État |
|---|---|
| curl natif | `CURLOPT_TIMEOUT`/`CONNECTTIMEOUT` **absents** dans : `Services/Pipedrive.php` (13 `curl_init`, ex. lignes 44, 82, 120…), `Google/Map.php:39-52`, `Pappers.php`, `PappersImmo.php`, `GouvFr.php`, `Docusign.php`, `SMS/SMSBOX.php`, `Ringover.php`, `Mindee.php`, `Scraper.php`. Présents seulement dans `SMS/MTarget.php` et `SMS/Ultramsg.php` |
| `Http::` Laravel | sans `->timeout()` : `SettingController.php:107, 113`, `SMS/Brevo.php:112`. Présents : `CloudTalk`, `Kavkom`, `GoogleSheetDownloader` |
| FPM | `docker/zz-pool.conf` : pas de `request_terminate_timeout` |
| nginx | `nginx/conf.d/app.conf` : pas de `fastcgi_read_timeout` (défaut 60 s → c'est la source du **504**) |
| PDO | `config/database.php:61-63` : aucune option `PDO::ATTR_TIMEOUT` |
| Worker | `--timeout=3600` et `stopwaitsecs=3600` |

**Pourquoi c'est lent.** Par défaut `CURLOPT_TIMEOUT = 0` (infini). Une API tierce qui répond lentement bloque le processus : worker FPM (F02) ou worker de queue (jusqu'à 1 h, et hors timeout, voir F02).

**Correctif proposé.**
```php
// Pour chaque curl_setopt_array : ajouter
CURLOPT_CONNECTTIMEOUT => 5,
CURLOPT_TIMEOUT        => 20,
```
```php
Http::timeout(15)->connectTimeout(5)->withHeaders(...)->get(...)
```
```ini
; docker/zz-pool.conf
request_terminate_timeout = 55s     ; < fastcgi_read_timeout nginx pour renvoyer une erreur propre
pm.status_path = /fpm-status        ; (protéger l'accès) pour mesurer active/idle/listen queue
```
```nginx
location ~ \.php$ {
    fastcgi_connect_timeout 5s;
    fastcgi_read_timeout   60s;     # explicite
}
```
```php
// config/database.php mysql.options
PDO::ATTR_TIMEOUT => 5,   // connexion ; pour read timeout, utiliser MYSQL_ATTR_INIT_COMMAND "SET SESSION max_execution_time=20000" (SELECT uniquement)
```
Une façon propre de centraliser : une petite classe `App\Support\Http\Curl::defaults()` retournant ce tableau d'options.

**Risque de régression.** Moyen : un export ou un import Pipedrive légitimement long (> 20 s) échouera. Les jobs concernés doivent alors être réessayés (`--tries=3` déjà présent). Pour les uploads (Docusign, Mindee) utiliser 60 s.

**Test.** `grep -rn "curl_init" app | wc -l` vs `grep -rn "CURLOPT_TIMEOUT" app | wc -l` (doit converger). En préprod : `iptables -A OUTPUT -d <ip-api> -j DROP` et vérifier que la requête échoue en ≤ 20 s au lieu de rester pendue.

---

### F04 — Scheduler : même conteneur que FPM, séquentiel, heure pile, sans verrou effectif

**Sévérité : critique — Gain : élevé — Effort : faible**

**Constat** (`app/Console/Kernel.php`).

| Ligne | Tâche | Fréquence |
|---|---|---|
| 19 | `app:user-tracker` | `hourly()` (:00) |
| 25 | `app:label-prospects-count` | `hourly()` (:00) |
| 28 | `app:user-prospects-count` | `hourly()` (:00) |
| 31 | `app:role-users-count` | `hourly()` (:00) |
| 38-40 | `app:campaign` ×3 | 15 min, 30 min, `hourly()` |
| 34, 41, 44 | `app:relevance`, `campaign daily/once` | `daily()` (00:00) |
| 16 | `app:stat` | 00:10 |
| 47 | `sheet-sync:recover` | chaque minute, `withoutOverlapping()` (inopérant : F01) |

Aucune tâche n'a `runInBackground()` ni `onOneServer()`. `schedule:run` est lancé par `docker exec` dans le conteneur FPM (votre description) : les commandes consomment CPU et mémoire du conteneur qui sert le web, et s'exécutent **l'une après l'autre** dans le même processus `schedule:run` (:00 = tracker 2-5 min → label count → user count → role count → 3× campaign).

**Pourquoi c'est lent.** À :00 le CPU (4 cœurs) est saturé par `user-tracker` (un cœur à 100 % pendant 2-5 min) pendant que MySQL traite 26 000 requêtes + des `UPDATE` longs. Vos pics à 10 h et 16 h correspondent à des heures pleines.

**Correctif proposé.**
1. Décaler (« lissage ») :
```php
$schedule->command('app:user-tracker')->hourlyAt(3)->withoutOverlapping(30)->runInBackground();
$schedule->command('app:label-prospects-count')->hourlyAt(8)->withoutOverlapping(15)->runInBackground();
$schedule->command('app:user-prospects-count')->hourlyAt(13)->withoutOverlapping(15)->runInBackground();
$schedule->command('app:role-users-count')->hourlyAt(18)->withoutOverlapping(15)->runInBackground();
$schedule->command('app:campaign --frequency=hourly')->hourlyAt(23)->withoutOverlapping(30)->runInBackground();
```
(le paramètre de `withoutOverlapping()` = expiration du verrou en minutes ; le défaut de 24 h bloquerait tout après un crash.)
2. Sortir le scheduler du conteneur FPM : un service dédié dans `docker-compose.yml`, même image, `command: php artisan schedule:work`, avec `cpus: "1.0"` et `mem_limit`. Le conteneur `supervisor` existant (`docker-compose.yml:79-95`) peut aussi héberger un `[program:scheduler]` sans toucher au web.
3. Lancer les commandes lourdes avec `nice` : `$schedule->exec('nice -n 15 php artisan app:user-tracker')`.

**Risque de régression.** Faible. Le seul comportement qui change est l'horaire d'exécution (les tableaux de bord affichent des chiffres à :03 au lieu de :00). `withoutOverlapping` **nécessite F01** (cache partagé) pour être efficace.

**Test.** `php artisan schedule:list` ; pendant 24 h, comparer la courbe de 504 (`awk '$9==504' access.log | cut -c14-18 | sort | uniq -c`) avant/après.

---

### F05 — `/menu` et `/default-menu` : un COUNT sur la table prospects par menu, à chaque appel

**Sévérité : critique — Gain : élevé — Effort : faible (cache) à moyen (agrégat)**

**Constat.**

`app/Http/Controllers/API/Project/DefaultMenuController.php`
- `:27-36` boucle sur 4 menus (`not-trashed`, `processed`, `not-processed`, `waiting`) → **4 `COUNT(*)`** (`:63`).
- `:51-54` le `cache()->remember` est **commenté** : aucune mise en cache, même avec Redis.
- `waitingUsers` (`ProspectFilters.php:319-327`) = `whereHas('messages', fn => whereHas('users'))` : double `EXISTS` imbriqué.

`app/Http/Controllers/API/Project/MenuController.php`
- `:36-39` un `COUNT` par menu personnalisé, `:122` mis en cache 300 s (inopérant avec `array`, F01).
- `:119` la clé de cache contient `'menu.' . $menu` : `Model::__toString()` renvoie le **JSON complet du modèle** (dont `users`, `updated_at`, et `count` s'il est déjà posé) → clé très longue, différente après chaque modification, et jamais identique d'un appel à l'autre pour le même utilisateur si une relation chargée varie.

Chaque `COUNT` passe par `ProspectScope` (`app/Models/Scopes/ProspectScope.php:16-39`) : pour un non-admin, `WHERE creator_id = ? OR EXISTS(users) OR EXISTS(groups)`, non indexable en OR.

**Pourquoi c'est lent.** `prospects` ≈ 1,1 M lignes. Un COUNT filtré par projet balaie l'index `project_id` (jusqu'à plusieurs centaines de milliers de lignes pour un gros projet) et évalue des `EXISTS` corrélés par ligne. Ces routes sont rappelées par le SPA (rafraîchissement), donc N requêtes lourdes par utilisateur et par navigation.

**Correctif proposé.**
```php
// DefaultMenuController::countProspects — décommenter et corriger
return cache()->remember(
    "project.{$project->id}.user.".auth()->id().".default-menu.{$menu}", 60,
    fn () => $project->prospects()->filter($prospectFilters)->count()
);

// MenuController.php:119
-   'menu.' . $menu . '.' .
+   'menu.' . $menu->id . '.' . $menu->updated_at?->timestamp . '.' .
```
Agrégat unique pour trois menus (remplace 3 requêtes par 1) :
```sql
SELECT COUNT(*) AS total,
       SUM(processed_at IS NOT NULL) AS processed,
       SUM(processed_at IS NULL)     AS not_processed
FROM prospects
WHERE project_id = :p AND deleted_at IS NULL;   -- + clause ProspectScope pour non-admin
```
Index à valider par `EXPLAIN` : `prospects (project_id, deleted_at, processed_at)` (F16).

**Risque de régression.** Les compteurs peuvent être décalés de 60 s : acceptable pour un badge de menu (le code de `MenuController` l'accepte déjà à 300 s). Le menu `waiting` doit rester exact pour l'utilisateur concerné : TTL court (30-60 s) et invalidation depuis l'événement qui crée/archive un message.

**Test.**
```php
DB::listen(fn($q) => logger($q->time.'ms '.$q->sql));   // appeler /default-menu 2 fois
// avant : 4 COUNT à chaque appel ; après : 4 COUNT au 1er appel, 0 au 2e (60 s)
```
```sql
EXPLAIN ANALYZE SELECT COUNT(*) FROM prospects WHERE project_id = 343 AND deleted_at IS NULL AND processed_at IS NULL;
```
Comparer les compteurs avant/après sur un jeu de 3 projets.

---

### F06 — `/event` : requête non bornée, EXISTS imbriqués, OR non indexable

**Sévérité : critique — Gain : très élevé — Effort : moyen**

**Constat.** C'est la requête observée à 12-30 s (`select * from events where exists (select * from calendars where events.calendar_id = calendars.id and project_id = 343 and exists (select * from u…`).

`app/Http/Controllers/API/Project/EventController.php`
```php
324:   ::whereHas('calendar', function($query) use($project) {
325:        $query->where('project_id', $project->id);
326:   })
...
366:   ->get();            // la pagination est commentée juste après ; $count (ligne 271) n'est jamais utilisé
```
- `Calendar` porte `CalendarScope` (`Models/Scopes/CalendarScope.php:15-30`) : pour tout non-admin, le `EXISTS` sur `calendars` contient **lui-même** `EXISTS(users) OR EXISTS(roles)` (relations `users`/`roles` de `Calendar`, lignes 22 et 25 du scope) → c'est le « exists (select * from u… » observé.
- `:329-337` (non-manager) : `user_id IN (...) OR creator_id = ? OR EXISTS(user_event…)` → 3 branches, dont un `EXISTS` corrélé, combinées en OR.
- `EventFilters.php:120-134` `withUsers` : `whereHas('user') OR whereHas('users')` → deux `EXISTS` corrélés combinés en OR, par ligne d'événement.
- `app/Http/Controllers/API/EventController.php:85` (route `/api/event`) : même `->get()` non borné via `affectedEvents()`.
- `events` : index existants = `calendar_id`, `user_id`, `prospect_id`, … (FK), `started_at`, `ended_at` ; **aucun composite** couvrant `calendar_id + started_at` ni `user_id + started_at` (`2023_05_17_144228_create_events_table.php`).

**Pourquoi c'est lent.** MySQL ne sait pas transformer un `EXISTS` corrélé placé sous un `OR` en semi-join : il évalue le sous-requête pour chaque ligne candidate d'`events`. Sans borne de dates, le projet « vision_laser » renvoie tous ses événements (≈ plusieurs dizaines de milliers ?) — **À vérifier** : `SELECT COUNT(*) FROM events e JOIN calendars c ON c.id=e.calendar_id WHERE c.project_id=343;`. Dix requêtes simultanées de 12-30 s = 10 workers FPM sur 20 retenus.

**Correctif proposé.**
1. Résoudre les calendriers **une fois** (le scope s'applique à cette requête simple) et passer des IDs :
```php
$calendarIds = \App\Models\Calendar::where('project_id', $project->id)->pluck('id');   // CalendarScope inclus
$events = Event::whereIn('calendar_id', $calendarIds) ...                              // remplace whereHas('calendar')
```
2. Remplacer les OR d'`EXISTS` par des `IN` (semi-join indexable) :
```php
// EventFilters::withUsers  (valeur non vide)
return $builder->where(function ($q) use ($value) {
    $q->whereIn('events.user_id', $value)
      ->orWhereIn('events.id', DB::table('user_event')->whereIn('user_id', $value)->select('event_id'));
});
```
Si `EXPLAIN` montre que MySQL matérialise mal le `IN` sous `OR`, passer à `UNION` de trois sélections d'IDs (`user_id`, `creator_id`, `user_event`) puis `whereIn('id', $union)`.
3. **Garde-fou de volume** : exiger une fenêtre `startedAt/endedAt` (déjà supportée par `EventRequestFilters`) ou plafonner (`->limit(5000)`). *Avant* : vérifier dans l'access log que le front envoie toujours une plage : `grep "/event" access.log | grep -v started_at | wc -l`.
4. Index (F16) :
```sql
ALTER TABLE events ADD INDEX events_calendar_started (calendar_id, deleted_at, started_at),
                   ADD INDEX events_user_started     (user_id, deleted_at, started_at);
```

**Risque de régression.** Moyen. `whereHas('user', whereIn('id'))` ≡ `user_id IN (…)` parce que `User` n'a ni `SoftDeletes` ni scope actif (`User.php:27` commenté) — vérifier. Le cas `$value` vide (`withUsers([])`) doit conserver l'ancienne sémantique « événement ayant un utilisateur ».

**Test.** Garder l'ancienne requête dans un test et comparer les IDs :
```php
$old = Event::whereHas('calendar', fn($q)=>$q->where('project_id',343))->filter($f)->pluck('id');
$new = Event::whereIn('calendar_id',$calendarIds)->filter($f)->pluck('id');
assert($old->sort()->values() == $new->sort()->values());
```
```sql
EXPLAIN ANALYZE <requête issue de DB::listen/toSql()>;   -- attendu : range scan sur events_calendar_started, pas de "dependent subquery"
```

---

### F07 — `app:user-tracker` : N+1, filtrage PHP, aucun index sur `created_at`

**Sévérité : critique — Gain : très élevé (2-5 min → quelques secondes) — Effort : moyen**

**Constat** (`app/Console/Commands/UserTracker.php`).
- `:66` `User::all()` (tous les utilisateurs, même inactifs).
- `:72-75` pour **chaque utilisateur** : `Log::has('routePath')->where('user_id')->whereBetween('created_at')->get()` → hydrate des modèles Eloquent complets.
- `:83-89` et `:120-127` : 96 créneaux × `Collection::where('created_at', …)` ×2 conditions, **pour l'utilisateur et pour chacun de ses projets**.
- `:108-112` `$log->routePath->path` dans un `filter()` → **1 requête `select * from tracker_route_paths where id = ?` par log** (vos 26 000).
- `:103` `foreach ($user->projects …)` : relation chargée par utilisateur.
- `:92, :129` deux `updateOrInsert` (2 requêtes chacun) par utilisateur/projet.
- La table `tracker_logs` n'a pas d'index sur `created_at` (`2023_10_11_114832_create_trackers_table.php`) : seuls `user_id`, `session_id`, `method`, `route_path_id`.
- Recalcul de **toute la journée à chaque heure** (:00 recalcule depuis 00:00).

**Pourquoi c'est lent.** Vos mesures sont cohérentes avec le code : SQL 7 s (en local OVH, 0,27 ms/requête) ; en prod, 26 000 aller-retours × latence réseau (ex. 5 ms → 130 s) explique 2 min → 5 min. Les ≈ 107 s de PHP viennent du fait que `Collection::where('created_at', …)` relit `$log->created_at`, qui re-parse la chaîne en objet `Carbon` **à chaque accès** : 96 créneaux × 2 comparaisons × (logs de l'utilisateur) × (1 + nb de projets) → des millions d'instances Carbon.

**Correctif proposé.** Agréger en SQL, une seule requête pour tous les utilisateurs et projets.
```sql
SELECT l.user_id,
       p.id AS project_id,                                   -- NULL = total global de l'utilisateur
       FLOOR(TIME_TO_SEC(TIME(l.created_at)) / 900) AS slot,  -- 0..95
       COUNT(*) AS hits
FROM tracker_logs l
JOIN tracker_route_paths rp ON rp.id = l.route_path_id
LEFT JOIN projects p
       ON rp.path LIKE 'api/project/%'
      AND p.slug = SUBSTRING_INDEX(SUBSTRING_INDEX(rp.path, '/', 3), '/', -1)
LEFT JOIN user_project up ON up.user_id = l.user_id AND up.project_id = p.id
WHERE l.created_at >= :from AND l.created_at < :to
  AND l.user_id IS NOT NULL
GROUP BY l.user_id, p.id, slot
```
(`p.id` ne compte que si `up.user_id` est non nul, pour conserver la règle « projets de l'utilisateur » de `:103`.) Côté PHP : construire 96 cases par (user, project) à partir des lignes du résultat, puis remplacer les `updateOrInsert` par une transaction `DELETE FROM tracker_stats WHERE date = ?` + `INSERT` par lots de 500.
Pré-requis :
```sql
ALTER TABLE tracker_logs ADD INDEX tracker_logs_created_user (created_at, user_id, route_path_id),
                         ALGORITHM=INPLACE, LOCK=NONE;
```
Variante incrémentale : ne recalculer que `[heure précédente, maintenant]` et fusionner dans `hits_per_time` ; ne recalculer J-1 qu'une fois à 00:05.

**Risque de régression.** Moyen : c'est un changement de calcul. À comparer octet par octet sur des jours passés (`--date=`). Le fuseau (`TIME()` MySQL vs `Carbon::today()` PHP) doit être identique : vérifier `SELECT @@session.time_zone, NOW();` vs `config('app.timezone')`.

**Test.** Sur une copie :
```bash
php artisan app:user-tracker --date=2026-10-20     # ancienne version → exporter tracker_stats pour cette date
# nouvelle version sur la même date, puis :
SELECT * FROM tracker_stats_old o JOIN tracker_stats n USING (user_id, project_id, date) WHERE o.hits <> n.hits OR o.hits_per_time <> n.hits_per_time;   -- attendu : 0 ligne
```
Mesurer le temps : `time php artisan app:user-tracker` (cible < 15 s).

---

### F08 — Commandes de comptage : GROUP BY complet + `UPDATE … CASE WHEN` en O(N²)

**Sévérité : élevée — Gain : élevé — Effort : faible à moyen**

**Constat.**
- `LabelProspectsCount.php:42-46` : `prospect_label` (2,6 M) `JOIN prospects` (1,1 M) `GROUP BY label_id, project_id`, toutes les heures.
- `:48-63` : `UPDATE labels SET prospects_count = CASE WHEN id=… THEN … WHEN id=… … END WHERE id IN (…)` — une branche `WHEN` par label.
- `:65-70` : lit **tout l'historique** `stats WHERE user_id IS NULL AND key LIKE 'label.total.%' ORDER BY date DESC` ; la clé unique `daily_stats_uniqueness` commence par `project_id` et ne sert pas ce filtre.
- `UserProspectsCount.php:41-69` et `RoleUsersCount.php:41-62` : même schéma sur `user_project` et `roles`.
- **Bug fonctionnel** : un label dont le compte tombe à 0 n'est plus dans `$counts` → son ancienne valeur n'est jamais remise à 0 (idem `user_project` : le `ELSE 0` ne vaut que pour les lignes sélectionnées par le `WHERE`).
- `prospect_label` n'a ni clé primaire ni index composite (`2023_05_16_193449_create_prospects_table.php:69-82`) ; l'unicité `(prospect_id, label_id)` est commentée (`:81`).

**Pourquoi c'est lent.** Pour chaque ligne de `labels` concernée, MySQL évalue le `CASE` séquentiellement : ≈ N²/2 comparaisons (N = nombre de labels, **À vérifier** : `SELECT COUNT(*) FROM labels;`), plus un texte SQL de plusieurs centaines de Ko envoyé sur le réseau. Les lignes restent verrouillées pendant les 13-14 s mesurés ; toute requête qui veut les modifier (F04) attend.
La jointure avec `prospects` est inutile ici : le `project_id` d'un label se déduit de `labels.category_id → categories.project_id`.

**Correctif proposé.**
```sql
-- 1) index couvrant
ALTER TABLE prospect_label ADD INDEX prospect_label_label_prospect (label_id, prospect_id), ALGORITHM=INPLACE, LOCK=NONE;

-- 2) comptage sans jointure (même sémantique que le code actuel : lignes soft-deleted comprises)
SELECT label_id, COUNT(*) AS n FROM prospect_label GROUP BY label_id;

-- 3) UPDATE ciblé : seulement ce qui change, y compris les retours à 0
UPDATE labels l
LEFT JOIN (SELECT label_id, COUNT(*) AS n FROM prospect_label GROUP BY label_id) c ON c.label_id = l.id
SET l.prospects_count = COALESCE(c.n, 0)
WHERE l.prospects_count <> COALESCE(c.n, 0);
```
Même principe pour `user_project` (`LEFT JOIN … ON user_id AND project_id`) et `roles`.
Pour `stats` : ne lire que la dernière valeur par label (`SELECT key, value FROM stats s JOIN (SELECT key, MAX(date) d FROM stats WHERE user_id IS NULL AND key LIKE 'label.total.%' GROUP BY key) m …`) avec l'index `stats (user_id, key, date)`.
**Question métier** : le code compte aussi les prospects supprimés (`prospects.deleted_at`) et les pivots `prospect_label.deleted_at` ; `Utils/Stat/Value/LabelProspectsCount.php:37` les exclut. Corriger ou non change les chiffres affichés : à trancher avec le métier avant de modifier la sémantique.

**Risque de régression.** Faible si la sémantique est conservée. Le correctif du bug « jamais remis à 0 » change des chiffres (à valider).

**Test.**
```sql
EXPLAIN ANALYZE SELECT label_id, COUNT(*) FROM prospect_label GROUP BY label_id;   -- attendu : "Covering index scan", pas de jointure prospects
```
Comparer `SELECT id, prospects_count FROM labels` avant/après exécution sur une copie, hors labels à 0.

---

### F09 — Tracker synchrone : ≈ 12 requêtes SQL par requête HTTP, plus l'écriture de `tracker_logs`

**Sévérité : élevée — Gain : élevé (surtout avec latence réseau) — Effort : moyen**

**Constat.** Deux passes indépendantes recalculent les mêmes données :

1. `app/Listeners/UserAuthenticatedListener.php:29` (écouteur de `Authenticated`, donc à chaque requête authentifiée) appelle `Tracker::makeSessionData()` : `DeviceRepository::getId` (SELECT), `AgentRepository::getId` (SELECT), `CookieRepository::getId` (SELECT `firstOrCreate`), puis `tracker_user_banned_devices` (SELECT).
2. `UserTrackActivity.php:26` → `Tracker::boot()` → `getLogData()` → `getSessionId()` → **refait** `makeSessionData()` (3 SELECT), `SessionRepository::createSessionIfNew` (`findByUuid` SELECT `:139`, puis `UPDATE tracker_sessions SET updated_at` `:154-157`), `RoutePathRepository::getId` (SELECT route + SELECT route_path, `:23-37`), `INSERT tracker_logs` (`LogRepository.php:18`).

Total ≈ 4 + 3 + 2 + 2 + 1 = **12 requêtes** par requête HTTP (≈ 16 à la création de session), auxquelles s'ajoute `UPDATE users` (F10). `Tracker` n'est pas déclaré `singleton` (aucune occurrence dans `app/Providers/`) : chaque `app(Tracker::class)` repart de zéro. `GeoIpRepository.php:39` retourne `null` immédiatement : le code de géoloc en dessous est mort.
Écriture : `tracker_logs` porte 5 index secondaires (`user_id`, `session_id`, `method`, `route_path_id` + FK) pour chaque INSERT ; `method` n'est utilisé par aucune requête relevée.
`tracker_route_paths.path` stocke l'URL **avec identifiants** (`RoutePathRepository.php:51` `$request->path()`) : une ligne par prospect/événement consulté → table qui grossit sans fin (F17).

**Pourquoi c'est lent.** 12 allers-retours séquentiels vers un MySQL externe : à 2 ms ≈ 24 ms, à 10 ms ≈ 120 ms **par requête**, avant tout travail métier. Pour une route « légère » comme `/prospect/{id}/label` c'est la majorité du temps. Mesure obligatoire (**À vérifier**) : `DB::listen` sur une requête réelle.

**Correctif proposé** (du plus simple au plus ambitieux — chaque palier est indépendant) :
1. `app()->singleton(Tracker::class)` + mémoïser `makeSessionData()` : supprime 3 SELECT.
2. Mettre en cache (Redis, TTL 1 h) les correspondances `agent`, `device`, `route`, `route_path`, `cookie` → id (`Cache::remember`). Ces tables sont quasi statiques → 6 SELECT deviennent 0.
3. Ne mettre à jour `tracker_sessions.updated_at` qu'une fois par minute.
4. Différer l'écriture : dans `UserTrackActivity::terminate()`, pousser la ligne de log dans une liste Redis (`RPUSH`), et un job minute (`tracker:flush`) fait un `INSERT` multi-lignes de 500. Le coût par requête web tombe à 1 commande Redis.
5. Stocker le **motif de route** (`$route->uri()`) plutôt que l'URL réelle dans `tracker_route_paths`.
6. Supprimer l'index `tracker_logs_method_index`.

**Risque de régression.** Moyen pour (4) : perte possible de quelques logs en cas de crash Redis ; `ActionController.php:101-148` lit `tracker_logs` pour l'historique d'un utilisateur : accepter jusqu'à 1 min de retard. Pour (5), `ActionController.php:140-148` filtre sur `tracker_route_paths.path` : vérifier que la recherche ne dépend pas des identifiants.

**Test.**
```php
// Dans tinker ou un middleware temporaire
$n = 0; DB::listen(function ($q) use (&$n) { $n++; });
// appeler GET /api/project/{slug}/prospect/{id}/label avec un utilisateur de test → afficher $n
```
Avant : ≈ 25-35 requêtes totales ; objectif après paliers 1-4 : ≤ 10.

---

### F10 — `last_activity` écrit à chaque requête, géolocalisation HTTP synchrone, colonne `ip_city` trop courte

**Sévérité : élevée — Gain : moyen à élevé — Effort : faible**

**Constat.**
- `app/Http/Middleware/Authenticate.php:37-52` `pingLastActivity` : `auth()->user()->update(['last_activity' => now(), 'ip_address' => …])` **à chaque requête** — `now()` rend le modèle toujours « dirty », donc 1 `UPDATE users` par requête (plus la mise à jour de `updated_at`).
- `app/Observers/UserObserver.php:39-42, 150-173` : `updated()` → `getLocation()` : si l'IP a changé, appel **synchrone** `Location::get($ip)` (pilote IpApi, timeout 3 s + connexion 3 s : `vendor/stevebauman/location/src/Drivers/HttpDriver.php:54-55`) puis un 2ᵉ `UPDATE users`. Le plan gratuit de ip-api.com est limité (≈ 45 requêtes/min) : au-delà, réponses d'erreur.
- L'erreur `SQLSTATE 22001` (`ip_city` trop court) lève une exception dans `updated()` → la requête de l'utilisateur échoue en 500 à chaque changement d'IP vers une ville au nom long.
- `app/Http/Middleware/UserLastProjectCheck.php:35-39` : sur toute route **sans** projet (ex. `/api/event`, `/api/auth`) `last_project_id` est remis à `NULL` ; la requête projet suivante le repositionne (`:25`) **et** met à jour `user_project.last_activity` (`:31`). Un SPA qui alterne les deux types de route génère 2 écritures par navigation — et la seconde peut se bloquer sur les verrous de F04.

**Pourquoi c'est lent.** Une écriture sur la même ligne `users` par requête concurrente de la même personne (plusieurs onglets, polling) = contention de verrou ; chaque écriture traverse le réseau vers MySQL.

**Correctif proposé.**
```php
// Authenticate.php — écrire au plus une fois par minute, sans événements Eloquent
$user = auth()->user();
$ip = $request->ip();
if (!$user->last_activity || $user->last_activity->diffInSeconds() >= 60 || $user->ip_address !== $ip) {
    DB::table('users')->where('id', $user->id)->update(['last_activity' => now(), 'ip_address' => $ip]);
    if ($user->ip_address !== $ip) { UserGetLocation::dispatch($user->id, $ip)->onQueue('default'); }  // job : ip_city tronqué
}
```
```php
// migration : élargir ip_city (et vérifier les autres colonnes ip_*)
$table->string('ip_city', 255)->nullable()->change();
```
```php
// UserLastProjectCheck: ne PAS remettre à NULL sur les routes sans projet ; n'écrire user_project.last_activity que si > 60 s
```
`Stat.php:134` et `ProjectController.php:301` lisent `last_activity` avec un décalage de 5 min : la granularité de 60 s est sans effet visible.

**Risque de régression.** Faible. `UserObserver::updated` ne doit plus être le seul point d'entrée pour la géoloc ; vérifier qu'aucun autre code ne s'appuie sur la mise à jour de `users.updated_at` à chaque requête (`grep -rn "users.updated_at" app`).

**Test.** `SELECT COUNT(*) FROM information_schema.processlist` ne change pas ; mesurer `Com_update` avant/après (`SHOW GLOBAL STATUS LIKE 'Com_update'` sur 10 min de trafic). Reproduire l'erreur `22001` avec un utilisateur dont la ville dépasse la taille actuelle de colonne.

---

### F11 — Chaîne de middlewares et permissions : requêtes répétées par requête

**Sévérité : élevée — Gain : moyen à élevé — Effort : moyen**

**Constat.** Pour `/api/project/{slug}/prospect/{id}/label` le contrôleur (`Prospect/LabelController.php:19-24`) est trivial ; le coût est dans l'empilement :

| Étape | Fichier:ligne | Requêtes |
|---|---|---|
| Appartenance au projet | `ProjectUserAccessCheck.php:23` | `SELECT user_project … first()` |
| Rôles de l'équipe | `ProjectUserAccessCheck.php:29` | `load('roles')` (1-2) |
| Liaison du prospect | `ProspectScope.php:16-39` + route model binding | `can('prospectView', project)` ; en OR, 3 EXISTS pour les non-admins |
| Appartenance au prospect | `ProjectProspectCheck.php:46-52` | `prospect_user`, `prospect_group`, `groups()->pluck('id')` |
| Dernier projet | `UserLastProjectCheck` | 0-2 écritures (F10) |

`User::getIsProjectAdminAttribute()` (`User.php:115-118`) appelle `$this->can('', request()->project)` à **chaque accès** à `$user->is_project_admin` ; or tous les `*Scope` (`Scopes/CalendarScope.php:16`, `LabelScope.php:17`, `ProspectScope.php:19`, …) l'appellent à chaque requête Eloquent qu'ils filtrent. `EventController.php:302` appelle `getAllPermissions()` (hydrate toutes les permissions de l'utilisateur).

**Pourquoi c'est lent.** Chaque accès non mémoïsé = une vérification Gate/Spatie, ce qui n'est supportable qu'avec un cache de permissions fonctionnel (F01). Les requêtes d'appartenance sont des SELECT simples mais s'additionnent à la latence réseau (F09).

**Correctif proposé.**
```php
// User.php — mémoïser pour la durée de la requête (helper once() disponible en Laravel 10)
public function getIsProjectAdminAttribute()
{
    return once(fn () => request()->project && (request()->project->creator_id == $this->id || $this->can('', request()->project)));
}
```
Mettre en cache (Redis, TTL 60 s) l'appartenance `user_project` : clé `up:{project}:{user}`, invalidée à l'attache/détache d'utilisateur. Fusionner `ProjectProspectCheck` en une seule requête `EXISTS` (UNION `prospect_user`/`prospect_group`) au lieu de trois.

**Risque de régression.** Moyen : `once()` fige la valeur pour la requête ; ne pas appeler avant que `request()->project` soit résolu (le middleware `ProjectUserAccessCheck` s'exécute après `SubstituteBindings`). Tester avec un super-admin, un admin projet, un membre simple, un utilisateur hors projet (404 attendu).

**Test.** `DB::listen` compte les requêtes `permissions|roles|user_project|prospect_user` sur la même requête HTTP : avant ≥ 8, objectif ≤ 3.

---

### F12 — Workers `queue:work --once` : reboot complet à chaque cycle + polling MySQL de 9 files

**Sévérité : élevée — Gain : moyen à élevé (CPU et DB) — Effort : faible à moyen**

**Constat** (`supervisor/worker.conf:7`) : 8 processus `queue:work --once --sleep=3 --queue=sheet-sync,emails,google_calendar,google_contact,google_drive,google_map,imports,sms,default`. Avec `--once`, le processus se termine après un cycle (job traité **ou** attente de 3 s sans job) et supervisord le relance : le framework est rebooté.
`Kernel::commands()` (`Kernel.php:55`) instancie **toutes** les commandes Artisan à chaque boot, y compris `Campaign` (≈ 45 objets d'actions dans le constructeur) et `Relevance` (15 objets).
Le pilote `database` interroge `jobs` **une file après l'autre** : 9 `SELECT … FOR UPDATE` par cycle d'inactivité et par worker. `jobs` n'a qu'un index sur `queue` (`2023_06_30_121741_create_jobs_table.php:20`).

**Pourquoi c'est lent.** Estimation en régime inactif : 8 workers / 3 s ≈ 2,7 boots par seconde (≈ 0,15-0,3 s de CPU chacun, soit ≈ 0,4 à 0,8 cœur sur les 4, en concurrence avec FPM) et ≈ 24 requêtes `jobs` par seconde vers le MySQL distant. À vérifier : `docker stats supervisor` et `SHOW GLOBAL STATUS LIKE 'Com_select'` à vide.

**Correctif proposé.**
1. Redis comme pilote de queue (la connexion existe : `config/queue.php:65-72`, `retry_after` 3700) : `QUEUE_CONNECTION=redis` → plus de polling SQL, attente bloquante côté Redis.
2. Garder le pilote `database` mais lancer des workers persistants :
```ini
command=php artisan queue:work --queue=... --sleep=3 --tries=3 --timeout=600 --max-jobs=100 --max-time=900 --memory=256
```
`--once` a probablement été choisi pour éviter les fuites d'état (`Config::set('pipedrive.api_token', …)` dans `Jobs/Pipedrive/PersonUpdate.php:57-61`) ; réinitialiser la config dans `finally`, ou utiliser `--max-jobs=1` seulement pour la file `imports`.
3. À défaut : index `jobs (queue, reserved_at, available_at)`.
4. Vérifier `startsecs`/`startretries` de supervisor : un worker qui sort en moins d'1 s est compté comme échec de démarrage.

**Risque de régression.** Moyen : migration de queue = vider ou drainer les jobs `database` avant bascule (`php artisan queue:work --stop-when-empty`). Fuites de mémoire/état des jobs à surveiller.

**Test.** CPU du conteneur `supervisor` à vide (`docker stats`) avant/après ; `performance_schema.events_statements_summary_by_digest` pour `FROM jobs` (nombre d'exécutions/h).

---

### F13 — `retry_after` (90 s) < `--timeout` (3600 s) : exécutions en double

**Sévérité : élevée — Gain : moyen — Effort : faible**

**Constat.** `config/queue.php:41` : `database.retry_after = 90`. `supervisor/worker.conf:7` : `--timeout=3600`. Laravel exige `retry_after` > durée maximale d'un job : au bout de 90 s, un job encore en cours est **remis à disposition** et pris par un autre worker.
`ImportProspects.php:273-290` contient déjà un verrou pour se protéger de ce cas (« import already being processed by another worker »). Les autres jobs longs (`ExportProspects`, `PrepareProspectDownload`, `Pipedrive/*`, `ImportCheckDuplicatedProspects`…) n'ont ni verrou ni `$tries`/`$timeout`.

**Pourquoi c'est lent / risqué.** Doublons d'appels Pipedrive/Google, travail dupliqué, `attempts` incrémenté artificiellement (donc `MaxAttemptsExceeded` et entrées dans `failed_jobs`).

**Correctif proposé.**
```php
// config/queue.php
'database' => [ ..., 'retry_after' => 3700, ],   // > --timeout, comme la connexion redis (:69)
```
Et déclarer `public $timeout`/`$tries`/`backoff()` sur les jobs courts (30-60 s) afin de ne pas laisser un job tourner 1 h.

**Risque de régression.** Faible : un job planté n'est repris qu'après 3700 s au lieu de 90 s (mettre un `--timeout` plus court sur les jobs courts, F03).

**Test.** `SELECT queue, attempts, COUNT(*) FROM jobs GROUP BY 1,2;` et `SELECT COUNT(*) FROM failed_jobs WHERE exception LIKE '%MaxAttemptsExceeded%';` avant/après.

---

### F14 — OPcache et caches de bootstrap : non présents dans le Dockerfile (à vérifier)

**Sévérité : élevée — Gain : élevé (CPU) — Effort : faible**

**Constat.** `Dockerfile` : image `php:8.1-fpm`, `php.ini-production` copié, mais aucune ligne `docker-php-ext-install opcache`, `opcache.*`, ni `artisan config:cache|route:cache|event:cache` ; `composer install` est commenté (`Dockerfile:~66`). Dans les images Docker officielles, OPcache n'est **pas** activé sans cette étape. `routes/api.php` fait plusieurs centaines de lignes de routes.

**Pourquoi c'est lent.** Sans OPcache, PHP recompile Laravel + `vendor/` (milliers de fichiers) à **chaque requête** : coût CPU typiquement 30-60 % d'une requête légère. Sans `route:cache`, `routes/api.php` est réévalué à chaque requête.

**À vérifier d'abord :**
```bash
docker exec heroescrm php -v | grep -i opcache
docker exec heroescrm php -i | grep -E "opcache.enable|opcache.validate_timestamps|opcache.memory_consumption"
docker exec heroescrm ls bootstrap/cache        # config.php, routes-v7.php présents ?
```

**Correctif proposé.**
```dockerfile
RUN docker-php-ext-install opcache
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
```
```ini
; docker/opcache.ini
opcache.enable=1
opcache.memory_consumption=192
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0   ; avec redéploiement qui redémarre FPM
opcache.jit=disable
```
Au déploiement : `composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan event:cache`. `route:cache` est refusé si une route utilise une closure : lancer la commande en préprod d'abord.

**Risque de régression.** Faible/moyen : avec `validate_timestamps=0`, tout déploiement doit recharger FPM (`kill -USR2`). Avec `config:cache`, `env()` hors fichiers de config renvoie `null` : `grep -rn "env(" app routes` doit être vide.

**Test.** `ab -n 200 -c 10 https://…/api/ping` (ou `wrk`) avant/après ; temps de réponse d'une route authentifiée et charge CPU FPM.

---

### F15 — Notifications : double saut et `notify()` inutiles

**Sévérité : moyenne — Gain : moyen — Effort : faible**

**Constat.** 
- Les notifications `Pipedrive*`, `GoogleCalendar*`, `GoogleContact*`, `GoogleDrive*` sont `ShouldQueue`, passent par un canal personnalisé qui fait `PipedriveAccount::where(project_id)->first()` puis *redistribue* le vrai job (F02). Chaque événement coûte donc : 1 INSERT `jobs` (notification) → un boot de worker → un SELECT du compte → un 2ᵉ job (exécuté inline).
- `EventObserver.php:275, 287, 299` : `$event->user->notify(new GoogleCalendar…Event)` pour **chaque** création/modification/suppression d'événement, même si l'utilisateur n'a aucun compte Google ; l'absence de compte n'est découverte qu'à l'exécution du canal. De même `ProspectObserver.php:226, 237` pour les projets sans compte Pipedrive.
- `updated()` (`EventObserver.php:42-47`) notifie même quand aucun champ pertinent n'a changé (ex. `done`, `confirmed`).

**Pourquoi c'est lent.** 700-1200 notifications/h (0,2 à 0,33/s) × (boot de worker ≈ 0,2 s + 3-4 requêtes) : de l'ordre de 5-10 % d'un cœur en continu, et autant d'écritures `jobs`. Peu critique pris seul, mais évitable.

**Correctif proposé.** Tester l'existence du compte avant `notify()` (requête mise en cache 5 min) ; envoyer directement le job final (`PersonUpdate::dispatch(...)`) sans passer par une notification ; ne notifier que si `$event->wasChanged([...champs synchronisés])`.

**Risque de régression.** Faible/moyen : la synchronisation Google/Pipedrive ne doit pas manquer de changement ; liste exacte des champs synchronisés à confirmer (`Jobs/Google/Calendar/EventUpdate.php`).

**Test.** `SELECT queue, COUNT(*) FROM jobs GROUP BY 1` sur 1 h avant/après ; vérifier qu'un événement modifié met toujours à jour Google Calendar sur un compte de test.

---

### F16 — Index : récapitulatif justifié par les requêtes réelles

**Sévérité : moyenne (élevée cumulée) — Effort : faible (ALTER en ligne)**

Les index des clés étrangères existent déjà (InnoDB les crée). Ce qui manque est lié aux requêtes ci-dessous. Toutes les propositions utilisent `ALGORITHM=INPLACE, LOCK=NONE` et **doivent être validées par `EXPLAIN ANALYZE`** (aucune n'est appliquée).

| Table | Index proposé | Requête qui le justifie | Gain |
|---|---|---|---|
| `tracker_logs` | `(created_at, user_id, route_path_id)` | F07 : fenêtre horaire + regroupement | balayage complet 7,2 M → plage |
| `tracker_logs` | `(user_id, created_at)` (remplace `user_id`) | `ActionController.php:101-148` `where user_id … order by created_at` | tri sans filesort |
| `tracker_logs` | **supprimer** `method`, `session_id` si inutilisés | aucune requête relevée ; chaque INSERT met à jour chaque index | écriture plus rapide |
| `events` | `(calendar_id, deleted_at, started_at)` et `(user_id, deleted_at, started_at)` | F06 | élevé |
| `prospect_label` | `(label_id, prospect_id)` et `(prospect_id, label_id)` | F08 + `ProspectFilters.php:606` (`whereHas('labels')`) ; permet aussi la contrainte d'unicité commentée | élevé |
| `prospects` | `(project_id, deleted_at, processed_at)` | F05 | à valider par EXPLAIN |
| `stats` | `(user_id, key, date)` | `LabelProspectsCount.php:65-70` | élevé tant que la table grossit |
| `tracker_stats` | `(user_id, project_id, date)` | `updateOrInsert` `UserTracker.php:92, 129` | moyen |
| `jobs` | `(queue, reserved_at, available_at)` | F12 | moyen |
| `users` | `(last_activity)` | `ProjectController.php:301` (5 dernières minutes) | faible |

**Avant d'ajouter une contrainte d'unicité sur `prospect_label`**, rechercher les doublons : `SELECT prospect_id, label_id, COUNT(*) FROM prospect_label GROUP BY 1,2 HAVING COUNT(*) > 1 LIMIT 20;`.
Pour confirmer l'utilité réelle des index existants : `SELECT * FROM sys.schema_unused_indexes WHERE object_schema = DATABASE();` et `sys.schema_redundant_indexes`.

**Risque de régression.** Faible (lecture) ; l'ajout d'un index sur `tracker_logs` (1,25 Go) dure plusieurs minutes et consomme I/O : le faire hors heures de pointe.

---

### F17 — Tables à croissance illimitée

**Sévérité : moyenne — Gain : moyen à terme — Effort : moyen**

| Table | Croissance | Constat |
|---|---|---|
| `tracker_logs` | 7,2 M lignes / 1,25 Go, 1 ligne par requête API | aucune purge ; relue par `ActionController` et `UserTracker` |
| `tracker_route_paths` | 1 ligne par URL **distincte avec identifiants** (`RoutePathRepository.php:51`) | cardinalité non bornée (F09) |
| `tracker_sessions`, `tracker_cookies`, `tracker_agents` | par session/navigateur | idem |
| `stats` | `LabelProspectsCount.php:96-98` : insertion à chaque variation, chaque heure ; unicité `(project_id, user_id, key, date)` inefficace avec `NULL` | doublons possibles |
| `revisions` | `Event` et `Prospect` utilisent `RevisionableTrait` : 1 ligne par champ modifié | pas de purge |
| `jobs` / `failed_jobs` | `queue.failed.driver = database-uuids` | aucun `queue:prune-failed` planifié |
| `storage/logs/laravel.log` | canal `single` (`config/logging.php:57`) | un seul fichier non roulé |

Aucune commande de purge n'existe (`grep prune app/Console routes/console.php` est vide).

**Correctif proposé.**
```php
// Commande app:tracker-prune (hors heures de pointe, p.ex. 03:30), par lots pour éviter les gros verrous
do {
    $n = DB::table('tracker_logs')->where('created_at', '<', now()->subDays(90))->limit(10000)->delete();
} while ($n === 10000);
```
Planifier `queue:prune-failed --hours=720`, `model:prune`, passer le log en `daily` (`'days' => 14`). Conserver `tracker_stats` (agrégat) sans limite. Rétention à valider avec le métier (qui consulte l'historique > 90 jours ?).

**Risque de régression.** Moyen : suppression irréversible → **sauvegarde** et accord métier avant la première exécution ; ON DELETE CASCADE : `tracker_route_paths` supprimée ⇒ ses logs aussi.

**Test.** `SELECT table_name, ROUND((data_length+index_length)/1024/1024) MB FROM information_schema.tables WHERE table_schema=DATABASE() ORDER BY 2 DESC LIMIT 10;` avant/après ; dry-run (`SELECT COUNT(*) …`) avant toute suppression.

---

### F18 — `app:campaign` : six invocations, constructeur lourd, pagination fragile

**Sévérité : moyenne — Effort : moyen**

**Constat.** `Kernel.php:38-44` : 6 appels distincts ; à :00, trois (`every_fifteen_minutes`, `every_thirty_minutes`, `hourly`) partent en même temps, plus `daily/once` à minuit. `Campaign.php` (constructeur, ~40 objets d'actions) est instancié à **chaque** démarrage d'Artisan (F12).
`Campaign.php:176-186` : boucle de pagination `skip/take` alors que `:178` passe les campagnes de la page à `pending = true` et que la requête filtre `pending = false` (`:224`) : le jeu de résultats rétrécit pendant la boucle, donc `skip += take` saute des campagnes dès que plus de 100 sont éligibles (bug latent, aujourd'hui masqué si moins de 100 campagnes).
Les requêtes `matching()` utilisent aussi `skip/take` sur des prospects (OFFSET profond).

**Correctif proposé.** Charger les campagnes par `id > $lastId` (pagination par clé, `chunkById`) ; charger les actions de manière paresseuse (`app(Handler::class)` au moment du besoin) ; décaler les 3 invocations (`hourlyAt(23)`, `everyThirtyMinutes()` → `cron('7,37 * * * *')`). Lire `app/Models/Campaign.php::matching()` avant toute optimisation de fond (non analysé en détail ici).

**Risque de régression.** Moyen (comportement des campagnes) : tester sur un environnement avec des campagnes réelles, comparer les prospects traités.

**Test.** `php artisan app:campaign --frequency=hourly --id=<id>` avant/après sur une copie ; comparer `campaign_*` pivot (`begin_at/end_at`).

---

### F19 — `app:relevance` et `app:stat` : remises à zéro totales et écritures unitaires

**Sévérité : moyenne/faible — Effort : faible**

**Constat.**
- `Relevance.php:126, :155` : `UPDATE table SET field = 0` sur **toute** la table, puis `UPDATE … CASE WHEN …` (même motif que F08). Quotidien (00:00), en même temps que `campaign daily/once`.
- `Stat.php:155-179` : `updateOrInsert` par projet × statistique × utilisateur actif (des milliers de requêtes unitaires) ; `previousStat` (`:182-196`) relit l'historique par clé. Il tourne à 00:10 (hors pic), donc impact web limité, mais il compte dans le total de charge MySQL.

**Correctif proposé.** `UPDATE … WHERE field <> 0` pour la remise à zéro ; `upsert()` par lots de 500 sur la clé `(project_id, user_id, key, date)` (réécrire l'unicité avec `COALESCE` pour que `NULL` soit pris en compte, ou clé générée). Reporter `app:relevance` à 03:00.

**Risque de régression.** Faible. **Test.** Comparer les tables avant/après sur une copie (`SELECT … EXCEPT SELECT …`).

---

### F20 — `->get()` sans limite : pagination commentée

**Sévérité : moyenne — Effort : faible à moyen**

**Constat.** `Project/EventController.php:366-367` et `API/EventController.php:85-86` : `->get()` suivi de `//->paginate($count)` ; `$count` est calculé (`min(…, 500)`) mais jamais utilisé. Dans `Project/EventController.php` chaque événement charge jusqu'à 6 relations (`calendar`, `prospect`, `user`, `users`, `vehicle`, `prospect.labels`) : mémoire et CPU proportionnels à la taille du projet (et 128 Mo de `PHP_MEMORY_LIMIT` en prod, `docker-compose.yml:15`).
`DashboardEngine.php:940` et d'autres endroits utilisent aussi `->get()` sur des tables volumineuses (non audités ligne à ligne ici).

**Correctif.** F06 (fenêtre de dates obligatoire ou plafond), `cursorPaginate` pour les listes ; pour les commandes, `chunkById(500)` / `lazyById()` au lieu de `User::all()` (`UserTracker.php:66`).

**Test.** Pic mémoire : `memory_get_peak_usage(true)` journalisé en fin de requête ; comparaison avant/après sur le plus gros projet.

---

### F21 — Divers (faible)

- **Connexions MySQL non persistantes** : chaque requête FPM ouvre une connexion TCP (+ TLS éventuel) vers un MySQL distant. `PDO::ATTR_PERSISTENT => true` (20 processus = 20 connexions) réduirait la latence de connexion ; à tester (état des transactions, `wait_timeout`). Gain à mesurer : `SHOW GLOBAL STATUS LIKE 'Connections'` / `Aborted_connects`.
- **Sessions** : `SESSION_DRIVER=file` dans `.env.example:22` ; Sanctum SPA utilise la session à chaque requête. À vérifier en prod (`php artisan tinker --execute="echo config('session.driver');"`) ; `redis` recommandé après F01.
- **Fichiers** : `Dockerfile` — `PHP_MEMORY_LIMIT: 128M` ; vérifier que les exports/imports lourds ne tournent pas dans FPM.
- **Scheduler externe** : `docker exec … schedule:run` chaque minute reboote Laravel (≈ 0,3 s) ; `schedule:work` dans un conteneur dédié l'évite (F04).

---

## 4. Tableau trié par rapport gain / effort

| Rang | ID | Action | Gain | Effort | Réversibilité | Dépend de |
|---|---|---|---|---|---|---|
| 1 | F03 | Timeouts curl/Http/FPM/nginx | borne le pire cas | **S** | totale (config) | — |
| 2 | F01 | `CACHE_DRIVER=redis` (+ `REDIS_HOST=redis`) | très élevé | **S** | variable d'env | — |
| 3 | F04.1 | Décaler les tâches (`hourlyAt`) + `withoutOverlapping(n)` | élevé | **S** | un commit | F01 |
| 4 | F13 | `retry_after` = 3700 | moyen | **S** | une ligne | — |
| 5 | F16 (tracker_logs) | Index `created_at` + suppression `method` | élevé | **S/M** (ALTER long) | `DROP INDEX` | — |
| 6 | F14 | OPcache + `config/route/event:cache` | élevé | **S** | image Docker | — |
| 7 | F05 | Cache des menus + clé corrigée | élevé | **S** | un commit | F01 |
| 8 | F10 | Throttle `last_activity` + `ip_city` + géoloc en job | moyen/élevé | **S** | un commit + migration | — |
| 9 | F02 | `dispatchAfterResponse` → `dispatch()` en file | élevé | **M** | par fichier | F12/F13 |
| 10 | F07 | Réécrire `app:user-tracker` en SQL | très élevé | **M** | ancienne commande conservée | F16 |
| 11 | F06 | Réécriture `/event` + fenêtre de dates + index | très élevé | **M** | feature flag | F16 |
| 12 | F08 | Réécrire les 3 commandes de comptage | élevé | **M** | ancienne commande conservée | F16 |
| 13 | F09 | Tracker : singleton, cache d'ids, écriture différée | élevé | **M/L** | par palier | F01 |
| 14 | F11 | `once()` + cache d'appartenance | moyen/élevé | **M** | par fichier | F01 |
| 15 | F12 | Queue Redis ou workers persistants | moyen/élevé | **M/L** | variable d'env | F01 |
| 16 | F04.2 | Scheduler dans son propre conteneur | élevé | **M** | `docker-compose` | F01 |
| 17 | F17 | Commande de purge + `daily` logs | moyen | **M** | irréversible (sauvegarder) | accord métier |
| 18 | F15, F18, F19, F20, F21 | Optimisations secondaires | moyen/faible | **M** | par fichier | — |

---

## 5. Plan d'implémentation en étapes indépendantes et réversibles

Chaque étape : **branche dédiée**, déploiement séparé, mesure avant/après, retour arrière indiqué. Aucune étape n'est appliquée avant votre validation.

**Étape 0 — Mesure de référence (aucune modification)**
Activer pendant 24 h : `pm.status_path`, slow query log MySQL (`long_query_time=1`), `performance_schema` digests. Relever : nombre de 504 par heure, requêtes par appel HTTP (voir annexe A), top 20 requêtes (annexe B). *Rollback : sans objet.*

**Étape 1 — Garde-fous (F03, F13)**
`request_terminate_timeout`, `fastcgi_read_timeout`, `CURLOPT_*TIMEOUT`, `retry_after`. *Rollback : revert du commit.* Vérification : annexe C.

**Étape 2 — Cache partagé (F01)**
`CACHE_DRIVER=redis`, `REDIS_HOST=redis`, `permission:cache-reset`. *Rollback : remettre `array` (+ `php artisan config:clear`).*

**Étape 3 — Lissage du scheduler (F04.1)**
`hourlyAt`, `withoutOverlapping(30)`, `runInBackground()`. *Rollback : revert.* Vérification : `schedule:list`, courbe de 504 par minute.

**Étape 4 — Index (F16)** — un index par déploiement, hors pointe, `ALGORITHM=INPLACE, LOCK=NONE`. Ordre : `tracker_logs(created_at,…)` → `prospect_label(label_id,prospect_id)` → `events(...)` → `stats(user_id,key,date)`. *Rollback : `DROP INDEX`.*

**Étape 5 — Cache des menus (F05)** et **throttle `last_activity` (F10)**. *Rollback : revert.*

**Étape 6 — OPcache et caches de bootstrap (F14).** *Rollback : image précédente.*

**Étape 7 — Commandes réécrites (F07, F08)** : nouvelles commandes `app:user-tracker-v2`, etc., exécutées en parallèle de l'ancienne sur une copie ; comparaison des résultats ; bascule du Kernel. *Rollback : repointer le Kernel sur l'ancienne commande (conservée).*

**Étape 8 — `/event` (F06)** derrière un flag `config('perf.event_v2')` ; comparaison d'IDs ancien/nouveau sur 3 projets ; activation progressive par projet (`vision_laser` en dernier). *Rollback : flag à `false`.*

**Étape 9 — Jobs hors FPM (F02, F15)** : un observer à la fois (`EventObserver` d'abord). *Rollback : revert du fichier.*

**Étape 10 — Tracker allégé (F09)** : paliers 1→4 séparés. *Rollback : par palier.*

**Étape 11 — Workers et scheduler dédiés (F12, F04.2)** : `docker-compose` ; migration queue `database` → `redis` après drain. *Rollback : `QUEUE_CONNECTION=database`.*

**Étape 12 — Rétention des données (F17)** : sauvegarde, dry-run, validation métier, puis purge planifiée.

---

## Annexes — commandes de vérification (toutes en lecture seule)

### A. Compter les requêtes SQL d'un appel HTTP
```php
// php artisan tinker (ou middleware temporaire en préprod)
$queries = [];
DB::listen(function ($q) use (&$queries) { $queries[] = [$q->time, $q->sql]; });
// ... exécuter l'appel ...
echo count($queries), " requêtes, ", array_sum(array_column($queries, 0)), " ms\n";
collect($queries)->groupBy(1)->map->count()->sortDesc()->take(15)->dump();
```
SQL réellement généré pour une requête Eloquent : `$builder->toSql()` et `$builder->getBindings()`.

### B. MySQL : requêtes les plus coûteuses et verrous
```sql
SELECT DIGEST_TEXT, COUNT_STAR, ROUND(SUM_TIMER_WAIT/1e12,1) AS total_s, ROUND(AVG_TIMER_WAIT/1e9) AS avg_ms
FROM performance_schema.events_statements_summary_by_digest ORDER BY SUM_TIMER_WAIT DESC LIMIT 20;

SELECT * FROM sys.innodb_lock_waits\G
SELECT * FROM sys.schema_unused_indexes WHERE object_schema = DATABASE();
SELECT table_name, table_rows, ROUND((data_length+index_length)/1024/1024) AS mb
FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY mb DESC LIMIT 15;
EXPLAIN ANALYZE <SELECT à auditer>;     -- exécute réellement un SELECT : le lancer hors pointe sur les grosses tables
```

### C. PHP-FPM, nginx, workers
```bash
docker exec heroescrm curl -s http://127.0.0.1/fpm-status?full      # après activation de pm.status_path (accès restreint)
docker logs heroescrm 2>&1 | grep -c "slowlog"                      # traces > 10 s
docker exec heroescrm php -i | grep -E "opcache.enable|max_execution_time|memory_limit"
docker stats --no-stream                                            # CPU par conteneur, à :00 et à :30
awk '$9==504 {print substr($4,14,5)}' /var/log/nginx/access.log | sort | uniq -c | sort -rn | head
```

### D. Scheduler et queue
```bash
php artisan schedule:list
php artisan queue:monitor default,imports,google_calendar,google_map,sms,emails
```
```sql
SELECT queue, attempts, COUNT(*) FROM jobs GROUP BY queue, attempts;
SELECT COUNT(*) FROM failed_jobs WHERE failed_at > NOW() - INTERVAL 1 DAY;
```

### E. Éléments non couverts (à auditer ensuite si besoin)
`app/Models/Campaign.php::matching()` et les actions `app/Console/Commands/Campaign/*` ; `app/Utils/Stat/Builder/DashboardEngine.php` (≈ 1000 lignes) ; `app/Services/ProspectAutoAssignment.php` ; les jobs `Import*` (volume de lignes par import) ; `ProspectFilters` (924 lignes, 35 `whereHas`) pour la liste des prospects. Ils n'ont pas été lus ligne à ligne : les conclusions les concernant, le cas échéant, sont à confirmer par `EXPLAIN ANALYZE`.
