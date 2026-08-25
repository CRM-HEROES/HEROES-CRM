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

            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('action_id')->references('id')->on('campaign_actions')->onDelete('cascade');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['reminder_j2_sent_at', 'reminder_j1_sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('reminder_j2_sent_at')->nullable()->after('started_at');
            $table->timestamp('reminder_j1_sent_at')->nullable()->after('reminder_j2_sent_at');
        });

        Schema::dropIfExists('campaign_event');
    }
};
