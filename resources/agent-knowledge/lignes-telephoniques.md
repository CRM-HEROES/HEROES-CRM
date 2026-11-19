# Lignes téléphoniques

## À quoi ça sert
Une ligne relie un utilisateur à un opérateur de téléphonie du projet. Elle sert à appeler un prospect, à envoyer des SMS par CloudTalk et à recevoir leurs retours dans l’historique du prospect.

## Comment faire
Dans la fiche du projet, ouvrez le bloc « Lignes téléphoniques » puis ajoutez une ligne : choisissez l’opérateur, remplissez sa configuration, puis assignez-la à un utilisateur du projet.

## Règles
Opérateurs de ligne et informations demandées :
- **CloudTalk** : identifiant et secret de clé API, et l’agent CloudTalk de la ligne.
- **Kavkom** : jeton d’API, identifiant de domaine, numéro de téléphone et extension. Un outil de diagnostic Kavkom aide à contrôler la configuration.
- **Ringover** : jeton d’API.
- **Twilio** : identifiant de compte, jeton d’authentification, clé d’API (identifiant et secret), application TwiML et numéro d’appelant.

Une ligne est liée à un utilisateur : seul son propriétaire l’utilise pour appeler. Un agent IA appelle avec la ligne CloudTalk de son propriétaire, qui doit avoir un agent CloudTalk choisi.

## Limites
Les droits d’ajout, de modification et de suppression sont `all.project.line.add`, `.update` et `.delete`. Les identifiants ne sont jamais renvoyés par les listes d’opérateurs destinées au navigateur. Les panneaux d’appel proposent aussi Aircall et le téléphone de l’utilisateur selon la configuration; ils n’ont pas de ligne propre dans ce bloc.

## Dans l'interface
Chemin : « Accueil » > le projet > bloc « Lignes téléphoniques » (`components/project/profile/blocs/line/`).

1. Cliquez l’icône d’ajout du bloc, titre « Ajouter une ligne ».
2. « Choisir un opérateur », puis remplissez « Configuration de la ligne ».
3. « Assigner à {user} » : « Choisir un agent » désigne l’agent CloudTalk correspondant quand l’opérateur est CloudTalk.
4. Pour appeler : fiche du prospect > « Appel », puis « Appeler via CloudTalk » (ou Ringover, Aircall, téléphone).

Droits requis : voir la rubrique Limites. Après l’ajout, la ligne apparaît dans le bloc et ses appels sont consignés dans l’historique du prospect.

Sources d’interface : `components/project/profile/blocs/line/`, `components/line/`, `components/prospect/interaction/Slide.vue`, `lang/fr.json` (`line.*`).

## Questions fréquentes
**Comment configurer CloudTalk ?** Bloc « Lignes téléphoniques » > ajout > opérateur CloudTalk, clé d’API et agent CloudTalk, puis assignez la ligne à l’utilisateur.

**Pourquoi l’agent IA ne peut-il pas appeler ?** Son propriétaire n’a pas de ligne CloudTalk dans le projet, ou aucun agent CloudTalk n’est choisi sur cette ligne.

**Qui peut ajouter une ligne ?** Un utilisateur qui a `all.project.line.add`.
