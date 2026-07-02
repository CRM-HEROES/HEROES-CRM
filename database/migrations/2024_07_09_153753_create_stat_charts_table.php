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
        Schema::create('stat_charts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('name');
            $table->text('description');
            $table->string('type', 50);
            $table->string('chart_type', 50)->nullable();
            $table->json('labels')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('data_at')->nullable();
            $table->json('options')->nullable();
            $table->unsignedInteger('order')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stat_charts');
    }
};
