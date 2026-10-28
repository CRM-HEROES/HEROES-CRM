# Monitoring Serveur

Le dashboard est accessible dans **Paramètres → Monitoring Serveur** (réservé au Super Admin). Le menu et l’API `GET /api/server-monitoring` sont réservés au Super Admin (`is_super_admin`), y compris en accès direct. Le dashboard recharge ses données toutes les dix secondes lorsqu’il est visible. Les données absentes restent indisponibles ; aucun chiffre de démonstration n’est affiché.

## Activer les métriques hôte et Docker

Le service facultatif `monitoring` utilise l’image PHP existante et publie un petit fichier JSON partagé. Il effectue la collecte en dehors de PHP-FPM : les requêtes du dashboard ne parcourent pas `/proc` et n’interrogent pas le moteur Docker.

Préparer le répertoire partagé, avec le groupe des processus PHP de l’image officielle (`www-data`, GID 33) :

```sh
docker exec -u root heroescrm sh -c 'mkdir -p /var/www/storage/app/server-monitoring && chown www-data:www-data /var/www/storage/app/server-monitoring && chmod 0770 /var/www/storage/app/server-monitoring'
```

En production :

```sh
docker compose -f docker-compose.yml -f docker-compose.monitoring.yml up -d --no-deps monitoring
```

Avec la surcharge de développement existante :

```sh
docker compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose.monitoring.yml up -d --no-deps monitoring
```

La commande ne redémarre pas les services du CRM. Vérification et arrêt du collecteur :

```sh
docker compose -f docker-compose.yml -f docker-compose.monitoring.yml logs --tail=50 monitoring
docker compose -f docker-compose.yml -f docker-compose.monitoring.yml stop monitoring
```

Le collecteur est le seul service recevant les montages hôte `/proc`, `/sys`, `/` et le socket Docker. Le conteneur PHP web n’a pas accès au socket. Le collecteur n’a pas de réseau ni de port, son système de fichiers est en lecture seule, ses capacités Linux sont retirées et ses ressources sont limitées. Le montage du socket en lecture seule ne restreint pas les commandes de l’API Docker : ce socket est une capacité sensible. Le code du collecteur appelle uniquement les deux endpoints GET d’inventaire et de statistiques.

Si le moteur utilise un socket rootless ou un autre emplacement, adapter le montage et `MONITORING_DOCKER_SOCKET`. Les variables de chemins sont définies dans `config/server-monitoring.php`. Un cache de configuration Laravel existant doit être régénéré selon la procédure habituelle si ces paramètres changent.

Pour une installation Linux sans Docker, `php artisan monitoring:collect --loop` peut être exécuté sous le gestionnaire de processus existant avec un utilisateur ayant accès au répertoire partagé. Le collecteur utilise alors les ressources Linux locales. Dans un conteneur sans montages hôte, il annonce explicitement que l’hôte est indisponible. Une collecte ponctuelle est possible avec `php artisan monitoring:collect` ; deux mesures espacées sont nécessaires pour les pourcentages CPU et débits réseau.

## Origine et périmètre des données

- **CPU hôte** : compteurs cumulés `/host/proc/stat`, différences entre mesures, nombre de cœurs logiques, charge à 1/5/15 minutes et uptime Linux. La première utilisation CPU reste inconnue jusqu’à la seconde mesure. Un redémarrage ou une interruption prolongée invalide les différences précédentes.
- **RAM hôte** : `MemTotal` et `MemAvailable` dans `/host/proc/meminfo`. La mémoire utilisée correspond à `MemTotal - MemAvailable`.
- **Disque hôte** : système de fichiers racine monté dans `/host/root`, et non couche inscriptible du conteneur ni somme des différents volumes. Le pourcentage correspond à l’espace total moins l’espace disponible exposé par le système de fichiers.
- **Réseau hôte** : compteurs de l’espace réseau du PID 1 de l’hôte (`/host/proc/1/net/dev`). Les interfaces loopback, veth et bridges Docker sont exclues, ainsi que les interfaces esclaves d’une autre interface, pour limiter le double comptage. Les débits entrants/sortants sont des différences en octets par seconde. La bande passante annoncée vient de `/sys/class/net/*/speed` lorsqu’elle existe ; une interface virtuelle peut ne pas publier de vitesse. Les compteurs remis à zéro ou les interfaces recréées invalident la mesure précédente.
- **Docker** : avec la surcharge Compose, inventaire et état des conteneurs du projet Compose CRM, puis statistiques des conteneurs en cours d’exécution. Le filtre est appliqué par Docker via le label `com.docker.compose.project`, pour éviter de parcourir les anciens conteneurs d’autres applications toutes les dix secondes. `MONITORING_DOCKER_PROJECT` sélectionne le projet ; sans cette valeur, une exécution native inspecte tous les conteneurs éligibles. Le CPU suit la convention Docker : 100 % représente un cœur utilisé et peut dépasser 100 % sur plusieurs cœurs. La mémoire retire le cache `inactive_file` / `total_inactive_file` lorsque disponible. Les limites mémoire sont celles retournées par Docker, y compris la limite de l’hôte si aucune limite spécifique n’est configurée. Les métriques Docker ne sont jamais présentées comme des mesures de l’hôte.
- **HTTP** : requêtes réellement traitées par Laravel, mesurées après passage dans la pile HTTP. Les ressources statiques servies directement par Nginx, les connexions refusées avant PHP et la latence réseau du navigateur ne sont pas incluses. Les temps de réponse sont des durées côté application ; la latence Redis est mesurée séparément. Les endpoints sont des modèles de routes, sans paramètres réels ni query strings. La fenêtre glissante et les éventuels échantillons partiels sont exposés par l’API.
- **Redis** : service Redis configuré dans Laravel, mémoire réelle et aller-retour vers le service. Une panne Redis n’empêche pas l’affichage des autres sections.
- **Laravel** : événements et métadonnées de logs, erreurs et exceptions, sans publication des messages, contextes, traces ni payloads sensibles. Les jobs échoués sont les événements observés depuis activation de la collecte ; le dashboard ne lit pas la table historique des jobs échoués. Le redémarrage des workers existants selon la procédure habituelle est nécessaire pour charger les nouveaux écouteurs dans les processus de queue déjà lancés.
- **Alertes** : seuils CPU/RAM/disque définis dans `config/server-monitoring.php`, erreurs HTTP récentes, erreurs Laravel des cinq dernières minutes et services indisponibles. `expected_containers` définit les services permanents attendus (PHP, Nginx, Redis et Supervisor par défaut), pour signaler aussi un arrêt propre ou une disparition sans traiter le conteneur ponctuel Certbot comme une panne. Les conteneurs arrêtés qui ne sont pas des services attendus restent consultables dans l’inventaire éligible, sans générer d’alerte de service CRM. Les états Docker `restarting`, `dead` ou `unhealthy` restent signalés. Une collecte système absente ou périmée reste signalée explicitement.

**Aucun monitoring MySQL ou de base de données n’est effectué.** Les conteneurs d’images de bases connues sont exclus avant la demande de statistiques Docker. Pour une image personnalisée, compléter `docker_exclude_images` dans la configuration avec son nom d’image ou de conteneur. Redis reste inclus pour son rôle de cache/service demandé.

## Charge et exploitation

Les écritures système sont atomiques (`system.json` et état privé des différences), avec des permissions `0640`. Un verrou empêche deux collecteurs de tourner simultanément. Le service réutilise les compteurs Linux : il ne lance pas de commandes shell système à chaque collecte. Les requêtes Docker sont parallélisées par groupes de huit avec un budget total de trois secondes et un délai individuel de deux secondes. Après une collecte Docker échouée, les compteurs CPU privés sont conservés pendant au plus 60 secondes pour calculer un delta réel à la reprise ; aucune ancienne mesure n’est affichée comme actuelle pendant l’échec. Par défaut, au plus 30 conteneurs sont détaillés ; l’API signale explicitement un inventaire tronqué ou des statistiques partielles. Adapter ces limites dans `config/server-monitoring.php` si nécessaire.

Le collecteur se réveille toutes les dix secondes, sans dépendre du scheduler Laravel. L’arrêt du collecteur ne bloque pas le CRM : les données deviennent périmées au-delà de 40 secondes avec la configuration par défaut. Les métriques applicatives sont stockées sur le système de fichiers partagé et ne nécessitent aucune migration SQL.

Les agrégats HTTP utilisent une fenêtre de 60 secondes, par tranches de 5 secondes ; les compteurs sont collectés au mieux avec des verrous non bloquants. Les routes sont bornées à 32 modèles par tranche (les autres sont regroupées) et les dix principaux agrégats sont affichés. Le P95 est la borne supérieure approximative d’un histogramme, pas un percentile exact. Les jobs échoués sont comptés sur 24 heures, avec les 30 derniers événements détaillés. Redis et les logs sont mis en cache dix secondes entre les administrateurs connectés. `SERVER_MONITORING_ENABLED=false` désactive l’enregistrement HTTP et des jobs ; le dashboard signale alors ces deux sources comme désactivées.

Après déploiement du code, appliquer la procédure habituelle de compilation des assets (`npm run development` en développement ou `npm run production`) et de régénération des caches de configuration/routes s’ils sont utilisés. Le polling du monitoring ne met pas à jour la présence ou le dernier projet de l’administrateur. L’authentification utilise toujours Sanctum, sans le hook applicatif d’activité ; le contrôleur exige ensuite le rôle Super Admin.

Vérifications ciblées sans lancer le build frontend :

```sh
docker exec heroescrm vendor/bin/phpunit tests/Unit/ServerMonitoringSystemCollectorTest.php
docker exec heroescrm vendor/bin/phpunit tests/Unit/Monitoring
docker exec heroescrm vendor/bin/phpunit tests/Feature/ServerMonitoringTest.php
node tests/frontend/server-monitoring.test.cjs
docker compose -f docker-compose.yml -f docker-compose.monitoring.yml config --quiet
```

Références : [API Docker Engine, statistiques des conteneurs](https://docs.docker.com/reference/api/engine/version/v1.45/#tag/Container/operation/ContainerStats), [interprétation des statistiques mémoire Docker](https://docs.docker.com/reference/cli/docker/container/stats/), [compteurs Linux `/proc`](https://docs.kernel.org/filesystems/proc.html).
