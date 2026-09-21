<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inbound calls are recorded in kavkom_calls too (the CDR webhook receives
 * them like outbound ones). `direction` makes it possible to know which end
 * of the call `destination` holds — the caller for an inbound call, the
 * dialled number for an outbound one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kavkom_calls', function (Blueprint $table) {
            $table->string('direction', 8)->nullable()->index()->after('domain_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('kavkom_calls', function (Blueprint $table) {
            $table->dropColumn('direction');
        });
    }
};
