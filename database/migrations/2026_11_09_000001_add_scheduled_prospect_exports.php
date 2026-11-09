<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exports', function (Blueprint $table) {
            $table->string('mode', 20)->nullable()->after('email');
            $table->string('sheet_name', 150)->nullable()->after('mode');
            $table->dateTime('scheduled_at')->nullable()->after('sheet_name');
            $table->dateTime('scheduled_claimed_at')->nullable()->after('scheduled_at');
            $table->dateTime('scheduled_dispatched_at')->nullable()->after('scheduled_claimed_at');
            $table->index(['scheduled_at', 'scheduled_dispatched_at'], 'exports_scheduled_queue_index');
        });
    }

    public function down(): void
    {
        Schema::table('exports', function (Blueprint $table) {
            $table->dropIndex('exports_scheduled_queue_index');
            $table->dropColumn([
                'mode',
                'sheet_name',
                'scheduled_at',
                'scheduled_claimed_at',
                'scheduled_dispatched_at',
            ]);
        });
    }
};
