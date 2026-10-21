# Heroes CRM

Ce projet est une application web utilisant Laravel 10 comme backend et Vue 3 comme frontend.

## Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés sur votre machine :

-   PHP >= 8.1
-   Composer
-   Node.js >= 16
-   NPM
-   MySQL
-   Imagick

## Installation

### 1. Cloner le dépôt

```sh
git clone https://github.com/DanielSautot/groupe-gmi.git
cd groupe-gmi
```

### 2. Installer les dépendances PHP

```sh
composer install
```

### 3. Configurer l'environnement

Copiez le fichier `.env.example` en `.env` et modifiez les variables de configuration selon votre environnement.

```sh
cp .env.example .env
```

Générez la clé d'application Laravel :

```sh
php artisan key:generate
```

### 4. Configurer la base de données

Créez une base de données et mettez à jour les paramètres de connexion dans `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nom_de_la_base
DB_USERNAME=utilisateur
DB_PASSWORD=mot_de_passe
```

Appliquez les migrations et les seeders :

```sh
php artisan migrate --seed
```

### 5. Installer les dépendances frontend

```sh
npm install
```

### 6. Compiler les assets Vue 3

```sh
npm run watch
```

### 7. Lancer le serveur de développement

```sh
php artisan serve
```

### Ou lancer le serveur de développement avec Docker

```sh
docker compose -f docker-compose.yml -f docker-compose.dev.yml up --build
```

## Compilation pour la production

Pour compiler l'application en mode production :

```sh
npm run production
```

## API et Routes

Si vous utilisez Laravel Sanctum pour l'authentification API, assurez-vous d'exécuter :

```sh
php artisan sanctum:install
```

### Exécution des commandes post-déploiement

```sh
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Queue à lancer sur le serveur

```sh
php artisan queue:work --tries=3 --queue=google_event,google_map,google_drive,imports,emails,woocommerce,media,sms,documents
```

Les calculs d’itinéraires utilisent la connexion `redis`, la file `google_map` et
des verrous partagés dans le cache `redis`. Ces choix sont configurables via
`EVENT_DIRECTIONS_QUEUE_CONNECTION`, `EVENT_DIRECTIONS_QUEUE` et
`EVENT_DIRECTIONS_CACHE_STORE`. Le worker Supervisor écoute déjà `google_map` ;
sa connexion doit correspondre à celle des itinéraires. Pour un worker manuel :

```sh
php artisan queue:work redis --queue=google_map --tries=3 --timeout=90
```

L’API détail renvoie `202` avec `pending` pendant le calcul, puis `200` avec
`ready` ou `empty`. Les endpoints de statut et de résumés groupés lisent les
résultats disponibles sans déclencher de calcul Google.

#### Chargement des agendas

Les vues jour et mois chargent leurs dates affichées dès le premier appel. La
semaine charge les colonnes visibles avec un jour de marge de chaque côté. Le
cache navigateur conserve jusqu’à 90 jours pendant une minute : les plages qui
se chevauchent partagent leurs requêtes en cours et seuls les jours manquants
sont demandés. Une modification de rendez-vous ou de calendrier invalide ce
cache. Les événements des autres projets sont chargés lorsque leur affichage
est activé.

Ces requêtes utilisent `filters.overlapping={startedAt,endedAt}` pour inclure
les rendez-vous traversant les limites d’une journée. La carte conserve ses
filtres de dates habituels avec
`store.dispatch(FETCH_EVENTS, { incremental: false })`.

#### Backup de la base de production

Le script `scripts/backup-heroescrm.sh` fait un `mysqldump` compressé dans `/home/heroes/backup` (7 jours de rétention).
Les identifiants ne sont pas dans le dépôt : ils sont lus dans `/home/heroes/.heroescrm-backup.env`
(modèle : `scripts/backup-heroescrm.env.example`, à protéger avec `chmod 600`).

```sh
chmod +x scripts/backup-heroescrm.sh
./scripts/backup-heroescrm.sh                 # sauvegarde manuelle
# cron (tous les jours à 02h00) :
# 0 2 * * * /home/heroes/Heroescrm/scripts/backup-heroescrm.sh
```
