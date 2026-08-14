<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_call_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kavkom_call_id')->unique()->constrained('kavkom_calls')->cascadeOnDelete();
            $table->foreignId('interaction_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('score_before')->default(0);
            $table->unsignedTinyInteger('score_after');
            $table->string('qualification', 16)->index();
            $table->unsignedTinyInteger('conversion_probability');
            $table->json('analysis');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_call_qualifications');
    }
};
