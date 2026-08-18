<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Proxycurl (the original P6 brief's LinkedIn enrichment provider) was
 * sunset after a LinkedIn lawsuit; the same API key now points at
 * NinjaPear, which deliberately does not scrape LinkedIn/social profiles.
 * "linkedin_url" would be misleading going forward — it's whatever
 * external professional/social URL NinjaPear can find, which is rarely a
 * LinkedIn link at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE prospects RENAME COLUMN linkedin_url TO external_profile_url');
        DB::statement('ALTER TABLE prospect_enrichments RENAME COLUMN linkedin_url TO external_profile_url');
        DB::statement('ALTER TABLE prospect_enrichments RENAME COLUMN proxycurl_data TO ninjapear_data');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE prospects RENAME COLUMN external_profile_url TO linkedin_url');
        DB::statement('ALTER TABLE prospect_enrichments RENAME COLUMN external_profile_url TO linkedin_url');
        DB::statement('ALTER TABLE prospect_enrichments RENAME COLUMN ninjapear_data TO proxycurl_data');
    }
};
