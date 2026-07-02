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
ou
php artisan migrate:fresh --seed
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
