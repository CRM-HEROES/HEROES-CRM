<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('imports', 'source_url')) {
            Schema::table('imports', function (Blueprint $table) {
                $table->string('source_url')->nullable()->after('path');
            });
        }

        // Older deployments received the URL under the wrong column name.
        // Copy it once so existing Google Sheets imports keep working.
        if (Schema::hasColumn('imports', 'url')) {
            DB::table('imports')
                ->whereNull('source_url')
                ->whereNotNull('url')
                ->update(['source_url' => DB::raw('url')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('imports', 'source_url')) {
            Schema::table('imports', function (Blueprint $table) {
                $table->dropColumn('source_url');
            });
        }
    }
};
