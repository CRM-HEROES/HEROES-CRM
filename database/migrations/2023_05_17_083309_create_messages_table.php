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
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->boolean('private')->default(1);
            $table->string('send_to')->nullable();
            $table->string('color', 7)->default('#333333');
            $table->string('bgcolor', 7)->default('#CCCCCC');
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('relevance')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('thread_id');
            $table->unsignedBigInteger('prospect_id');
            $table->text('body')->nullable();
            $table->text('plain_text')->nullable();
            $table->text('error')->nullable();
            $table->boolean('sent')->default(0);
            $table->boolean('from_user')->default(1);
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('archived_by')->nullable(); // ID of the user who archived the message. Null if it's the prospect.
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('thread_id')->references('id')->on('threads')->onDelete('cascade');
            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->string('name');
            $table->string('path')->nullable();
            $table->unsignedInteger('size')->default(0);

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->text('body');
            $table->unsignedInteger('order')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('user_message', function (Blueprint $table) {
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['user_id', 'message_id']);
        });
        
        Schema::create('user_thread', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('thread_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('thread_id')->references('id')->on('threads')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['user_id', 'thread_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_thread');
        Schema::dropIfExists('user_message');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('threads');
    }
};
