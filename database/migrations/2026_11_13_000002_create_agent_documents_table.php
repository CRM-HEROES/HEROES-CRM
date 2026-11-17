<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('mime', 100);
            $table->string('path');
            $table->json('summary')->nullable();
            $table->string('status', 20)->default('queued');
            $table->timestamps();

            $table->index(['agent_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_documents');
    }
};
