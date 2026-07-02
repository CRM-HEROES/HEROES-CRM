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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();

            $table->string('slug');
            $table->string('name', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('type', 20)->default("text");
            $table->string('for', 20)->default("prospect")->index();
            $table->boolean('searchable')->default(false);
            $table->boolean('meta')->default(false);
            $table->boolean('required')->default(false);
            $table->string('default_value')->nullable();
            $table->string('format', 100)->nullable();
            $table->boolean('unique')->default(false);
            $table->boolean('labeled')->default(false);
            $table->unsignedInteger('order')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('fields');
    }
};
