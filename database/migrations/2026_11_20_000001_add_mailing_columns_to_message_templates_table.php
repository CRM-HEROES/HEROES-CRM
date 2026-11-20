<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a library of email templates (a mailing plan) needs: a code to find a
 * template by (the same everywhere: in the plan, in the automations), whether
 * it is a marketing email (it then follows the sending rules of the project),
 * and whether it is switched off (it is never sent).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->string('code', 40)->nullable()->index();
            $table->boolean('marketing')->default(false);
            $table->boolean('disabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropColumn(['code', 'marketing', 'disabled']);
        });
    }
};
