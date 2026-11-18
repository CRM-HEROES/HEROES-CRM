# Connexion et sécurité du compte

## À quoi ça sert
Les pages de connexion, inscription, réinitialisation de mot de passe et double authentification permettent d’accéder au CRM et de sécuriser le compte.

## Comment faire
Utilisez « Se connecter sur Heroes CRM » pour ouvrir une session. Les pages de compte proposent aussi Google Authenticator et un parcours de mot de passe oublié.

## Règles
Les pages authentifiées nécessitent une session. L’activation de l’authentification à deux facteurs demande une confirmation dans le composant de réglage.

## Limites
Un mot de passe choisi par l’utilisateur (réinitialisation, invitation, changement) doit avoir au moins 12 caractères avec une minuscule, une majuscule, un chiffre et un symbole. La création de compte par `/register` n’impose, elle, que la saisie et la confirmation du mot de passe. Après 3 échecs de connexion consécutifs, le compte est bloqué et les administrateurs sont prévenus par e-mail; un administrateur doit le débloquer. Un mot de passe temporaire expiré refuse la connexion (« Votre mot de passe temporaire a expiré. Veuillez contacter l’administrateur. »). Une connexion depuis une nouvelle ville est bloquée jusqu’à la confirmation par un e-mail envoyé à l’utilisateur. Avec Google Authenticator activé, un code est demandé à la connexion, sauf si l’appareil est reconnu selon le type de vérification choisi.

## Dans l'interface
Chemin : routes publiques `/login`, `/register`, `/password/reset` et `/password/reset/:token`; routes de double authentification `/two-factors/enable` et `/two-factors/login`. Le panneau compte est accessible par l’icône engrenage > « Paramètres » > « Google Authenticator (2 facteurs) ».

1. Sur la page de connexion, saisissez « Email » et « Mot de passe », puis « Se connecter sur Heroes CRM ».
2. Pour demander un lien de réinitialisation, utilisez « J'ai oublié mon mot de passe », puis suivez le formulaire; le libellé d’envoi est « Envoyer le lien de réinitialisation ».
3. Pour activer ou désactiver Google Authenticator, ouvrez « Google Authenticator (2 facteurs) » dans « Paramètres » et confirmez la demande affichée.

Droit requis : un compte pour se connecter; la double authentification est accessible à l’utilisateur connecté. Activer ou désactiver Google Authenticator ne demande aucun droit particulier. Après connexion, le CRM redirige vers l’espace autorisé; après demande de réinitialisation, un message de confirmation ou d’erreur est affiché.

Sources d’interface : `router/auth.js`, `components/auth/`, `components/layouts/setting/Layout.vue`, `router/index.js`, `lang/fr.json` (`auth.*`, `setting.google_auth.*`).

## Questions fréquentes
**Comment demander un lien de réinitialisation ?** Cliquez « J'ai oublié mon mot de passe » puis « Envoyer le lien de réinitialisation ».

**Comment activer le second facteur ?** Dans « Paramètres », ouvrez « Google Authenticator (2 facteurs) » et confirmez.

**Pourquoi l’accès est-il refusé ?** Causes possibles : mot de passe erroné (3 échecs bloquent le compte), compte bloqué, mot de passe temporaire expiré, connexion depuis une nouvelle ville en attente de confirmation par e-mail, ou code Google Authenticator à saisir. Dans les deux premiers cas, contactez un administrateur.
