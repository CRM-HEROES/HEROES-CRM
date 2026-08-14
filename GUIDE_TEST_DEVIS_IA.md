# Guide de test — Devis pré-rempli assisté IA (P4)

## Ce que fait la fonctionnalité

Quand un prospect passe à l'étape "Bilan réalisé" dans le pipeline, l'IA
(Claude) rédige automatiquement un devis/proposition personnalisé et le
dépose en PDF dans le dossier documents du prospect — **sans jamais
l'envoyer automatiquement**. C'est toujours un humain qui décide
d'envoyer ce document au prospect, à la main, comme n'importe quel autre
document du CRM.

## Prérequis avant de tester

1. **`ANTHROPIC_API_KEY` renseignée** dans le `.env` (voir étapes
   d'obtention données séparément).
2. **Le label "Bilan réalisé" doit exister** dans le pipeline du projet
   de test — il n'existe pas encore par défaut, il faut le créer une
   fois (comme n'importe quel label du CRM : Paramètres du projet →
   Pipelines/Catégories → ajouter un label nommé exactement
   `Bilan réalisé`, dans la catégorie de pipeline de ton choix).
3. **Un template de document "Devis IA"** doit exister pour le projet —
   un modèle minimal de test a déjà été créé automatiquement (nom exact
   `Devis IA`) pour valider que le mécanisme fonctionne ; à remplacer par
   un vrai modèle avec la mise en page définitive quand vous serez prêts
   (même écran que pour créer n'importe quel autre modèle de document du
   CRM).

## Étapes de test (via l'interface, comme un vrai utilisateur)

1. Ouvre un prospect de test dans le CRM (idéalement un prospect qui a
   déjà des informations renseignées : nom, société, éventuellement un
   besoin/budget déjà connu).
2. Fais-lui passer le label **"Bilan réalisé"** (glisser dans le
   pipeline, ou l'attacher depuis la fiche prospect selon l'endroit
   habituel).
3. Attends quelques secondes (génération IA + PDF).
4. Va dans les **documents/dossiers** de ce prospect — un nouveau
   fichier PDF doit apparaître, nommé du style
   `Devis IA - 20260814_143022`.
5. Ouvre le PDF : il doit contenir un texte rédigé qui reprend les
   informations connues du prospect (pas de texte inventé ou de champ
   vide bizarre).
6. Vérifie qu'**aucun email/SMS n'a été envoyé automatiquement** au
   prospect — le document doit juste être présent dans le dossier,
   en attente qu'un humain décide de l'envoyer ou non.

## Test rapide sans passer par l'interface (pour vérifier vite)

Si tu veux tester sans manipuler le pipeline dans l'UI, ça peut se faire
directement en ligne de commande :

```sh
php artisan tinker
```
```php
$prospect = App\Models\Prospect::withoutGlobalScopes()->find(ID_DU_PROSPECT);
$user = App\Models\User::first();
(new App\Jobs\GenerateAiQuoteDraft($prospect->id, $user->id))->handle(app(App\Services\Anthropic::class));
$prospect->refresh();
echo $prospect->files->last()->name;
```
Si ça affiche un nom de fichier, va vérifier dans le CRM que le PDF est
bien présent et lisible.

## En cas de problème

- Logs dédiés à cette fonctionnalité : `storage/logs/ai-quote.log`.
- Si rien ne se passe après avoir attaché le label : vérifie que le nom
  du label est **exactement** `Bilan réalisé` (sensible à la casse et
  aux accents) et que le template `Devis IA` existe bien pour ce projet.
- Si le PDF est généré mais vide/sans texte IA : vérifie que
  `ANTHROPIC_API_KEY` est bien renseignée et valide (un message d'erreur
  clair apparaît dans `storage/logs/ai-quote.log` si ce n'est pas le
  cas).
