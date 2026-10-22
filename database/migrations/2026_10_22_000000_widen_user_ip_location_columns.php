<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * users.ip_city was too short in some environments (SQLSTATE 22001 "Data too long").
     * Columns are only widened, never shrunk.
     */
    protected array $columns = [
        'ip_country' => 191,
        'ip_state' => 191,
        'ip_city' => 191,
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->columns as $column => $length) {
            $current = DB::table('information_schema.columns')
                ->where('table_schema', DB::raw('DATABASE()'))
                ->where('table_name', 'users')
                ->where('column_name', $column)
                ->value('character_maximum_length');

            if ($current !== null && (int) $current < $length) {
                DB::statement("ALTER TABLE users MODIFY {$column} VARCHAR({$length}) NULL");
            }
        }
    }

    public function down(): void
    {
        // Not reversible on purpose: shrinking could truncate or reject existing data.
    }
};
