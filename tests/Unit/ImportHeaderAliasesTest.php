<?php

namespace Tests\Unit;

use App\Support\ImportHeaderAliases;
use PHPUnit\Framework\TestCase;

class ImportHeaderAliasesTest extends TestCase
{
    public function test_it_maps_the_import_header_variants_used_by_meta_ads(): void
    {
        $this->assertSame('email', ImportHeaderAliases::resolve('e-mail'));
        $this->assertSame('full_name', ImportHeaderAliases::resolve('nom_complet'));
        $this->assertSame('first_name', ImportHeaderAliases::resolve('prénom'));
        $this->assertSame('last_name', ImportHeaderAliases::resolve('nom'));
        $this->assertSame('mobile_phone_number', ImportHeaderAliases::resolve('numéro_de_téléphone'));
    }
}