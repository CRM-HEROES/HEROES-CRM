<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agenda queries: "calendar_id IN (project calendars) AND started_at range"
     * and "user_id = ? AND started_at range", both ignoring soft-deleted rows.
     * The leading columns still satisfy the calendar_id / user_id foreign keys.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index(['calendar_id', 'deleted_at', 'started_at'], 'events_calendar_deleted_started_index');
            $table->index(['user_id', 'deleted_at', 'started_at'], 'events_user_deleted_started_index');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_calendar_deleted_started_index');
            $table->dropIndex('events_user_deleted_started_index');
        });
    }
};
