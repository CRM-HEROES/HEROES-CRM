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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->string('slug', 50)->unique();
            $table->string('phone_number', 20)->nullable();
            $table->string('email')->nullable();
            
            $table->string('street')->nullable();
            $table->string('street_bis')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->float('latitude', 10, 6)->nullable();
            $table->float('longitude', 10, 6)->nullable();
            $table->boolean('valid_address')->default(0);

            $table->string('num_tva_intra', 50)->nullable();
            $table->string('naf', 50)->nullable();
            $table->string('siret', 50)->nullable();
            $table->string('capital')->nullable();

            $table->string('logo')->nullable();
            
            $table->string('type', 50)->default('professional');
            $table->string('job_category')->nullable();
            $table->json('meta')->nullable();

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('user_project', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
            $table->string('color', 7)->default('#333333');
            $table->string('bgcolor', 7)->default('#CCCCCC');
            $table->unsignedInteger('relevance')->default(0);
            $table->unsignedInteger('relevance_event')->default(0);
            $table->unsignedInteger('relevance_prospect')->default(0);
            $table->unsignedInteger('relevance_order_action')->default(0);
            $table->unsignedInteger('relevance_message')->default(0);
            $table->unsignedInteger('order')->default(1000);
            $table->unsignedInteger('prospects_count')->default(0);
            $table->timestamp('last_activity')->nullable();
            $table->json('meta')->nullable();

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['user_id', 'project_id']);
        });

        
        /**
         * Associated user assignable users
         * If user is not a project admin
         * he can use only these users
         */
        Schema::create('user_assignable_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('assignable_user_id');
            $table->unsignedBigInteger('project_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('assignable_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['user_id', 'assignable_user_id', 'project_id'], 'user_assignable_user_uniqueness');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('last_project_id')->nullable()->after('last_activity');
            
            $table->foreign('last_project_id')->references('id')->on('projects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign("users_last_project_id_foreign");
            $table->dropColumn("last_project_id");
        });

        Schema::dropIfExists('user_assignable_user');
        Schema::dropIfExists('user_project');
        Schema::dropIfExists('projects');
    }
};
