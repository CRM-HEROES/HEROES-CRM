# Automatisation

## À quoi ça sert
Les workflows automatisent des traitements à partir de déclencheurs, conditions, actions et étapes d’attente. Le CRM expose un catalogue de construction ainsi que le suivi de leurs exécutions.

## Comment faire
Consulte `workflow-catalog` et `workflow-provider`, crée ou modifie un brouillon, valide son graphe, puis publie-le. L’API permet aussi d’activer, mettre en pause ou archiver un workflow, et de consulter ou annuler des exécutions.

## Règles
Le graphe est nettoyé et diagnostiqué par le serveur. Les opérateurs de communication sont fournis selon les capacités configurées du projet; leurs références publiques n’exposent pas leurs identifiants secrets.

## Limites
La publication et l’activation sont des actions distinctes. Les limites opérationnelles exactes et les délais de traitement sont À vérifier dans le catalogue et la configuration en vigueur.

## Dans l'interface
Chemin : barre du haut du projet > « Automatisations » ouvre `workflow` (`components/workflow/Workflow.vue`). L’écran affiche une notice à la place de l’outil sur mobile.

1. Pour créer une automatisation, saisissez son nom dans « Nom de l'automatisation », puis cliquez « Créer »; la création et les modèles sont visibles avec `all.project.workflow.add`.
2. Sélectionnez une automatisation dans la liste. Dans « Schéma », construisez le flux avec les nœuds disponibles puis utilisez « Valider » pour vérifier les problèmes.
3. « Publier » enregistre une version sans nécessairement la démarrer; « Activer » démarre ou relance l’automatisation. « Mettre en pause » arrête les nouveaux démarrages, et « Archiver » ou « Supprimer » sont d’autres actions du menu « ⋯ ».
4. Ouvrez « Suivi » pour consulter les exécutions, lorsque `all.project.workflow.execution.view` l’autorise.

Droits requis : `all.project.workflow.add` pour créer, `all.project.workflow.update` pour modifier/publier, `all.project.workflow.execute` pour activer, mettre en pause ou archiver, `all.project.workflow.delete` pour supprimer, et `all.project.workflow.execution.view` pour le suivi. Après validation, le schéma affiche ses erreurs ou son état valide; les exécutions sont consultables dans « Suivi ».

Sources d’interface : `components/layouts/Header.vue`, `components/workflow/Workflow.vue`, `components/workflow/WorkflowBuilder.vue`, `components/workflow/WorkflowMonitor.vue`, `router/index.js`, `lang/fr.json` (`header.workflows`, `workflow.*`).

## Questions fréquentes
**Comment créer un brouillon ?** Saisissez un nom dans « Nom de l'automatisation », puis cliquez « Créer ».

**« Publier » démarre-t-il le workflow ?** Non, la publication et l’activation sont distinctes; « Activer » démarre l’automatisation.

**Pourquoi ne vois-je pas « Suivi » ?** Il faut le droit `all.project.workflow.execution.view`.
