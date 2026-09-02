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
        // One Doctolib account (API credentials) per project,
        // mirrors google_accounts / pipedrive_accounts.
        // "token" will hold the Doctolib API key once partner access is granted.
        Schema::create('doctolib_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('token')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Which local Calendar a Doctolib agenda is imported into,
        // mirrors google_calendar.
        Schema::create('doctolib_calendar', function (Blueprint $table) {
            $table->unsignedBigInteger('doctolib_account_id');
            $table->unsignedBigInteger('calendar_id');
            $table->string('doctolib_calendar_id')->nullable();
            $table->timestamps();

            $table->foreign('doctolib_account_id')->references('id')->on('doctolib_accounts')->onDelete('cascade');
            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('cascade');

            $table->unique(['doctolib_account_id', 'calendar_id'], 'doctolib_account_calendar_uniqueness');
        });

        // Maps a distant Doctolib appointment id to the local Event
        // created from it, mirrors google_event. Prevents re-importing
        // the same appointment twice.
        Schema::create('doctolib_event', function (Blueprint $table) {
            $table->unsignedBigInteger('doctolib_account_id');
            $table->unsignedBigInteger('event_id');
            $table->string('doctolib_event_id');
            $table->timestamps();

            $table->foreign('doctolib_account_id')->references('id')->on('doctolib_accounts')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');

            $table->unique(['doctolib_account_id', 'event_id'], 'doctolib_account_event_uniqueness');
            $table->unique(['doctolib_account_id', 'doctolib_event_id'], 'doctolib_account_distant_event_uniqueness');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctolib_event');vscode-webview://1l7h8k3eqkbvofi95b3e2v23i33718i1frs91cnf7gkk1ndcsq0g/index.html?id=4673632f-77a4-4b49-adc1-865e392ce3ff&parentId=1&origin=4d1fe1f9-b2d3-4c34-870d-8c7772aa5567&swVersion=6&extensionId=Anthropic.claude-code&platform=electron&vscode-resource-base-authority=vscode-resource.vscode-cdn.net&parentOrigin=vscode-file%3A%2F%2Fvscode-app&session=654629b6-96ac-4520-b53c-0bac06119a04#
        Schema::dropIfExists('doctolib_calendar');
        Schema::dropIfExists('doctolib_accounts');
    }
};
