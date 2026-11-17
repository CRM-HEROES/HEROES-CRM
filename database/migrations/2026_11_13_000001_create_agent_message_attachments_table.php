<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->string('name', 255);
            $table->string('mime', 100);
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('agent_messages')->onDelete('cascade');
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_message_attachments');
    }
};
