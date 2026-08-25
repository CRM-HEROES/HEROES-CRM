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
        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('reminder_j2_sent_at')->nullable()->after('started_at');
            $table->timestamp('reminder_j1_sent_at')->nullable()->after('reminder_j2_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['reminder_j2_sent_at', 'reminder_j1_sent_at']);
        });
    }
};
