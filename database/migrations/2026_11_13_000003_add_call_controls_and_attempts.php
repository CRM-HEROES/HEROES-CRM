<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->boolean('call_consent')->nullable()->default(null);
            $table->boolean('do_not_call')->nullable()->default(null);
            $table->string('call_timezone', 64)->nullable();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('call_agent_id')->nullable();
            $table->boolean('calls_enabled')->default(false);
            $table->unsignedBigInteger('call_back_label_id')->nullable();
            $table->unsignedBigInteger('call_transfer_user_id')->nullable();
        });

        Schema::create('call_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_run_id')->nullable()->constrained('agent_runs')->nullOnDelete();
            $table->string('provider', 30);
            $table->string('external_id')->nullable();
            $table->string('status', 20);
            $table->string('skip_reason')->nullable();
            $table->string('outcome', 30)->nullable();
            $table->text('summary')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['prospect_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_attempts');
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['call_agent_id', 'calls_enabled', 'call_back_label_id', 'call_transfer_user_id']);
        });
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn(['call_consent', 'do_not_call', 'call_timezone']);
        });
    }
};
