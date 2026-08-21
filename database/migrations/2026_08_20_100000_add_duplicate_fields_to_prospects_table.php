<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * duplicate_group_id says a prospect belongs to a duplicate cluster, but
 * not which field(s) caused it — needed to highlight only the offending
 * cell (e.g. Email) in the table instead of tinting the whole row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->json('duplicate_fields')->nullable()->after('duplicate_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn('duplicate_fields');
        });
    }
};
