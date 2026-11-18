# Gestion des utilisateurs

## À quoi ça sert
Créer des utilisateurs, les ajouter à un projet, les bloquer ou débloquer, leur envoyer un lien de changement de mot de passe, consulter leur activité et se connecter à leur place pour les aider.

## Comment faire
Icône engrenage > « Paramètres » > « Liste des utilisateurs » (visible avec `all.project`) : la liste des utilisateurs du projet. Cliquez un utilisateur pour ouvrir son profil et ses actions.

## Règles
- **Créer un compte :** un nouvel utilisateur reçoit un e-mail d’invitation valable 48 heures; il y choisit lui-même son mot de passe (12 caractères au minimum). Tant qu’il ne l’a pas fait, le compte reste « en attente ». Si l’e-mail ne peut pas partir, le compte n’est pas créé. Un administrateur ne définit jamais le mot de passe d’un autre : il envoie un lien de changement de mot de passe.
- **Ajouter à un projet :** on ne peut ajouter au projet qu’un utilisateur qui existe déjà (recherché par son e-mail). S’il n’existe pas, on propose de le créer. Un utilisateur déjà dans le projet ne peut pas être ajouté deux fois.
- **Qui peut quoi :** ajouter ou retirer un utilisateur du projet est réservé aux super-administrateurs et aux détenteurs de `all`. Seul un super-administrateur peut créer un super-administrateur.
- **Blocage :** un compte se bloque tout seul après 3 mots de passe erronés; un administrateur du projet peut aussi le bloquer ou le débloquer à la main.
- **Se connecter à la place d’un utilisateur** ne marche que pour un membre du projet et pour un détenteur de `all`; on revient à son propre compte avec « Retrouver mon compte ».

## Limites
Modifier la fiche d’un utilisateur demande le droit `all.project.user.update`, qui s’obtient avec `all`. Le profil d’un super-administrateur ne peut être modifié que par lui-même. Les droits d’un utilisateur se gèrent dans « Permissions » (voir la fiche Droits et permissions).

## Dans l'interface
Chemin : « Paramètres » > « Liste des utilisateurs » > clic sur un utilisateur (`components/user/`).

1. **Créer ou ajouter :** « Ajouter » ouvre « Nouvel utilisateur » avec le champ « Saisir l'email de l'utilisateur ». Si l’e-mail existe, l’utilisateur est ajouté au projet. Sinon, « Aucun utilisateur ayant ce mail n'a été trouvé dans notre base de données. » s’affiche avec « Créer l'utilisateur » : saisissez le prénom, le nom (facultatif), l’e-mail et, si besoin, l’adresse. À l’accueil, « Nouvel utilisateur » propose aussi « Créer un projet professionnel », « Créer un projet personnel » ou « Créer un projet démo ».
2. **Actions d’un profil** (icônes du profil) : « Fichiers », « Dupliquer le profil pour d'autres utilisateurs » (« Duplication de profil » : « Dupliquer depuis » un utilisateur « Dupliquer pour » d’autres), « Historique de connexion », « Actions faites par l'utilisateur », « Permissions », « Véhicules », « Paramètre de la fiche prospect », « Paramètre du tableau de prospects » et « Paramètre de la fiche utilisateur ».
3. **Mot de passe :** l’icône clé (« Envoyer un email de changement de mot de passe ») envoie le lien. Elle est disponible pour soi-même et pour les détenteurs de `all`.
4. **Blocage :** l’icône cadenas (« Bloquer l'accès de cet utilisateur » ou « Débloquer l'accès de cet utilisateur ») bascule le blocage; on ne peut pas bloquer son propre compte.
5. **Se connecter avec ce compte :** icône « Se connecter avec ce compte » (droit `all`).
6. **Blocs du profil :** « Calendriers affectés », « Catégories de filtre affectées », « Documents affectés », « Dossiers affectés », « Groupes affectés », « Menus affectés », « Actions sur les devis affectées », « Etats de devis affectés », « Etapes de devis affectées », « Questionnaires affectées », « Rôles affectés », « Canaux de discussion affectés », « Utilisateurs affectés », ainsi que « Connexions », « Locations », « Exports », « Imports » et « Projets ». Voir la fiche Affectations des utilisateurs et des rôles.
7. **Appareils connectés :** « Paramètres » > « Appareils connectés » liste les appareils où le compte est connecté; « Bannir l'appareil » (avec un « Code de bannissement ») coupe l’accès d’un appareil.

Droits requis : `all.project.user.add` et `.delete` (ajouter, retirer), `all.project.user.agenda` (voir les agendas des autres), `all` pour les actions sensibles du profil (permissions, historique, duplication, blocage, connexion à la place). Après chaque action, le profil se met à jour.

Sources d’interface : `components/user/list/Layout.vue`, `components/user/profile/Profile.vue`, `components/user/add/`, `components/global/user/`, `components/user/session/`, `lang/fr.json` (`user.*`, `auth.impersonate.*`, `auth.session.*`).

## Questions fréquentes
**Comment ajouter un commercial à mon projet ?** « Paramètres » > « Liste des utilisateurs » > « Ajouter », saisissez son e-mail; s’il n’existe pas, « Créer l'utilisateur ».

**Le nouvel utilisateur n’a pas reçu son e-mail.** Une nouvelle invitation peut être envoyée tant que le compte n’est pas activé; le lien expire après 48 heures.

**Comment réinitialiser le mot de passe de quelqu’un ?** Sur son profil, icône clé : il reçoit un lien pour choisir un nouveau mot de passe.

**Comment débloquer un compte bloqué ?** Sur son profil, icône cadenas « Débloquer l'accès de cet utilisateur ».

**Comment voir ce que fait un utilisateur ?** Icônes « Historique de connexion » et « Actions faites par l'utilisateur ».
