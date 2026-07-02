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
        /**
         * Label categories
         */
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('for', 20)->default('prospect')->index();
            $table->string('color', 7)->default('#333333');
            $table->string('bgcolor', 7)->default('#CCCCCC');
            $table->boolean('unique')->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('relevance')->default(0);

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');

            $table->string('name');
            $table->text('description')->nullable();

            $table->string('color', 7)->default('#333333');
            $table->string('bgcolor', 7)->default('#CCCCCC');

            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('prospects_count')->default(0);
            $table->unsignedInteger('relevance')->default(0);

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        /**
         * Associated user category
         * If user is not a project admin
         * he can use only these categories
         */
        Schema::create('user_category', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['user_id', 'category_id']);
        });

        /**
         * Associated user label
         * If user is not a project admin
         * he can use only these labels
         */
        Schema::create('user_label', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('label_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['user_id', 'label_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_label');
        Schema::dropIfExists('user_category');
        Schema::dropIfExists('labels');
        Schema::dropIfExists('categories');
    }
};
