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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            $table->string('phone_number')->nullable();
            $table->string('mobile_phone_number')->nullable();
            $table->string('street')->nullable();
            $table->string('street_bis')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('county')->nullable();
            $table->float('latitude', 10, 6)->nullable();
            $table->float('longitude', 10, 6)->nullable();

            $table->string('ip_postal_code', 20)->nullable();
            $table->string('ip_city')->nullable();
            $table->string('ip_country')->nullable();
            $table->string('ip_state')->nullable();
            $table->float('ip_latitude', 10, 6)->nullable();
            $table->float('ip_longitude', 10, 6)->nullable();
            $table->string('ip_address')->nullable();

            $table->timestamp('last_activity')->nullable();
            $table->string('role')->nullable();
            $table->json('default_projects')->nullable();
            $table->text('two_factors_secret')->nullable();
            $table->string('two_factors_check_type', 50)->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();

            $table->timestamp('banned_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
