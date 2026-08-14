# Script pour la vidéo de démonstration — Devis pré-rempli assisté IA (P4)

Un texte à lire (ou paraphraser librement) pendant que tu enregistres ton
écran. Prévois environ 4-5 minutes.

---

## 1. Introduction (30 secondes, pas besoin de montrer l'écran)

> "Je vais te montrer la fonctionnalité P4 : le devis pré-rempli assisté
> par IA. Le principe : quand un prospect passe à l'étape 'Bilan
> réalisé' dans le pipeline, l'IA rédige automatiquement un devis
> personnalisé et le dépose en PDF dans son dossier — sans jamais
> l'envoyer toute seule. C'est toujours un humain qui décide d'envoyer
> ou non."

## 2. Préparation avant la démo (à faire une fois, avant d'enregistrer)

**Ne montre pas cette partie dans la vidéo**, fais-la avant :
1. Vérifie que le label **"Bilan réalisé"** existe dans le pipeline du
   projet de démo (sinon, crée-le une fois : Paramètres du projet →
   Pipelines → ajouter le label).
2. Choisis un des prospects de test déjà créés (cherche
   `@test.heroes-crm.local` dans le CRM) — il a déjà des informations
   complètes (société, besoin, budget), pratique pour une démo propre.

## 3. Démo — déclenchement (1 minute)

**Montre à l'écran :** la fiche d'un prospect de test, avec ses
informations déjà visibles (nom, société, besoin).

> "Voici un prospect avec des informations déjà connues — nom,
> entreprise, besoin exprimé, budget. Je vais maintenant le faire passer
> à l'étape 'Bilan réalisé'."

**Action :** fais glisser le prospect vers le label "Bilan réalisé" dans
le pipeline (ou attache le label depuis sa fiche).

> "C'est fait. En arrière-plan, le CRM vient de déclencher la génération
> automatique du devis — je n'ai rien d'autre à faire."

## 4. Démo — résultat (2 minutes)

**Action :** attends quelques secondes, puis ouvre les documents/
dossiers du prospect.

> "Voilà : un nouveau PDF vient d'apparaître tout seul dans son
> dossier."

**Action :** ouvre le PDF généré.

> "Et le contenu est bien personnalisé — l'IA a repris les vraies
> informations du prospect : son nom, son besoin, le budget évoqué. Rien
> n'a été retapé à la main."

**Action :** montre qu'aucun email/SMS n'a été envoyé (ex. l'historique
d'interactions du prospect, ou juste le dire).

> "Point important : à aucun moment ce document n'a été envoyé au
> prospect. Il est juste prêt, en attente. Si on veut l'envoyer, il faut
> le faire nous-mêmes, volontairement — exactement comme demandé dans le
> cahier des charges : validation humaine obligatoire avant tout envoi."

## 5. Conclusion (30 secondes)

> "En résumé : dès qu'un prospect est marqué 'Bilan réalisé', son devis
> est prêt en quelques secondes, avec un contenu personnalisé et sans
> ressaisie — il ne reste plus qu'à le relire et l'envoyer quand on est
> prêt."

---

## Si le label "Bilan réalisé" n'est pas encore configuré le jour de l'enregistrement

Tu peux quand même montrer que ça fonctionne, sans passer par le
pipeline, directement en ligne de commande (moins visuel mais toujours
convaincant) :

```sh
php artisan tinker
```
```php
$prospect = App\Models\Prospect::withoutGlobalScopes()->find(ID_DU_PROSPECT);
$user = App\Models\User::first();
(new App\Jobs\GenerateAiQuoteDraft($prospect->id, $user->id))->handle(app(App\Services\Anthropic::class));
```
Puis va directement montrer le PDF apparu dans le dossier du prospect
dans le CRM.

## Conseils pour l'enregistrement

- Ferme les onglets/applications sensibles avant de démarrer.
- Utilise un des prospects de test (`@test.heroes-crm.local`), jamais une
  vraie fiche client.
- Teste une fois "à blanc" avant d'enregistrer pour de vrai, pour
  connaître le temps d'attente exact entre le déclenchement et
  l'apparition du PDF.
