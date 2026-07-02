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
        Schema::create('pipedrive_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('token');
            $table->string('password');
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
        Schema::create('pipedrive_person', function (Blueprint $table) {
            $table->unsignedBigInteger('pipedrive_account_id');
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('person_id');
            $table->boolean('from_pipedrive', 1);
            $table->timestamps();

            $table->foreign('pipedrive_account_id')->references('id')->on('pipedrive_accounts')->onDelete('cascade');
            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');

            $table->unique(['pipedrive_account_id', 'prospect_id'], 'pipedrive_account_person_uniqueness');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipedrive_person');
        Schema::dropIfExists('pipedrive_accounts');
    }
};
