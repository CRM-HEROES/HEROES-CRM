<?php

namespace Tests\Unit;

use App\Jobs\Import\ImportColumnToField\DefaultField;
use Tests\TestCase;

class MetaLeadAdsImportMappingTest extends TestCase
{
    public function test_it_splits_a_meta_full_name_and_normalizes_contact_values(): void
    {
        $field = new DefaultField();
        $prospect = [];

        $field->handle($prospect, 'full_name', 'Jacky Pautasso');
        $field->handle($prospect, 'mobile_phone_number', 'p:+33602285896');
        $field->handle($prospect, 'created_at', '2026-07-30T02:10:07-05:00');

        $this->assertSame('Jacky', $prospect['first_name']);
        $this->assertSame('Pautasso', $prospect['last_name']);
        $this->assertSame('+33602285896', $prospect['mobile_phone_number']);
        $this->assertSame('2026-07-30 07:10:07.000', $prospect['created_at']);
    }

    public function test_it_preserves_uppercase_compound_last_names(): void
    {
        $field = new DefaultField();
        $prospect = [];

        $field->handle($prospect, 'full_name', 'DE LA CRUZ Juan');

        $this->assertSame('Juan', $prospect['first_name']);
        $this->assertSame('DE LA CRUZ', $prospect['last_name']);
    }
}
