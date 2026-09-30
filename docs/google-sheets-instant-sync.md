# Synchronisation instantanée Google Sheets → Heroes CRM

Les **nouveaux leads** ajoutés dans un Google Sheet sont envoyés directement au CRM, par lots, sans télécharger
ni relire la feuille. Les prospects déjà présents en base ne sont **jamais** modifiés ni supprimés.

## Principe

```
Google Sheet ── déclencheur onChange ──> Apps Script (généré par le CRM)
    │  lit la feuille 1 fois, garde les lignes dont la clé n'a pas encore été acquittée,
    │  les envoie par lots de 200 (requêtes en parallèle), signe chaque corps (HMAC-SHA256)
    ▼
POST /api/webservice/{import}/sheet-sync          SheetSyncController
    │  vérifie la signature, écrit les lignes dans import_sync_rows (clé unique),
    │  répond 202 (~30 ms pour 200 lignes), puis met le traitement en file
    ▼
File « sheet-sync » (Redis en production)          ProcessSheetSyncRows → SheetSyncProcessor
    │  réserve les lignes (UPDATE atomique), verrou MySQL de projet, une transaction
    ▼
prospects + relations + affectation automatique     ImportProspects::syncRows()
```

### Colonnes « MAJ »
Les colonnes cochées dans l'étape **MAJ** de l'import définissent l'identité d'un lead :
- **Clé de la ligne** dans la feuille : la colonne `id` si elle existe (Meta : `l:123…`), sinon les valeurs des colonnes MAJ.
- **Détection « le lead existe déjà dans le CRM »** : comparaison sur ces mêmes colonnes MAJ (à défaut, e-mail / téléphones),
  avec la même normalisation que l'import classique (téléphone `+33…`, e-mail en minuscules).
- Un lead **nouveau** est créé avec **toutes** les colonnes mappées (nom, prénom, téléphone, champs personnalisés, dates…).

### Garanties
| Exigence | Mécanisme |
|---|---|
| Aucun doublon, même webhook reçu plusieurs fois en même temps | Clé unique MySQL `(import_id, external_id)` : la ligne est stockée **avant** l'accusé de réception, un second envoi est refusé par la base elle-même |
| Requêtes simultanées / relances / redémarrage | Les lignes sont durables en MySQL ; réservation atomique ; état `processing` récupéré après 5 min |
| Deux leads différents de même e-mail/téléphone en parallèle | Verrou MySQL `GET_LOCK` de projet (le même que l'import classique), tenu pendant toute la transaction (vérification + insertion + statuts) |
| Aucune perte si la file est indisponible | Le webhook répond 202 quand les lignes sont en base ; `sheet-sync:recover` (chaque minute) relance le traitement |
| Plantage en plein traitement | Insertion des prospects et changement de statut dans **une seule transaction** : tout ou rien, puis nouvelle tentative (5 essais, attente 10 s → 5 min) |
| Données existantes | Aucun `UPDATE`/`DELETE` sur `prospects` : un lead existant est marqué `skipped / already_exists` |
| Affectation automatique | Même `ProspectAutoAssignment` (rôles, utilisateurs, groupes, indicatifs `user_settings`), limité aux prospects qui viennent d'être créés |
| Données personnelles | Le contenu de la ligne est effacé de `import_sync_rows` dès qu'elle est traitée |

Le verrou MySQL ne dépend pas de Redis : Redis n'accélère que la file d'attente.

## Limites techniques (à lire)

**Détection instantanée selon la source des lignes**
- **Saisie manuelle** dans l'interface : détectée immédiatement (`onChange`, type EDIT).
- **Connecteurs qui écrivent via l'API Google Sheets** (Zapier, Make, intégration Meta/Google…) : `onEdit` **ne se déclenche pas**
  pour ces écritures ; c'est pour cela que le script utilise le déclencheur installable **`onChange`**, qui est bien celui
  émis pour les changements faits par l'API. Google ne publie toutefois **aucune garantie de délai** : en pratique de l'ordre
  de quelques secondes, parfois davantage, et une rafale peut être **fusionnée en un seul événement** (le script lit donc
  la feuille, pas l'événement, pour ne rien perdre).
- **Écritures faites par un autre Apps Script** : aucun déclencheur n'est émis par Google pour elles.
- **Non vérifié ici** : je n'ai pas de compte Google à disposition pour mesurer ces délais en réel. Le script est testé contre
  des simulations de `SpreadsheetApp`, `UrlFetchApp`, `LockService` et de la signature, avec les vraies lignes du classeur de test,
  et la signature qu'il produit est acceptée par le webhook. Le délai réel `onChange` est à valider sur votre classeur (voir ci-dessous).
- **Filet de sécurité** : un déclencheur quotidien (chaque nuit à minuit, `safetyNetHour` dans le script, `null` pour le désactiver ;
  Google choisit la minute exacte dans l'heure 00 h–01 h) renvoie les lignes restées sans accusé de réception. Ce n'est pas le mécanisme principal.
- **Meilleure option pour Meta Lead Ads** si le délai `onChange` ne convient pas : le webhook Meta vers le CRM, sans passer par la feuille
  (même contrat : une clé stable par lead). Non implémenté ici.

**Règles de fonctionnement**
- Seuls les **nouveaux** leads sont synchronisés ; une modification faite ensuite dans la feuille **n'est jamais appliquée** au CRM (choix voulu).
- Une ligne saisie à la main **sans colonne `id`** est envoyée dès que ses colonnes MAJ sont renseignées ; si l'e-mail est complété plus tard, le lead
  existe déjà et n'est pas modifié. Corriger l'e-mail d'une ligne sans `id` peut être vu comme un nouveau lead s'il n'existe pas dans le CRM.
- Quotas Google Apps Script (valeurs publiées par Google, à vérifier, compte gratuit) : ~20 000 appels `UrlFetch`/jour, ~90 min de temps d'exécution
  des déclencheurs/jour, 6 min par exécution, 30 exécutions simultanées. Un lot de 200 lignes = 1 appel : 5 000 leads = 25 appels.

## Résultats de test (base MySQL/MariaDB jetable, vrais processus en parallèle)

| Scénario | Résultat |
|---|---|
| 100 / 1 000 / 10 000 leads, lots de 200, 4 workers | 100 %, 100 %, 100 % créés, 0 doublon — 10 000 leads traités en **13 s** |
| Accusé de réception du webhook | **≈ 30 ms** par lot de 200 lignes (médiane), p95 ≈ 33 ms |
| Même lot envoyé par **8 processus au même instant** | exactement 200 lignes stockées, 200 prospects, 0 doublon (6 workers en parallèle) |
| Lots qui se chevauchent + workers actifs en même temps | 500 leads distincts → 500 prospects, 0 doublon |
| 200 prospects existants + 400 leads dans la feuille | les 200 existants **identiques octet pour octet** (somme de contrôle), 200 créés, 200 ignorés |
| Lead existant modifié dans la feuille puis renvoyé | rien créé, rien modifié |
| Plantage juste avant le commit | 0 prospect, lignes de nouveau en attente ; reprise sans doublon |
| Worker tué après réservation (claims périmés) | récupérés par `sheet-sync:recover`, 150/150 créés, 0 doublon |
| File d'attente injoignable | webhook répond 202, lignes sauvegardées, traitées ensuite par `sheet-sync:recover` |
| Signature invalide / corps falsifié / import non activé / >500 lignes / clé vide | 403 / 403 / 409 / 413 / 422 |
| Import classique fichier (CSV, XLSX) | inchangé (noms, téléphones `+33`, doublons, dates) |

Tests automatisés : `tests/Feature/SheetSyncTest.php` (9 tests, transactions annulées).

## Déploiement

1. `php artisan migrate` — ajoute `imports.sync_enabled`, `imports.last_synced_at` et la table `import_sync_rows`.
2. `npm run build`.
3. **Workers** : la file `sheet-sync` est ajoutée en tête de `supervisor/worker.conf`. Redémarrer les workers
   (`supervisorctl restart all` ou redéploiement du conteneur). En local, relancer `queue:work` en ajoutant `sheet-sync` à `--queue=`
   (ou exécuter `php artisan sheet-sync:recover --sync`).
4. **Planificateur** : `php artisan schedule:run` doit tourner chaque minute (déjà nécessaire aux autres tâches) : il exécute `sheet-sync:recover`.
5. En production : `QUEUE_CONNECTION=redis` et `APP_URL` correct (le script utilise l'hôte de la requête, `APP_URL` sert de repli).
6. Dans le CRM : import Google Sheets → cocher **Synchronisation instantanée** → onglet Import → **Copier le script**.
7. Dans Google Sheets : *Extensions ▸ Apps Script*, coller, exécuter **`setup`** une fois et autoriser. Les lignes déjà présentes sont
   considérées comme déjà importées (l'import classique les a créées) ; `backfill` renvoie tout si besoin (le CRM ignore ce qu'il possède).
8. Validation : ajouter une ligne et mesurer le délai réel d'apparition du prospect dans le CRM. Le détail de chaque ligne reçue (créée, ignorée, en échec) est dans la table `import_sync_rows`.

## Exploitation

- `php artisan sheet-sync:recover` — remet en attente les réservations périmées et relance le traitement.
  `--retry-failed` réessaie les lignes en échec définitif ; `--sync` traite immédiatement sans passer par la file.
- Table `import_sync_rows` : `status` = `pending` · `processing` · `created` · `skipped` (`reason` : `already_exists`, `duplicate_in_batch`,
  `no_contact_info`, `suspicious_row`) · `failed` (`error`).
- Désactiver : décocher la case (le webhook répond 409) et supprimer les déclencheurs du script.

## Fichiers

- Webhook : `app/Http/Controllers/API/SheetSyncController.php` — route `POST /api/webservice/{import}/sheet-sync`
- Traitement : `app/Services/Import/SheetSyncProcessor.php`, `app/Jobs/ProcessSheetSyncRows.php`, `ImportProspects::syncRows()`
- Reprise : `app/Console/Commands/SheetSyncRecover.php` (planifiée chaque minute dans `app/Console/Kernel.php`)
- Script Apps Script : `app/Services/Import/SheetSyncScript.php` (API `GET /import/{import}/sync-script`)
- Base : `database/migrations/2026_10_01_000000_add_google_sheets_instant_sync.php`
