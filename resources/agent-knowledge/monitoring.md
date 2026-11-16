# Monitoring

## À quoi ça sert
La page Monitoring Serveur présente l’état et les métriques du serveur, des conteneurs et de l’application, ainsi que les alertes disponibles.

## Comment faire
Ouvrez la page depuis le panneau « Paramètres », puis consultez les métriques. « Actualiser » demande une nouvelle lecture.

## Règles
L’accès est réservé au super-admin, imposé dans la route et dans le composant.

## Limites
Des métriques peuvent être indisponibles selon les sources de collecte; la page affiche alors les raisons disponibles. Ces données ne sont pas des outils de gestion du serveur.

## Dans l'interface
Chemin : icône engrenage > « Paramètres » > « Monitoring Serveur » (`components/layouts/setting/Layout.vue`), route `server-monitoring`.

1. Ouvrez « Monitoring Serveur ».
2. Consultez les sections « Serveur hôte », « Réseau hôte », « Requêtes HTTP » et les alertes affichées.
3. Cliquez « Actualiser » pour relancer le chargement; l’écran indique l’état de collecte et l’heure de réception quand disponible.

Droit requis : `user.is_super_admin === true`, condition appliquée par le routeur et le composant. Sans ce droit, l’entrée et la page ne sont pas accessibles. Après actualisation, les métriques actualisées ou un message d’indisponibilité sont affichés.

Sources d’interface : `components/layouts/setting/Layout.vue`, `components/server-monitoring/Dashboard.vue`, `router/index.js`.

## Questions fréquentes
**Qui peut accéder au monitoring ?** Un super-admin.

**Comment recharger les données ?** Cliquez « Actualiser ».

**Que signifie une métrique indisponible ?** La source de collecte ne fournit pas cette mesure; consultez le message affiché.
