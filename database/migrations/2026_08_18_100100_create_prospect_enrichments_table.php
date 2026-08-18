<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_enrichments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->cascadeOnDelete();
            $table->string('status', 16)->default('pending')->index();

            $table->boolean('email_verified')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('phone_verified')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('linkedin_url')->nullable();

            $table->json('dropcontact_data')->nullable();
            $table->json('proxycurl_data')->nullable();

            $table->unsignedTinyInteger('score')->nullable();
            $table->decimal('percentile', 5, 2)->nullable();
            $table->boolean('is_top_20')->default(false);

            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['prospect_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_enrichments');
    }
};
