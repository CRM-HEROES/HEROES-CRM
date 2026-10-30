<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - campaign_order never had the action_id column that the campaign engine
 *   reads and writes (it only existed on campaign_prospect): order campaigns
 *   crashed on a schema built from the migrations. Guarded so that databases
 *   where the column was added by hand are left untouched.
 * - composite indexes for the "not yet processed by this campaign/action"
 *   anti-join.
 * - campaign_event: processing log of the appointment campaigns.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('campaign_order', 'action_id')) {
            Schema::table('campaign_order', function (Blueprint $table) {
                // Nullable: rows written before this fix have no action
                $table->unsignedBigInteger('action_id')->nullable()->after('order_id');
                $table->foreign('action_id')->references('id')->on('campaign_actions')->onDelete('cascade');
            });
        }

        Schema::table('campaign_prospect', function (Blueprint $table) {
            $table->index(['campaign_id', 'action_id', 'prospect_id'], 'campaign_prospect_processed_index');
        });

        Schema::table('campaign_order', function (Blueprint $table) {
            $table->index(['campaign_id', 'action_id', 'order_id'], 'campaign_order_processed_index');
        });

        if (!Schema::hasTable('campaign_event')) {
            Schema::create('campaign_event', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campaign_id');
                $table->unsignedBigInteger('event_id');
                $table->unsignedBigInteger('action_id');
                $table->boolean('processing')->default(false);
                $table->boolean('error')->default(false);
                $table->unsignedInteger('execution')->index();
                $table->timestamp('begin_at')->nullable();
                $table->timestamp('end_at')->nullable();

                $table->timestamps();

                // One row per campaign / action / appointment
                $table->unique(['campaign_id', 'action_id', 'event_id'], 'campaign_event_unique');

                $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->foreign('action_id')->references('id')->on('campaign_actions')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_event');

        // The composite indexes and campaign_order.action_id are intentionally kept:
        // MySQL uses the composite indexes for the campaign_id foreign keys (dropping
        // them would fail) and the engine needs action_id.
    }
};
