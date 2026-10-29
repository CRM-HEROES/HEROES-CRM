<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->string('cloudtalk_message_id', 191)->nullable()->index();
            $table->string('cloudtalk_event_id', 191)->nullable()->index();
            $table->string('cloudtalk_sender_number', 32)->nullable();
            $table->timestamp('cloudtalk_send_started_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->dropIndex(['cloudtalk_message_id']);
            $table->dropIndex(['cloudtalk_event_id']);
            $table->dropColumn([
                'cloudtalk_message_id', 'cloudtalk_event_id',
                'cloudtalk_sender_number', 'cloudtalk_send_started_at',
            ]);
        });
    }
};
