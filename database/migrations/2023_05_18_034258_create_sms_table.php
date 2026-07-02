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
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->text('message');
            $table->text('error')->nullable();
            $table->string('source', 20)->nullable()->index();
            $table->boolean('from_user')->default(1);
            $table->timestamp('sent_at')->nullable();
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name', 50);
            $table->text('body');
            $table->unsignedInteger('order')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
        Schema::dropIfExists('sms');
    }
};
