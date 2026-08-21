<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            // The source URL (e.g. Google Sheets share link), persisted so
            // it can be re-downloaded periodically instead of only being
            // used once at creation time.
            $table->string('url')->nullable()->after('path');
            // Whether this import should be periodically re-downloaded and
            // re-processed automatically (currently only meaningful for
            // source = google_sheets).
            $table->boolean('sync_enabled')->default(false)->after('selected_sheets');
            $table->unsignedInteger('sync_interval_minutes')->default(30)->after('sync_enabled');
            $table->timestamp('last_synced_at')->nullable()->after('sync_interval_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn(['url', 'sync_enabled', 'sync_interval_minutes', 'last_synced_at']);
        });
    }
};
