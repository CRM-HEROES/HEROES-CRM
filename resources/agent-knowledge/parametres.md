# Paramètres

## À quoi ça sert
Le panneau « Paramètres » regroupe les préférences du compte, les connexions externes et des accès aux réglages du projet.

## Comment faire
Ouvrez l’icône engrenage dans la barre du haut. Sélectionnez l’entrée voulue pour ouvrir un réglage, une fenêtre ou une page.

## Règles
Certaines entrées ne sont affichées que pour un projet ou selon des droits. Monitoring Serveur est réservé au super-admin.

## Limites
Les entrées toujours affichées sont « Langue » (si la langue est disponible), « Google Authenticator (2 facteurs) », « Google Calendar », « Google Contact », « claude.ai » (clé de l’API Claude, indispensable pour utiliser les agents IA), « Appareils connectés », « Modifier les icones des menus », « Gérer les tutoriels » et « Notifier les RDVs proches ». Les entrées liées à un projet ouvert sont « Gérer mes fichiers », « Pipeline », « Corbeille » et, avec `all.project`, « Paramètre du project {project} », « Liste des utilisateurs » et « Google Drive ». « Monitoring Serveur » est réservé aux super-administrateurs. L’entrée Pipedrive existe dans le code mais elle est désactivée (commentée).

## Dans l'interface
Chemin : icône engrenage en haut de page > panneau « Paramètres » (`layouts/setting/Slide.vue`, `layouts/setting/Layout.vue`).

1. Choisissez « Langue » pour ouvrir le réglage de langue.
2. Dans un projet, utilisez « Paramètre du project {project} » ou « Liste des utilisateurs » si ces entrées apparaissent.
3. Pour relier un service, sélectionnez « Google Drive », « Google Calendar » ou « Google Contact »; le compte ou l’état de connexion apparaît ensuite dans la ligne.
4. « Gérer mes fichiers », « Pipeline », « Appareils connectés », « Corbeille », « Modifier les icones des menus », « Gérer les tutoriels » et « Notifier les RDVs proches » ouvrent leur panneau, page ou réglage associé.
5. « Monitoring Serveur » apparaît uniquement pour `user.is_super_admin`.

Droits requis : projet + `all.project` pour les entrées projet/utilisateurs; super-admin pour « Monitoring Serveur ». Les autres entrées ne demandent aucun droit particulier. La ligne « claude.ai » affiche une coche verte quand la clé API enregistrée est valide; sinon un message précise la cause (clé invalide ou expirée, trop de demandes, vérification impossible). Après action, le panneau concerné s’ouvre ou l’état de connexion est actualisé.

Sources d’interface : `components/layouts/Header.vue`, `components/layouts/setting/Slide.vue`, `components/layouts/setting/Layout.vue`, `components/server-monitoring/Dashboard.vue`, `router/index.js`, `lang/fr.json` (`setting.*`).

## Questions fréquentes
**Comment ouvrir les paramètres ?** Cliquez l’icône engrenage de la barre du haut.

**Pourquoi « Liste des utilisateurs » est-elle absente ?** Elle nécessite un projet et `all.project`.

**Qui voit « Monitoring Serveur » ?** Un utilisateur super-admin uniquement.

## Pour aller plus loin
Google, Pappers et clé Claude : sujet `google-pappers-claude`. Services d’envoi : sujet `services-sms-email-telephonie`. Utilisateurs : sujet `gestion-utilisateurs`.
