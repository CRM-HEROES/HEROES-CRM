<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Instant Google Sheets sync.
     *
     * import_sync_rows is a durable inbox: the webhook stores every received
     * row there (one row per lead, unique per import) before acknowledging
     * the request, then a queued job turns the pending rows into prospects.
     * The unique (import_id, external_id) key is what makes the whole pipeline
     * idempotent: the same lead received twice, at the same time or after a
     * retry / restart, can only ever be stored, and therefore created, once.
     */
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            if (!Schema::hasColumn('imports', 'sync_enabled')) {
                $table->boolean('sync_enabled')->default(false)->after('source_url');
            }
            if (!Schema::hasColumn('imports', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('sync_enabled');
            }
        });

        if (!Schema::hasTable('import_sync_rows')) {
            Schema::create('import_sync_rows', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('import_id');
                // Stable identifier of the lead in the sheet (Meta lead id, or
                // a hash of the "MAJ" columns when the sheet has no id column).
                $table->string('external_id', 191);
                $table->string('sheet', 191)->nullable();
                $table->string('batch_id', 64)->nullable();
                // {"headers": [...], "values": [...]}; emptied once processed.
                $table->json('payload')->nullable();
                // pending -> processing -> created | skipped | failed
                $table->string('status', 16)->default('pending');
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->string('claim_token', 36)->nullable();
                $table->timestamp('claimed_at')->nullable();
                $table->unsignedBigInteger('prospect_id')->nullable();
                $table->string('reason', 64)->nullable();
                $table->text('error')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->unique(['import_id', 'external_id'], 'import_sync_rows_unique_lead');
                $table->index(['import_id', 'status', 'id'], 'import_sync_rows_queue');
                $table->index(['status', 'claimed_at'], 'import_sync_rows_stale');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('import_sync_rows');

        Schema::table('imports', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['sync_enabled', 'last_synced_at'],
                fn ($column) => Schema::hasColumn('imports', $column)
            ));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
