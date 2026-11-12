<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What an automatic email needs and a message written by a user does not:
 * its own subject, and the people who get a copy of it. Both are optional:
 * without them the email is the one the CRM has always sent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('subject', 255)->nullable();
            // [{ "email": "...", "name": "..." }]
            $table->json('cc')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['subject', 'cc']);
        });
    }
};
