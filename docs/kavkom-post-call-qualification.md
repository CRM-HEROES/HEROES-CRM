# Qualification automatique des appels Kavkom

Le CRM traite les appels terminés en arrière-plan : CDR Kavkom, téléchargement temporaire de l'enregistrement, Whisper, analyse Claude, mise à jour non destructive de la fiche prospect et historique du score.

1. Définir `OPENAI_API_KEY`, `ANTHROPIC_API_KEY` et un secret aléatoire `KAVKOM_WEBHOOK_SECRET` dans `.env`, puis exécuter `php artisan migrate`.
2. Dans **Kavkom → Advanced → Integrations → Webhook**, activer le type d'appel **CDR après fin d'appel** vers `https://<domaine-crm>/api/webhooks/kavkom/cdr`.
3. Ajouter le header personnalisé `X-Kavkom-Webhook-Secret: <KAVKOM_WEBHOOK_SECRET>` et mapper au minimum `callUuid`, le numéro appelé et l'URL HTTPS de l'enregistrement. Si Kavkom ne fournit qu'un chemin d'enregistrement, définir également `KAVKOM_RECORDING_BASE_URL`.
4. Faire tourner un worker Laravel (la queue par défaut doit être incluse) : `php artisan queue:work --tries=5`.

Kavkom documente le webhook CDR et le `callUuid`, ainsi que l'accès programmatique aux enregistrements, mais pas un endpoint public de téléchargement par `call_uuid`. Le mapping de l'URL/du chemin d'enregistrement dans le CDR est donc nécessaire. Les erreurs sont retriées puis visibles dans `storage/logs/kavkom.log` et dans `kavkom_calls`.
