<?php

namespace Tests\Unit;

use App\Jobs\Import\ImportColumnToField\DefaultField;
use Tests\TestCase;

class DefaultFieldDateTest extends TestCase
{
    public function test_it_converts_iso8601_timestamps_with_offsets_to_mysql_datetime(): void
    {
        $prospect = [];

        (new DefaultField())->handle(
            $prospect,
            'created_at',
            '2026-09-24T12:17:55-05:00'
        );

        $expected = now()
            ->parse('2026-09-24T12:17:55-05:00')
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d H:i:s');

        $this->assertSame($expected, $prospect['created_at']);
    }
}