<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kavkom_calls', function (Blueprint $table) {
            $table->id();
            $table->uuid('call_uuid')->unique();
            $table->foreignId('prospect_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('interaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('domain_uuid')->nullable()->index();
            $table->string('destination', 32)->nullable();
            $table->string('status', 32)->default('initiated')->index();
            $table->text('recording_url')->nullable();
            $table->json('webhook_payload')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kavkom_calls');
    }
};
