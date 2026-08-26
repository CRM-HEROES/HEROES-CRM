<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The Import model already has an "url" accessor (the file download
     * link), so a raw "url" DB column silently gets shadowed by it
     * everywhere ($import->url, toArray(), toJson()) instead of ever being
     * read. Renamed to avoid the collision.
     */
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->renameColumn('url', 'source_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->renameColumn('source_url', 'url');
        });
    }
};
