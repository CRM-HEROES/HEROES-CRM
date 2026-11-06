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
        Schema::create('user_login_locations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('ip', 45);
        $table->string('country', 191)->nullable();
        $table->string('city', 191)->nullable();
        // known = pays habituel, pending = en attente de réponse,
        // confirmed = "c'était moi", denied = "ce n'était pas moi"
        $table->string('status', 20)->default('known');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_login_locations');
    }
};
