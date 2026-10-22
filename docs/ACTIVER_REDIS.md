# Activer Redis comme cache en production

Le service `redis` existe déjà dans `docker-compose.yml`, l'extension `phpredis` est installée dans l'image, et `ImportProspects` l'utilise déjà pour ses verrous.
Il reste à dire à Laravel de s'en servir comme cache. Tant que `CACHE_DRIVER=array`, le cache est vide à chaque requête.

## Ce que Redis débloque

| Mécanisme | Sans Redis (`array`) | Avec Redis |
|---|---|---|
| Permissions Spatie (24 h) | rechargées à chaque requête | chargées une fois |
| Compteurs de menus (`/menu`, `/default-menu`) | 1 à 4 COUNT sur `prospects` à chaque appel | 1 fois par minute et par utilisateur |
| Tracker (ids agent, appareil, cookie, route) | ≈ 6 SELECT à chaque requête | 0 (cache 30 min) |
| Ban d'appareil (`UserAuthenticatedListener`) | 1 SELECT à chaque requête | 1 par minute et par appareil |
| `withoutOverlapping()` du scheduler | sans effet | effectif |
| Rate limiter `api` (300/min) | sans effet | effectif |
| Statistiques (`cache()->remember`, ~20 fichiers) | jamais servies du cache | servies du cache |

## Procédure

1. Mettre à jour la définition du service (nouvelle commande `redis-server --maxmemory 256mb --maxmemory-policy volatile-lru`) :
   ```bash
   docker compose up -d redis
   docker exec redis-heroes redis-cli ping        # PONG
   ```
2. Dans le `.env` de production (valeurs, pas de secret ici) :
   ```dotenv
   CACHE_DRIVER=redis
   REDIS_HOST=redis          # nom du service docker, pas 127.0.0.1
   REDIS_CLIENT=phpredis
   ```
3. Recharger la configuration et vider les anciens caches de permissions :
   ```bash
   docker exec heroescrm php artisan config:clear
   docker exec heroescrm php artisan permission:cache-reset
   docker exec supervisor php artisan config:clear   # le conteneur des workers lit aussi le .env
   docker restart heroescrm supervisor               # PHP-FPM et workers reprennent la config
   ```

## Vérifier

```bash
docker exec heroescrm php artisan tinker --execute="Cache::put('ping', 1, 60); echo Cache::get('ping'), ' ', config('cache.default');"
# attendu : 1 redis
docker exec redis-heroes redis-cli -n 1 dbsize         # le nombre de clés augmente avec le trafic
docker exec redis-heroes redis-cli info memory | grep used_memory_human
```

Nombre de requêtes SQL par appel API (avant / après) : dans `php artisan tinker`, utiliser `DB::listen` autour d'un appel,
ou regarder `SHOW GLOBAL STATUS LIKE 'Com_select'` sur 10 minutes de trafic.

## Si Redis tombe

- Le tracker et le listener d'authentification retombent sur la base de données (ils attrapent l'erreur du cache).
- Les autres usages du cache (permissions Spatie, compteurs de menus, statistiques) lèvent une exception tant que Redis est indisponible : surveiller le service (`restart: unless-stopped` est déjà positionné).
- Retour arrière immédiat : remettre `CACHE_DRIVER=array` (ou `file`), puis `php artisan config:clear` et redémarrer FPM.

## Délais de fraîcheur à connaître

- Compteurs de `/default-menu` : jusqu'à 60 s de retard. Compteurs de `/menu` : 5 min (inchangé).
- Statut de bannissement d'un appareil : 60 s, mais la confirmation ou l'annulation d'un bannissement vide le cache immédiatement.
- Ids du tracker : 30 min. Si des lignes de `tracker_*` sont purgées à la main, vider le cache (`php artisan cache:clear`).
