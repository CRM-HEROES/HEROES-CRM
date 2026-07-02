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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->boolean('private')->default(1); // 0 = the user/prospect has access to his own filed
            $table->string('color', 7)->default('#333333');
            $table->string('bgcolor', 7)->default('#CCCCCC');
            $table->string('for', 20)->default('prospect')->index();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('relevance')->default(0);
            $table->unsignedBigInteger('folder_id')->nullable();
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('set null');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id');
            $table->unsignedBigInteger('prospect_id');
            $table->string('name');
            $table->string('path')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->string('source')->default('local');
            $table->boolean('from_user')->default(1);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('user_folder', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('folder_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['user_id', 'folder_id']);
        });
        
        Schema::create('user_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('path')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->string('source')->default('local');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_files');
        Schema::dropIfExists('user_folder');
        Schema::dropIfExists('files');
        Schema::dropIfExists('folders');
    }
};
