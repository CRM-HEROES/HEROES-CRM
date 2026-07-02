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
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('import_id')->nullable();

            $table->string('email')->nullable()->index();
            $table->string('title')->nullable();
            $table->string('last_name')->nullable()->index();
            $table->string('first_name')->nullable()->index();
            $table->string('company_name')->nullable()->index();
            $table->string('job_title')->nullable()->index();

            $table->string('website_url')->nullable()->index();
            $table->string('phone_number', 20)->nullable()->index();
            $table->string('mobile_phone_number', 20)->nullable()->index();
            $table->string('fax_number', 20)->nullable()->index();

            $table->string('street')->nullable()->index();
            $table->string('street_bis')->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('postal_code', 20)->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->string('county')->nullable()->index();
            $table->string('country')->nullable()->index();
            $table->float('latitude', 10, 6)->nullable()->index();
            $table->float('longitude', 10, 6)->nullable()->index();
            $table->boolean('valid_address')->nullable()->index();

            $table->string('ip_address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->index()->nullable();
            
            $table->string('password')->nullable();
            $table->rememberToken();

            $table->unsignedBigInteger('duplicate_id')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            // $table->softDeletes();
            $table->timestamp('deleted_at')->index()->nullable();
            // $table->timestamps();
            $table->timestamp('created_at', 3)->index()->nullable();
            $table->timestamp('updated_at')->index()->nullable();
            
            $table->index(['project_id', 'created_at'], 'prospects_project_id_created_at_index');
            $table->index(['project_id', 'updated_at'], 'prospects_project_id_updated_at_index');
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('import_id')->references('id')->on('imports')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('duplicate_id')->references('id')->on('prospects')->onDelete('set null');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('prospect_label', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('label_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamp('deleted_at')->index()->nullable();
            $table->timestamps();

            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');

            // $table->unique(['prospect_id', 'label_id']);
        });

        Schema::create('prospect_user', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');

            $table->unique(['prospect_id', 'user_id']);
        });

        Schema::create('prospect_wait_for_user', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');

            $table->unique(['prospect_id', 'user_id']);
        });

        Schema::create('prospect_group', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('group_id');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');

            $table->unique(['prospect_id', 'group_id']);
        });
        
        Schema::create('password_resets_prospects', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('unique_id')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_resets_prospects');
        Schema::dropIfExists('prospect_group');
        Schema::dropIfExists('prospect_wait_for_user');
        Schema::dropIfExists('prospect_user');
        Schema::dropIfExists('prospect_label');
        Schema::dropIfExists('prospects');
    }
};
