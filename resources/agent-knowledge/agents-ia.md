# Agents IA

## À quoi ça sert
Les agents IA discutent avec l’utilisateur pour l’aider dans le projet : consulter et analyser le CRM, proposer des modifications, des envois ou des appels, et produire des documents. Un agent connaît toutes les fonctionnalités du CRM, mais il ne peut faire que ce que les droits de son propriétaire permettent, et il ne voit jamais les données d’un autre projet.

## Comment faire
Ouvrez la page Claude depuis l’icône « Claude » de la barre supérieure. La page comporte trois colonnes : l’historique des conversations à gauche, la discussion au centre, les documents produits par l’agent à droite. Choisissez un agent dans la barre des agents du projet, écrivez votre demande, puis envoyez-la.

## Règles
Les modifications, envois, appels et suppressions passent par une demande d’autorisation affichée dans la conversation : l’agent n’agit qu’après votre accord, sauf les modifications simples d’un agent « Autonome ». Les envois, les appels et les suppressions sont toujours soumis à validation, quel que soit le niveau d’autonomie. Une demande non traitée expire.

## Limites
L’agent ne répond que si la clé API Claude de l’utilisateur est enregistrée et si le traitement de la file « agents » tourne sur le serveur (`php artisan queue:work --queue=agents`). Sinon la conversation affiche « L’agent est en attente d’un worker… » puis « L’agent n’a pas démarré ». Un agent est lié au projet dans lequel il a été créé.

## Dans l'interface
Chemin : dans un projet, barre du haut > icône « Claude » (`Header.vue`, route `agent`). Le profil du projet comporte aussi le bloc « Agents IA » avec l’icône « Nouvel agent ».

1. Ouvrez « Claude » puis choisissez un agent dans la barre « Agents IA du projet ». Le bouton « i » ouvre un panneau « Informations sur l’agent » (identité, mission, instructions, projet, autonomie) avec le bouton « Modifier l'agent ».
2. Pour créer un agent, utilisez l’icône « Nouvel agent » du bloc « Agents IA ». Le formulaire a trois étapes : « Identité » (nom, spécialité, photo), « Mission » (description, personnalité, objectifs, instructions, documents de référence), « Autonomie » (« Lecture seule », « Propose » ou « Autonome »). Boutons « Suivant », « Retour » puis « Créer l'agent ».
3. Pour discuter, écrivez dans « Écrivez à votre agent… » puis « Envoyer » (Entrée envoie, Maj + Entrée passe à la ligne). Le bouton « Ajouter des pièces jointes » joint des fichiers, « Arrêter » interrompt un traitement en cours. « Nouvelle conversation » ouvre une discussion vierge; l’historique permet de rouvrir ou de supprimer une conversation.
4. Quand l’agent doit agir, une carte « demande votre autorisation », précédée du nom de l’agent, apparaît avec un aperçu (nombre de prospects, détails, exemples). Utilisez « Autoriser » ou « Refuser » (« Autoriser la suppression » pour une suppression). La carte indique ensuite « Autorisée et exécutée », « Refusée », « Échec de l’exécution » ou « Expirée ».
5. Les documents et rapports produits s’affichent dans la colonne « Rendus de l’agent » (boutons « Ouvrir », « Copier », « Télécharger (.md) »).

Droits requis : l’entrée « Claude » de la barre du haut demande `all.project.agent.view`. Le serveur ne contrôle aucun autre droit CRM pour créer un agent : chaque utilisateur gère ses propres agents. En revanche, l’agent ne peut jamais dépasser les droits de son propriétaire dans le projet. Une action autorisée n’est exécutée qu’après la décision; tant que la carte attend, rien n’a été modifié.

Sources d’interface : `components/layouts/Header.vue`, `components/agent/chat/AgentPage.vue`, `components/agent/chat/ChatPanel.vue`, `components/agent/chat/AgentInfo.vue`, `components/agent/ActionCard.vue`, `components/agent/FormModal.vue`, `components/project/profile/blocs/agent/Bloc.vue`, `router/index.js`, `lang/fr.json` (`agent.*`).

## Questions fréquentes
**Comment démarrer une conversation ?** Choisissez un agent, écrivez dans « Écrivez à votre agent… », puis cliquez « Envoyer ».

**Comment créer un agent ?** Depuis le bloc « Agents IA » du profil du projet, utilisez « Nouvel agent ».

**Une action proposée est-elle déjà faite ?** Non : tant que la carte d’autorisation est en attente, rien n’a été modifié. Utilisez « Autoriser » ou « Refuser ».

**L’agent reste sur « L’agent travaille… » ?** Le traitement de la file « agents » ne tourne probablement pas sur le serveur.

## Pour aller plus loin
La clé Claude d’un utilisateur s’enregistre dans « Paramètres » : sujet `google-pappers-claude`. Droit d’accès à la page : sujet `droits-permissions`.
