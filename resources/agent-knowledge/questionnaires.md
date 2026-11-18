# Questionnaires

## À quoi ça sert
Un questionnaire est un formulaire structuré (sections, questions, options de réponse) que l’on remplit sur la fiche d’un prospect, par exemple pour qualifier un besoin.

## Comment faire
Ouvrez la fiche du prospect, puis « Questionnaire ». Le panneau liste les questionnaires disponibles pour l’utilisateur et permet d’en créer ou d’en modifier selon les droits.

## Règles
Un questionnaire se compose de sections; une section contient des questions; une question à choix a des options. Les types de question sont « Choix unique », « Choix multiple », « Champ de texte », « Champ de texte multiligne », « Date », « Image » et « Fichier ». Les questionnaires accessibles à un utilisateur ou à un rôle se règlent dans les affectations de l’utilisateur ou du rôle (panneaux « questionnaires » des utilisateurs et des rôles).

## Limites
Les droits de création et de modification sont `all.project.questionnaire.add` et `all.project.questionnaire.update`; le serveur ne vérifie pas d’autre droit dans le code consulté. Cette fiche ne décrit pas l’exploitation statistique des réponses.

## Dans l'interface
Chemin : « Prospects » > fiche du prospect > « Questionnaire » (`components/prospect/questionnaire/`).

1. Pour créer un questionnaire, utilisez le bouton « Nouveau questionaire » (le libellé de l’interface a cette orthographe) du panneau; il demande `all.project.questionnaire.add`.
2. Ajoutez une « Nouvelle section », puis une « Nouvelle question » dans la section en choisissant son type.
3. Pour une question à choix, ajoutez des options avec « Nouvelle option » en « Choisir une section » si besoin.
4. Pour modifier un questionnaire existant, utilisez son action de modification (droit `all.project.questionnaire.update`).
5. Pour répondre, ouvrez le questionnaire sur la fiche du prospect et remplissez ses questions.

Droits requis : voir la rubrique Limites. Après enregistrement, les réponses sont attachées à la fiche du prospect.

Sources d’interface : `components/prospect/questionnaire/`, `components/questionnaire/`, `components/layouts/Main.vue`, `lang/fr.json` (`questionnaire.*`).

## Questions fréquentes
**Où remplir un questionnaire ?** Fiche du prospect > « Questionnaire ».

**Quels types de questions existe-t-il ?** « Choix unique », « Choix multiple », « Champ de texte », « Champ de texte multiligne », « Date », « Image » et « Fichier ».

**Pourquoi ne vois-je pas de bouton de création ?** Il faut le droit `all.project.questionnaire.add`.
