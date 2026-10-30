<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Wait for an event" node.
 *
 * An execution parked on such a node has the awaited event in
 * waiting_event (and the timeout in resume_at). When the event happens for
 * its prospect it is woken up by clearing waiting_event; when the timeout
 * comes first, waiting_event is still set: that is how the node knows which
 * way (event / timeout) to go.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_executions', function (Blueprint $table) {
            $table->string('waiting_event', 40)->nullable()->after('locked_at');

            // "Who is waiting for this event for this prospect?"
            $table->index(['waiting_event', 'subject_type', 'subject_id'], 'workflow_executions_waiting_event_index');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_executions', function (Blueprint $table) {
            $table->dropIndex('workflow_executions_waiting_event_index');
            $table->dropColumn('waiting_event');
        });
    }
};
