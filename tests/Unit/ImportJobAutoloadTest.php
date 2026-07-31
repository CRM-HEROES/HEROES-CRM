<?php

namespace Tests\Unit;

use Tests\TestCase;

class ImportJobAutoloadTest extends TestCase
{
    public function test_import_jobs_load_the_welcome_sms_trait_on_case_sensitive_filesystems(): void
    {
        $this->assertTrue(class_exists(\App\Jobs\ImportProspects::class));
        $this->assertTrue(class_exists(\App\Jobs\ImportHandleDuplicatedProspects::class));
    }
}
