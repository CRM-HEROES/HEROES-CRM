# Connexion et sécurité du compte

## À quoi ça sert
Les pages de connexion, inscription, réinitialisation de mot de passe et double authentification permettent d’accéder au CRM et de sécuriser le compte.

## Comment faire
Utilisez « Se connecter sur Heroes CRM » pour ouvrir une session. Les pages de compte proposent aussi Google Authenticator et un parcours de mot de passe oublié.

## Règles
Les pages authentifiées nécessitent une session. L’activation de l’authentification à deux facteurs demande une confirmation dans le composant de réglage.

## Limites
Les règles de mot de passe et les cas de blocage/validation de connexion sont À vérifier dans les pages d’authentification et leurs validations serveur.

## Dans l'interface
Chemin : routes publiques `/login`, `/register`, `/password/reset` et `/password/reset/:token`; routes de double authentification `/two-factors/enable` et `/two-factors/login`. Le panneau compte est accessible par l’icône engrenage > « Paramètres » > « Google Authenticator (2 facteurs) ».

1. Sur la page de connexion, saisissez « Email » et « Mot de passe », puis « Se connecter sur Heroes CRM ».
2. Pour demander un lien de réinitialisation, utilisez « J'ai oublié mon mot de passe », puis suivez le formulaire; le libellé d’envoi est « Envoyer le lien de réinitialisation ».
3. Pour activer ou désactiver Google Authenticator, ouvrez « Google Authenticator (2 facteurs) » dans « Paramètres » et confirmez la demande affichée.

Droit requis : un compte pour se connecter; la double authentification est accessible à l’utilisateur connecté. D’autres règles de validation sont À vérifier. Après connexion, le CRM redirige vers l’espace autorisé; après demande de réinitialisation, un message de confirmation ou d’erreur est affiché.

Sources d’interface : `router/auth.js`, `components/auth/`, `components/layouts/setting/Layout.vue`, `router/index.js`, `lang/fr.json` (`auth.*`, `setting.google_auth.*`).

## Questions fréquentes
**Comment demander un lien de réinitialisation ?** Cliquez « J'ai oublié mon mot de passe » puis « Envoyer le lien de réinitialisation ».

**Comment activer le second facteur ?** Dans « Paramètres », ouvrez « Google Authenticator (2 facteurs) » et confirmez.

**Pourquoi l’accès est-il refusé ?** L’état du compte et les validations serveur déterminent l’accès; le diagnostic est À vérifier.
