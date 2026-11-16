# Agents IA

## À quoi ça sert
Les agents IA discutent avec l’utilisateur pour l’aider dans le projet. Selon les outils et droits disponibles, ils peuvent proposer des actions et documents.

## Comment faire
Ouvrez la page Claude depuis l’icône « Claude » de la barre supérieure. La page permet de choisir un agent, échanger, créer une conversation et consulter les actions en attente.

## Règles
Les actions modifiant des données ou envoyant des communications peuvent demander une validation humaine. Les agents accessibles dépendent du projet et de la configuration.

## Limites
La disponibilité d’un modèle et des outils dépend de la configuration, des habilitations et de la clé API; À vérifier si l’agent ne démarre pas.

## Dans l'interface
Chemin : dans un projet, barre du haut > icône « Claude » (`Header.vue`, route `agent`). Le profil projet comporte également le bloc « Agents IA » et l’action « Nouvel agent ».

1. Ouvrez « Claude » puis choisissez un agent dans la barre d’agents.
2. Pour créer un agent depuis le projet, ouvrez le bloc « Agents IA » et l’icône « Nouvel agent »; complétez les étapes de formulaire puis utilisez « Suivant » et « Créer l'agent ».
3. Pour discuter, saisissez une demande dans « Écrivez à votre agent… » puis « Envoyer ».
4. Utilisez les onglets « Discussion », « Validations » ou « Mes agents » pour consulter les espaces correspondants. Une action proposée peut être « Confirmer » ou « Annuler »; vérifiez son aperçu avant décision.

Droits requis : le bloc est disponible depuis un projet accessible. Le contrôle exact pour créer ou modifier des agents n’est pas affiché dans les composants consultés : À vérifier. Les validations ne signifient pas que l’action a déjà été exécutée. Après envoi, la réponse apparaît dans la conversation; un document généré peut être affiché dans le panneau de rapport.

Sources d’interface : `components/layouts/Header.vue`, `components/agent/chat/AgentPage.vue`, `components/agent/chat/ChatPanel.vue`, `components/agent/FormModal.vue`, `components/project/profile/blocs/agent/Bloc.vue`, `router/index.js`, `lang/fr.json` (`agent.*`).

## Questions fréquentes
**Comment démarrer une conversation ?** Choisissez un agent, écrivez dans « Écrivez à votre agent… », puis cliquez « Envoyer ».

**Comment créer un agent ?** Depuis le bloc « Agents IA », utilisez « Nouvel agent ».

**Une action proposée est-elle déjà faite ?** Non : si elle attend validation, examinez l’aperçu puis « Confirmer » ou « Annuler ».
