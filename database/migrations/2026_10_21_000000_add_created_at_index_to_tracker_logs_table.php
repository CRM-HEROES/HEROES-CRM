<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Covering index for the app:user-tracker aggregation
     * (created_at range, grouped by user_id and route_path_id).
     *
     * tracker_logs is large (millions of rows): on MySQL the index is built online
     * (no write lock), but it can still take several minutes, so run it off-peak.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE tracker_logs ADD INDEX tracker_logs_created_user_path_index ' .
                '(created_at, user_id, route_path_id), ALGORITHM=INPLACE, LOCK=NONE'
            );

            return;
        }

        Schema::table('tracker_logs', function (Blueprint $table) {
            $table->index(['created_at', 'user_id', 'route_path_id'], 'tracker_logs_created_user_path_index');
        });
    }

    public function down(): void
    {
        Schema::table('tracker_logs', function (Blueprint $table) {
            $table->dropIndex('tracker_logs_created_user_path_index');
        });
    }
};
