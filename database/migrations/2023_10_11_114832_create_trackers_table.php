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
        Schema::create('tracker_agents', function (Blueprint $table) {
            $table->id();

            $table->string('browser')->index();
            $table->string('browser_version');

            $table->timestamps();
        });

        Schema::create('tracker_cookies', function (Blueprint $table) {
            $table->id();

            $table->string('uuid', 512)->unique();

            $table->timestamps();
        });

        Schema::create('tracker_devices', function (Blueprint $table) {
            $table->id();

            $table->string('kind', 50)->index();
            $table->string('model', 50)->index();
            $table->string('platform', 50)->index();
            $table->string('platform_version', 50)->index();
            $table->boolean('is_mobile');

            $table->timestamps();

            // $table->unique(['kind', 'model', 'platform', 'platform_version'], 'device_uniqueness');
        });

        Schema::create('tracker_geoip', function (Blueprint $table) {
            $table->id();

            $table->double('latitude')->nullable()->index();
            $table->double('longitude')->nullable()->index();

            $table->string('country_code', 2)->nullable()->index();
            $table->string('country_name')->nullable()->index();
            $table->string('region', 50)->nullable();
            $table->string('city', 50)->nullable()->index();
            $table->string('postal_code', 20)->nullable();

            $table->timestamps();
        });

        Schema::create('tracker_sessions', function (Blueprint $table) {
            $table->id();

            $table->string('uuid')->unique()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('device_id')->nullable()->index();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->string('client_ip')->nullable()->index();
            $table->unsignedBigInteger('cookie_id')->nullable()->index();
            $table->unsignedBigInteger('geoip_id')->nullable()->index();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('device_id')->references('id')->on('tracker_devices')->onDelete('set null');
            $table->foreign('agent_id')->references('id')->on('tracker_agents')->onDelete('set null');
            $table->foreign('cookie_id')->references('id')->on('tracker_cookies')->onDelete('set null');
            $table->foreign('geoip_id')->references('id')->on('tracker_geoip')->onDelete('set null');
        });

        Schema::create('tracker_routes', function (Blueprint $table) {
            $table->id();

            $table->string('name')->index();
            $table->string('action')->index();

            $table->timestamps();
        });

        Schema::create('tracker_route_paths', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('route_id')->index();
            $table->string('path')->index();
            $table->json('parameters');

            $table->timestamps();
            
            $table->foreign('route_id')->references('id')->on('tracker_routes')->onDelete('cascade');
        });

        Schema::create('tracker_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('session_id')->index();
            $table->string('method', 10)->index();
            $table->unsignedBigInteger('route_path_id')->nullable()->index();
            $table->boolean('is_ajax');
            $table->boolean('is_secure');
            $table->boolean('is_json');
            $table->boolean('wants_json');

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('tracker_sessions')->onDelete('cascade');
            $table->foreign('route_path_id')->references('id')->on('tracker_route_paths')->onDelete('cascade');
        });
        
        Schema::create('tracker_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->integer('hits');
            $table->text('hits_per_time');
            $table->timestamps();

            $table->index('date');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });

        Schema::create('tracker_user_banned_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('device_id')->index();
            $table->string('client_ip')->index();
            $table->timestamp('banned_at')->nullable();
            $table->unsignedInteger('ban_code')->nullable();

            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('device_id')->references('id')->on('tracker_devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracker_user_banned_devices');
        Schema::dropIfExists('tracker_stats');
        Schema::dropIfExists('tracker_logs');
        Schema::dropIfExists('tracker_route_paths');
        Schema::dropIfExists('tracker_routes');
        Schema::dropIfExists('tracker_sessions');
        Schema::dropIfExists('tracker_geoip');
        Schema::dropIfExists('tracker_devices');
        Schema::dropIfExists('tracker_cookies');
        Schema::dropIfExists('tracker_agents');
    }
};
