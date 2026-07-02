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
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#333333');
            $table->string('bgcolor', 7)->default('#CCCCCC');
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('relevance')->default(0);

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('pipeline_label', function (Blueprint $table) {
            $table->unsignedBigInteger('pipeline_id');
            $table->unsignedBigInteger('label_id');
            $table->unsignedInteger('order')->default(0);

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('pipeline_id')->references('id')->on('pipelines')->onDelete('cascade');
            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['pipeline_id', 'label_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipeline_label');
        Schema::dropIfExists('pipelines');
    }
};
