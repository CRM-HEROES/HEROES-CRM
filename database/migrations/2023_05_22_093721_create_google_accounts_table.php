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
        Schema::create('google_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('google_id');
            $table->string('name');
            $table->string('for', 20);
            $table->json('token');
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
        Schema::create('google_calendar', function (Blueprint $table) {
            $table->unsignedBigInteger('google_account_id');
            $table->unsignedBigInteger('calendar_id');
            $table->string('google_calendar_id');
            $table->string('google_channel_id')->nullable();
            $table->timestamps();

            $table->foreign('google_account_id')->references('id')->on('google_accounts')->onDelete('cascade');
            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('cascade');

            $table->unique(['google_account_id', 'calendar_id'], 'google_account_calendar_uniqueness');
        });
        
        Schema::create('google_event', function (Blueprint $table) {
            $table->unsignedBigInteger('google_account_id');
            $table->unsignedBigInteger('event_id');
            $table->string('google_event_id');
            $table->string('hangout')->nullable();
            $table->timestamps();

            $table->foreign('google_account_id')->references('id')->on('google_accounts')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');

            $table->unique(['google_account_id', 'event_id'], 'google_account_event_uniqueness');
        });
        
        Schema::create('google_contact', function (Blueprint $table) {
            $table->unsignedBigInteger('google_account_id');
            $table->unsignedBigInteger('prospect_id');
            $table->string('google_contact_id');
            $table->timestamps();

            $table->foreign('google_account_id')->references('id')->on('google_accounts')->onDelete('cascade');
            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');

            $table->unique(['google_account_id', 'prospect_id'], 'google_account_contact_uniqueness');
        });
        
        Schema::create('google_folder', function (Blueprint $table) {
            $table->unsignedBigInteger('google_account_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('google_folder_id');
            $table->timestamps();

            $table->foreign('google_account_id')->references('id')->on('google_accounts')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
        });
        
        Schema::create('google_file', function (Blueprint $table) {
            $table->unsignedBigInteger('google_account_id');
            $table->unsignedBigInteger('file_id');
            $table->string('google_file_id');
            $table->string('icon_link')->nullable();
            $table->string('thumbnail_link')->nullable();
            $table->string('web_content_link')->nullable();
            $table->string('web_view_link')->nullable();
            $table->timestamps();

            $table->foreign('google_account_id')->references('id')->on('google_accounts')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');

            $table->unique(['google_account_id', 'file_id'], 'google_account_file_uniqueness');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_file');
        Schema::dropIfExists('google_folder');
        Schema::dropIfExists('google_contact');
        Schema::dropIfExists('google_event');
        Schema::dropIfExists('google_calendar');
        Schema::dropIfExists('google_accounts');
    }
};
