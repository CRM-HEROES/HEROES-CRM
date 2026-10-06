<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_metrics', function (Blueprint $table) {
            $table->id();
            $table->timestamp('recorded_at')->index();
            $table->float('cpu_percent');
            $table->float('load_1')->nullable();
            $table->float('ram_percent');
            $table->unsignedBigInteger('ram_used')->nullable();
            $table->unsignedBigInteger('ram_total')->nullable();
            $table->float('disk_percent')->nullable();
            $table->unsignedBigInteger('net_rx_bps')->default(0);
            $table->unsignedBigInteger('net_tx_bps')->default(0);
            // Top processes / containers at that moment (to explain peaks)
            $table->json('details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_metrics');
    }
};
