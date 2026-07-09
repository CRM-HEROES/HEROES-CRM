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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');

            // Jour concerné
            $table->date('date')->index();

            // Horaires prévus (référence pour calculer le retard)
            $table->time('expected_arrival')->nullable();
            $table->time('expected_departure')->nullable();

            // Pointage réel (saisi par l'admin)
            $table->dateTime('arrival_at')->nullable();
            $table->dateTime('departure_at')->nullable();

            $table->unsignedInteger('break_minutes')->default(0);

            // present | late | absent | leave
            $table->string('status')->nullable();
            $table->text('note')->nullable();

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            // Un seul pointage par employé et par jour
            $table->unique(['project_id', 'user_id', 'date']);

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
