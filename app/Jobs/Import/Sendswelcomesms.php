<?php

namespace App\Jobs\Import;

use App\Models\Import;

trait SendsWelcomeSms
{
    /**
     * Send the welcome SMS to every prospect still
     * attached to the given import, once import
     * processing (and duplicate check) is done.
     *
     * Guarded by `welcome_sms_sent_at` so it is only
     * ever sent once per import, even if this is called
     * from more than one place (eg. once at the end of
     * ImportProspects, and again if the user later runs
     * an additional manual duplicate resolution pass).
     */
    protected function sendWelcomeSms(Import $import)
    {
        if (!$import->notify_welcome_sms) {
            return;
        }

        if (empty($import->welcome_sms_message)) {
            return;
        }

        // Reload to avoid acting on a stale
        // in-memory copy of the import
        if ($import->refresh()->welcome_sms_sent_at) {
            return;
        }

        $prospects = $import->prospects()->get();

        foreach ($prospects as $prospect) {
            if (empty($prospect->mobile_phone_number)) {
                continue;
            }

            try {
                // Same pattern as SmsController::store()/bulk():
                // creating the Sms record is what triggers the
                // actual sending (see App\Observers\SmsObserver)
                // and also acts as the tracking entry visible on
                // the prospect's SMS thread ("Message bienvenue
                // envoyé").
                $prospect->sms()->create([
                    'message' => $import->welcome_sms_message,
                    'source' => $import->welcome_sms_source ?: 'brevo',
                    'from_user' => 1,
                    'creator_id' => $import->creator_id,
                ]);
            } catch (\Throwable $e) {
                // Do not let a single SMS failure (eg. invalid
                // number, Brevo API error) block the rest of the batch.
                report($e);
            }
        }

        $import->update([
            'welcome_sms_sent_at' => now(),
        ]);
    }
}