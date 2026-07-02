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
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->boolean('from_user')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('source', 20)->nullable()->index();
            // initiated, ringing, answered
            $table->string('status', 20)->default("new")->index();
            $table->string('number', 20)->nullable();
            $table->string('from_number', 20)->nullable();
            $table->string('path')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->json('data')->nullable();
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
