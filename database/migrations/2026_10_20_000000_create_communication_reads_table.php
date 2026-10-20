<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('communication_type', 32);
            $table->unsignedBigInteger('communication_id');
            $table->timestamp('read_at');
            $table->unique(['user_id', 'communication_type', 'communication_id'], 'communication_reads_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_reads');
    }
};
