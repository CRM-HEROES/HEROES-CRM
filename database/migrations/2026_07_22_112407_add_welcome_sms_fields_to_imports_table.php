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
        Schema::table('imports', function (Blueprint $table) {
            $table->boolean('notify_welcome_sms')->default(false)->after('token')->comment('Active ou non l\'envoi du SMS de bienvenue via Brevo');
            $table->text('welcome_sms_message')->nullable()->after('notify_welcome_sms');
            $table->timestamp('welcome_sms_sent_at')->nullable()->after('welcome_sms_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn([
                'notify_welcome_sms',
                'welcome_sms_message',
                'welcome_sms_sent_at',
            ]);
        });
    }
};